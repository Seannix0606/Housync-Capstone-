<?php

namespace Tests\Feature\Landlord;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fixed dwelling types cannot open the “add unit” form when already at the type’s unit cap.
 *
 * @see \App\Services\Landlord\PropertyTypeUnitRules
 */
class FixedDwellingCreateUnitRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(RoleMiddleware::class);
    }

    private function landlord(): User
    {
        return User::factory()->create(['role' => 'landlord']);
    }

    public function test_create_unit_form_redirects_when_duplex_has_two_units(): void
    {
        $landlord = $this->landlord();
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'duplex',
        ]);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'East']);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'West']);

        $response = $this->actingAs($landlord)
            ->get(route('landlord.create-unit-for-apartment', $property->id));

        $response->assertRedirect(route('landlord.units', ['apartment' => $property->id]));
        $response->assertSessionHasErrors('unit_number');
    }

    public function test_create_unit_form_redirects_when_single_family_house_has_one_unit(): void
    {
        $landlord = $this->landlord();
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'house',
        ]);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'Main']);

        $response = $this->actingAs($landlord)
            ->get(route('landlord.create-unit-for-apartment', $property->id));

        $response->assertRedirect(route('landlord.units', ['apartment' => $property->id]));
        $response->assertSessionHasErrors('unit_number');
    }

    public function test_create_unit_form_redirects_when_townhouse_has_one_unit(): void
    {
        $landlord = $this->landlord();
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'townhouse',
        ]);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'TH1']);

        $response = $this->actingAs($landlord)
            ->get(route('landlord.create-unit-for-apartment', $property->id));

        $response->assertRedirect(route('landlord.units', ['apartment' => $property->id]));
        $response->assertSessionHasErrors('unit_number');
    }

    public function test_create_multiple_units_form_redirects_when_duplex_is_full(): void
    {
        $landlord = $this->landlord();
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'duplex',
        ]);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => '1']);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => '2']);

        $response = $this->actingAs($landlord)
            ->get(route('landlord.create-multiple-units', $property->id));

        $response->assertRedirect(route('landlord.units', ['apartment' => $property->id]));
        $response->assertSessionHasErrors('unit_number');
    }

    public function test_create_unit_form_allows_duplex_with_only_one_existing_unit(): void
    {
        $landlord = $this->landlord();
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'duplex',
        ]);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'Side-A']);

        $response = $this->actingAs($landlord)
            ->get(route('landlord.create-unit-for-apartment', $property->id));

        $response->assertOk();
    }
}
