<?php

namespace Tests\Feature\Landlord;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplexPropertyRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(RoleMiddleware::class);
    }

    public function test_landlord_cannot_post_third_unit_on_duplex(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'duplex',
        ]);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'Unit 1']);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'Unit 2']);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), [
                'unit_number' => 'Unit 3',
                'unit_type' => 'Duplex',
                'rent_amount' => 1000,
                'status' => 'available',
                'leasing_type' => 'separate',
                'bedrooms' => 1,
                'bathrooms' => 1,
            ]);

        $response->assertSessionHasErrors('unit_number');
    }
}
