<?php

namespace Tests\Unit\Services\SuperAdmin;

use App\Models\Property;
use App\Models\TenantAssignment;
use App\Models\Unit;
use App\Models\User;
use App\Services\SuperAdmin\PropertyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyServiceTest extends TestCase
{
    use RefreshDatabase;

    private PropertyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PropertyService;
    }

    public function test_can_fetch_property_details(): void
    {
        $property = Property::factory()->create();
        Unit::factory()->count(2)->create([
            'property_id' => $property->id,
            'status' => 'occupied',
        ]);
        Unit::factory()->create([
            'property_id' => $property->id,
            'status' => 'available',
        ]);

        $result = $this->service->getPropertyDetails($property->id);

        $this->assertSame($property->id, $result->id);
        $this->assertSame(3, $result->units_count);
        $this->assertSame(2, $result->occupied_units_count);
        $this->assertSame(1, $result->available_units_count);
        $this->assertTrue($result->relationLoaded('landlord'));
    }

    public function test_can_fetch_units_under_a_property(): void
    {
        $property = Property::factory()->create();
        $tenant = User::factory()->create(['role' => 'tenant']);
        $unit = Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => 'A-101',
            'status' => 'occupied',
            'rent_amount' => 15000,
        ]);

        TenantAssignment::create([
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'landlord_id' => $property->landlord_id,
            'assigned_at' => now(),
            'lease_start_date' => now()->toDateString(),
            'lease_end_date' => now()->addYear()->toDateString(),
            'rent_amount' => 15000,
            'security_deposit' => 15000,
            'status' => 'active',
        ]);

        $result = $this->service->getPropertyWithUnits($property->id);

        $this->assertCount(1, $result->units);
        $this->assertSame('A-101', $result->units->first()->unit_number);
        $this->assertTrue($result->units->first()->relationLoaded('tenantAssignment'));
        $this->assertNotNull($result->units->first()->tenantAssignment?->tenant);
    }

    public function test_handles_property_with_no_units(): void
    {
        $property = Property::factory()->create();

        $details = $this->service->getPropertyDetails($property->id);
        $units = $this->service->getPropertyWithUnits($property->id);

        $this->assertSame(0, $details->units_count);
        $this->assertSame(0, $details->occupied_units_count);
        $this->assertSame(0, $details->available_units_count);
        $this->assertCount(0, $units->units);
    }

    public function test_handles_invalid_property_id(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getPropertyDetails(999999);
    }
}
