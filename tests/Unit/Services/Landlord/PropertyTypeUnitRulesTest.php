<?php

namespace Tests\Unit\Services\Landlord;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Services\Landlord\PropertyTypeUnitRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PropertyTypeUnitRulesTest extends TestCase
{
    use RefreshDatabase;

    private PropertyTypeUnitRules $rules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rules = new PropertyTypeUnitRules;
    }

    public function test_duplex_initial_count_is_always_two(): void
    {
        $this->assertSame(2, $this->rules->resolveInitialUnitCount('duplex', null, null));
        $this->assertSame(2, $this->rules->resolveInitialUnitCount('duplex', 2, null));
    }

    public function test_duplex_rejects_non_two_request_hint(): void
    {
        $this->expectException(ValidationException::class);
        $this->rules->resolveInitialUnitCount('duplex', 3, null);
    }

    public function test_maximum_units_for_fixed_dwelling_types(): void
    {
        $this->assertSame(1, $this->rules->maximumUnitsForType('house'));
        $this->assertSame(1, $this->rules->maximumUnitsForType('townhouse'));
        $this->assertSame(2, $this->rules->maximumUnitsForType('duplex'));
        $this->assertNull($this->rules->maximumUnitsForType('apartment'));
    }

    public function test_townhouse_initial_count_is_always_one(): void
    {
        $this->assertSame(1, $this->rules->resolveInitialUnitCount('townhouse', null, null));
        $this->assertSame(1, $this->rules->resolveInitialUnitCount('townhouse', 1, null));
    }

    public function test_townhouse_rejects_non_one_request_hint(): void
    {
        $this->expectException(ValidationException::class);
        $this->rules->resolveInitialUnitCount('townhouse', 2, null);
    }

    public function test_cannot_add_unit_when_townhouse_at_cap(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'townhouse',
        ]);
        Unit::factory()->create(['property_id' => $property->id]);

        $this->expectException(ValidationException::class);
        $this->rules->assertMayAddUnits($property->fresh(), 1);
    }

    public function test_cannot_add_unit_when_house_at_cap(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'house',
        ]);
        Unit::factory()->create(['property_id' => $property->id]);

        $this->expectException(ValidationException::class);
        $this->rules->assertMayAddUnits($property->fresh(), 1);
    }

    public function test_cannot_delete_sole_unit_on_house(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'house',
        ]);
        $unit = Unit::factory()->create(['property_id' => $property->id]);

        $this->expectException(ValidationException::class);
        $this->rules->assertDeletingUnitAllowed($unit);
    }

    public function test_update_to_townhouse_requires_exactly_one_existing_unit(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'apartment',
        ]);
        Unit::factory()->create(['property_id' => $property->id]);
        Unit::factory()->create(['property_id' => $property->id]);

        $this->expectException(ValidationException::class);
        $this->rules->assertUpdateCompatibleWithExistingUnits($property->fresh(), 'townhouse', 1);
    }

    public function test_cannot_add_unit_beyond_duplex_cap(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'duplex',
        ]);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'U1']);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'U2']);

        $this->expectException(ValidationException::class);
        $this->rules->assertMayAddUnits($property->fresh(), 1);
    }

    public function test_cannot_delete_unit_on_duplex(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'duplex',
        ]);
        $unit = Unit::factory()->create(['property_id' => $property->id]);

        $this->expectException(ValidationException::class);
        $this->rules->assertDeletingUnitAllowed($unit);
    }

    public function test_update_to_duplex_requires_exactly_two_existing_units(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'apartment',
        ]);
        Unit::factory()->create(['property_id' => $property->id]);

        $this->expectException(ValidationException::class);
        $this->rules->assertUpdateCompatibleWithExistingUnits($property->fresh(), 'duplex', 2);
    }

    public function test_update_to_duplex_succeeds_when_two_units(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'apartment',
        ]);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'A']);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'B']);

        $this->rules->assertUpdateCompatibleWithExistingUnits($property->fresh(), 'duplex', 2);
        $this->assertTrue(true);
    }
}
