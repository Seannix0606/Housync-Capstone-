<?php

namespace Tests\Feature\Explore;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyDetailsFloorGroupingTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(): User
    {
        return User::factory()->create([
            'role' => 'tenant',
        ]);
    }

    private function createProperty(string $type = 'apartment'): Property
    {
        $landlord = User::factory()->create([
            'role' => 'landlord',
        ]);

        return Property::factory()->create([
            'name' => 'Sunrise Residence',
            'slug' => 'sunrise-residence',
            'property_type' => $type,
            'landlord_id' => $landlord->id,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function test_it_groups_available_units_by_floor_and_shows_floor_counts(): void
    {
        $property = $this->createProperty('apartment');
        $tenant = $this->createTenant();

        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'floor_number' => 1,
            'rent_amount' => 6000,
            'status' => 'available',
        ]);
        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => '202',
            'floor_number' => 2,
            'rent_amount' => 7000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSee('Floor 1');
        $response->assertSee('Floor 2');
        $response->assertSee('1 available');
        $response->assertSee('Room 101');
        $response->assertSee('Room 202');
    }

    public function test_it_sorts_units_by_rent_then_unit_number_and_places_unspecified_floor_last(): void
    {
        $property = $this->createProperty('condominium');
        $tenant = $this->createTenant();

        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => '106',
            'floor_number' => 1,
            'rent_amount' => 9500,
            'status' => 'available',
        ]);
        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'floor_number' => 1,
            'rent_amount' => 9000,
            'status' => 'available',
        ]);
        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => 'X1',
            'floor_number' => 0,
            'rent_amount' => 8000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSeeInOrder(['Unit 101', 'Unit 106']);
        $response->assertSeeInOrder(['Floor 1', 'Unspecified Floor']);
    }

    public function test_it_renders_compatible_unit_view_links_and_tenant_actions(): void
    {
        $property = $this->createProperty('townhouse');
        $tenant = $this->createTenant();

        $unit = Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => '301',
            'floor_number' => 3,
            'rent_amount' => 11000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSee(route('property.show', $property->slug.'-unit-'.$unit->id), false);
        $response->assertSee('Contact Landlord');
        $response->assertSee('Apply to this Unit');
    }

    public function test_hero_uses_property_cover_image_when_present(): void
    {
        $property = $this->createProperty('apartment');
        $tenant = $this->createTenant();
        $property->update([
            'cover_image' => 'https://cdn.example.com/property-cover.jpg',
            'gallery' => ['https://cdn.example.com/property-gallery-1.jpg'],
        ]);

        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'floor_number' => 1,
            'cover_image' => 'https://cdn.example.com/unit-cover.jpg',
            'status' => 'available',
        ]);

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSee('data-testid="property-hero-image"', false);
        $response->assertSee('https://cdn.example.com/property-cover.jpg', false);
    }

    public function test_hero_falls_back_to_property_gallery_when_cover_is_missing(): void
    {
        $property = $this->createProperty('apartment');
        $tenant = $this->createTenant();
        $property->update([
            'cover_image' => null,
            'gallery' => ['https://cdn.example.com/property-gallery-1.jpg'],
        ]);

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSee('https://cdn.example.com/property-gallery-1.jpg', false);
    }

    public function test_hero_uses_placeholder_when_property_has_no_media(): void
    {
        $property = $this->createProperty('apartment');
        $tenant = $this->createTenant();
        $property->update([
            'cover_image' => null,
            'gallery' => null,
        ]);

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSee('data-testid="property-hero-placeholder"', false);
    }

    public function test_hero_does_not_render_unit_image_when_property_has_no_media(): void
    {
        $property = $this->createProperty('apartment');
        $tenant = $this->createTenant();
        $property->update([
            'cover_image' => null,
            'gallery' => null,
        ]);

        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'floor_number' => 1,
            'cover_image' => 'https://cdn.example.com/unit-cover-hero-test.jpg',
            'status' => 'available',
        ]);

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSee('data-testid="property-hero-placeholder"', false);
        $response->assertDontSee('src="https://cdn.example.com/unit-cover-hero-test.jpg" data-testid="property-hero-image"', false);
        $response->assertSee('src="https://cdn.example.com/unit-cover-hero-test.jpg"', false);
    }

    public function test_guest_still_sees_contact_login_call_to_action(): void
    {
        $property = $this->createProperty('apartment');
        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'floor_number' => 1,
            'status' => 'available',
        ]);

        $response = $this->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSee('Login to Contact Landlord');
    }

    public function test_house_without_units_shows_fallback_and_no_dead_end(): void
    {
        $property = $this->createProperty('house');
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertSee('no unit record is configured yet', false);
        $response->assertSee('Contact Landlord');
        $response->assertSee('Apply Unavailable (Unit Setup Pending)');
    }

    public function test_property_details_query_count_stays_reasonable_as_n_plus_one_smoke_test(): void
    {
        $property = $this->createProperty('apartment');
        $tenant = $this->createTenant();

        Unit::factory()->count(8)->create([
            'property_id' => $property->id,
            'floor_number' => 1,
            'status' => 'available',
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($tenant)->get(route('property.show', $property->slug));

        $response->assertOk();

        $queries = DB::getQueryLog();
        $this->assertLessThanOrEqual(20, count($queries), 'Potential N+1 regression: query count is unexpectedly high.');
    }
}
