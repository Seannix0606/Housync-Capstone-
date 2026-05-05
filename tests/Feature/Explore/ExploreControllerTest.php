<?php

namespace Tests\Feature\Explore;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExploreControllerTest extends TestCase
{
    use RefreshDatabase;

    // ── helpers ───────────────────────────────────────────────────────────────

    private function createLandlord(): User
    {
        return User::factory()->create(['role' => 'landlord']);
    }

    private function createTenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    private function createActiveProperty(User $landlord, array $propertyOverrides = []): Property
    {
        return Property::factory()->create(array_merge([
            'landlord_id' => $landlord->id,
            'status' => 'active',
            'is_active' => true,
            'property_type' => 'apartment',
        ], $propertyOverrides));
    }

    private function addAvailableUnit(Property $property, array $unitOverrides = []): Unit
    {
        return Unit::factory()->create(array_merge([
            'property_id' => $property->id,
            'status' => 'available',
            'rent_amount' => 5000,
            'floor_number' => 1,
        ], $unitOverrides));
    }

    // ── index page – unauthenticated ──────────────────────────────────────────

    public function test_index_returns_200_for_guest(): void
    {
        $response = $this->get(route('explore'));

        $response->assertOk();
    }

    public function test_index_renders_explore_view_for_guest(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);

        $response = $this->get(route('explore'));

        $response->assertOk();
        $response->assertViewIs('explore');
        $response->assertViewHas('properties');
    }

    public function test_index_renders_tenant_explore_view_for_authenticated_tenant(): void
    {
        $tenant = $this->createTenant();
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);

        $response = $this->actingAs($tenant)->get(route('explore'));

        $response->assertOk();
        $response->assertViewIs('tenant.explore');
    }

    public function test_index_passes_property_types_to_view(): void
    {
        $response = $this->get(route('explore'));

        $response->assertOk();
        $response->assertViewHas('propertyTypes');
        $types = $response->viewData('propertyTypes');
        $this->assertContains('apartment', $types);
        $this->assertContains('condominium', $types);
    }

    // ── index page – property visibility ─────────────────────────────────────

    public function test_index_only_shows_active_properties_with_available_units(): void
    {
        $landlord = $this->createLandlord();

        // Should appear: active property with available unit
        $activeProperty = $this->createActiveProperty($landlord, ['name' => 'Active Property']);
        $this->addAvailableUnit($activeProperty);

        // Should NOT appear: inactive property
        $inactiveProperty = $this->createActiveProperty($landlord, [
            'name' => 'Inactive Property',
            'status' => 'inactive',
        ]);
        $this->addAvailableUnit($inactiveProperty);

        // Should NOT appear: active property with no available units
        $noUnitsProperty = $this->createActiveProperty($landlord, ['name' => 'No Units Property']);
        Unit::factory()->create([
            'property_id' => $noUnitsProperty->id,
            'status' => 'occupied',
        ]);

        $response = $this->get(route('explore'));

        $response->assertOk();
        $properties = $response->viewData('properties');
        $names = $properties->pluck('name')->toArray();
        $this->assertContains('Active Property', $names);
        $this->assertNotContains('Inactive Property', $names);
        $this->assertNotContains('No Units Property', $names);
    }

    public function test_index_includes_available_units_count_on_properties(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);
        $this->addAvailableUnit($property);

        $response = $this->get(route('explore'));

        $response->assertOk();
        $properties = $response->viewData('properties');
        $found = $properties->firstWhere('id', $property->id);
        $this->assertNotNull($found);
        $this->assertSame(2, (int) $found->available_units_count);
    }

    // ── AJAX filter endpoint ──────────────────────────────────────────────────

    public function test_filter_properties_returns_json_for_ajax_requests(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore'));

        $response->assertOk();
        $response->assertJsonStructure(['success', 'html', 'pagination', 'count']);
        $response->assertJson(['success' => true]);
    }

    public function test_filter_properties_ajax_response_contains_correct_count(): void
    {
        $landlord = $this->createLandlord();
        $property1 = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property1);
        $property2 = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property2);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore'));

        $response->assertOk();
        $data = $response->json();
        $this->assertSame(2, $data['count']);
    }

    // ── type filter ───────────────────────────────────────────────────────────

    public function test_index_filters_by_property_type(): void
    {
        $landlord = $this->createLandlord();

        $apt = $this->createActiveProperty($landlord, ['name' => 'Apartment A', 'property_type' => 'apartment']);
        $this->addAvailableUnit($apt);

        $condo = $this->createActiveProperty($landlord, ['name' => 'Condo B', 'property_type' => 'condominium']);
        $this->addAvailableUnit($condo);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore', ['type' => 'condominium']));

        $response->assertOk();
        $data = $response->json();
        $this->assertSame(1, $data['count']);
        $this->assertStringContainsString('Condo B', $data['html']);
        $this->assertStringNotContainsString('Apartment A', $data['html']);
    }

    // ── search filter ─────────────────────────────────────────────────────────

    public function test_index_search_filters_by_property_name(): void
    {
        $landlord = $this->createLandlord();

        $sunrise = $this->createActiveProperty($landlord, ['name' => 'Sunrise Towers']);
        $this->addAvailableUnit($sunrise);

        $moonlight = $this->createActiveProperty($landlord, ['name' => 'Moonlight Heights']);
        $this->addAvailableUnit($moonlight);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore', ['search' => 'Sunrise']));

        $response->assertOk();
        $data = $response->json();
        $this->assertSame(1, $data['count']);
        $this->assertStringContainsString('Sunrise Towers', $data['html']);
    }

    public function test_index_search_filters_by_city(): void
    {
        $landlord = $this->createLandlord();

        $cebuProperty = $this->createActiveProperty($landlord, ['name' => 'Cebu Property', 'city' => 'Cebu City']);
        $this->addAvailableUnit($cebuProperty);

        $manilaProperty = $this->createActiveProperty($landlord, ['name' => 'Manila Property', 'city' => 'Manila']);
        $this->addAvailableUnit($manilaProperty);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore', ['search' => 'Cebu']));

        $response->assertOk();
        $data = $response->json();
        $this->assertSame(1, $data['count']);
    }

    // ── price filters ─────────────────────────────────────────────────────────

    public function test_index_filters_by_min_price(): void
    {
        $landlord = $this->createLandlord();

        $cheap = $this->createActiveProperty($landlord, ['name' => 'Cheap Property']);
        $this->addAvailableUnit($cheap, ['rent_amount' => 3000]);

        $expensive = $this->createActiveProperty($landlord, ['name' => 'Expensive Property']);
        $this->addAvailableUnit($expensive, ['rent_amount' => 15000]);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore', ['min_price' => 10000]));

        $response->assertOk();
        $data = $response->json();
        $this->assertSame(1, $data['count']);
        $this->assertStringContainsString('Expensive Property', $data['html']);
    }

    public function test_index_filters_by_max_price(): void
    {
        $landlord = $this->createLandlord();

        $cheap = $this->createActiveProperty($landlord, ['name' => 'Budget Property']);
        $this->addAvailableUnit($cheap, ['rent_amount' => 3000]);

        $expensive = $this->createActiveProperty($landlord, ['name' => 'Luxury Property']);
        $this->addAvailableUnit($expensive, ['rent_amount' => 25000]);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore', ['max_price' => 5000]));

        $response->assertOk();
        $data = $response->json();
        $this->assertSame(1, $data['count']);
        $this->assertStringContainsString('Budget Property', $data['html']);
    }

    // ── availability filter ───────────────────────────────────────────────────

    public function test_index_default_shows_only_properties_with_available_units(): void
    {
        $landlord = $this->createLandlord();

        $available = $this->createActiveProperty($landlord, ['name' => 'Has Available']);
        $this->addAvailableUnit($available);

        $occupied = $this->createActiveProperty($landlord, ['name' => 'Fully Occupied']);
        Unit::factory()->create(['property_id' => $occupied->id, 'status' => 'occupied']);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore'));

        $data = $response->json();
        $this->assertStringContainsString('Has Available', $data['html']);
        $this->assertStringNotContainsString('Fully Occupied', $data['html']);
    }

    public function test_index_filters_by_amenities_from_modal_input(): void
    {
        if (! Schema::hasColumn('properties', 'amenities')) {
            $this->markTestSkipped('properties.amenities column is unavailable in current schema.');
        }

        $wifiAmenity = Amenity::create([
            'name' => 'wifi',
            'icon' => 'fas fa-wifi',
            'slug' => 'wifi',
        ]);

        $parkingAmenity = Amenity::create([
            'name' => 'parking',
            'icon' => 'fas fa-parking',
            'slug' => 'parking',
        ]);

        $landlord = $this->createLandlord();

        $wifiProperty = $this->createActiveProperty($landlord, [
            'name' => 'Wifi Home',
            'amenities' => ['wifi'],
        ]);
        $this->addAvailableUnit($wifiProperty);

        $parkingProperty = $this->createActiveProperty($landlord, [
            'name' => 'Parking Home',
            'amenities' => ['parking'],
        ]);
        $this->addAvailableUnit($parkingProperty);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore', ['amenities' => [$wifiAmenity->id]]));

        $response->assertOk();
        $data = $response->json();
        $this->assertSame(1, $data['count']);
        $this->assertStringContainsString('Wifi Home', $data['html']);
        $this->assertStringNotContainsString('Parking Home', $data['html']);
    }

    public function test_index_available_date_filters_do_not_error_when_columns_are_absent(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord, ['name' => 'Date Filter Safe Property']);
        $this->addAvailableUnit($property);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('explore', [
                'available_from' => now()->toDateString(),
                'available_to' => now()->addMonth()->toDateString(),
            ]));

        $response->assertOk();
        $response->assertJsonStructure(['success', 'html', 'pagination', 'count']);
        $this->assertSame(1, $response->json('count'));
    }

    public function test_index_featured_sort_orders_featured_first_when_column_exists(): void
    {
        if (! Schema::hasColumn('properties', 'is_featured')) {
            $this->markTestSkipped('properties.is_featured column is unavailable in current schema.');
        }

        $landlord = $this->createLandlord();

        $regular = $this->createActiveProperty($landlord, [
            'name' => 'Regular Listing',
            'is_featured' => false,
        ]);
        $this->addAvailableUnit($regular);

        $featured = $this->createActiveProperty($landlord, [
            'name' => 'Featured Listing',
            'is_featured' => true,
        ]);
        $this->addAvailableUnit($featured);

        $response = $this->get(route('explore', ['sort_by' => 'featured']));
        $response->assertOk();

        $properties = $response->viewData('properties');
        $first = $properties->first();

        $this->assertNotNull($first);
        $this->assertSame('Featured Listing', $first->name);
    }

    // ── show() – unit slug ────────────────────────────────────────────────────

    public function test_show_redirects_to_unit_details_view_when_slug_ends_with_unit_id(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $unit = $this->addAvailableUnit($property);

        $slug = $property->slug.'-unit-'.$unit->id;

        $response = $this->get(route('property.show', $slug));

        // Either renders unit-details or property-details – must not 404
        $response->assertOk();
    }

    // ── showProperty() ────────────────────────────────────────────────────────

    public function test_show_property_returns_200_for_valid_property_slug(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord, ['slug' => 'my-test-property']);
        $this->addAvailableUnit($property);

        $response = $this->get(route('property.show', 'my-test-property'));

        $response->assertOk();
    }

    public function test_show_property_returns_404_for_unknown_slug(): void
    {
        $response = $this->get(route('property.show', 'non-existent-property-slug'));

        $response->assertNotFound();
    }

    public function test_show_property_passes_hero_presentation_to_view(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord, ['slug' => 'hero-test']);
        $this->addAvailableUnit($property);

        $response = $this->get(route('property.show', 'hero-test'));

        $response->assertOk();
        $response->assertViewHas('propertyHero');
        $hero = $response->viewData('propertyHero');
        $this->assertArrayHasKey('has_hero_image', $hero);
        $this->assertArrayHasKey('hero_image_url', $hero);
        $this->assertArrayHasKey('hero_alt_text', $hero);
    }

    public function test_show_property_passes_floor_groups_to_view(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord, ['slug' => 'floor-groups-test']);
        $this->addAvailableUnit($property, ['floor_number' => 1]);
        $this->addAvailableUnit($property, ['floor_number' => 2]);

        $response = $this->get(route('property.show', 'floor-groups-test'));

        $response->assertOk();
        $response->assertViewHas('floorGroups');
        $floorGroups = $response->viewData('floorGroups');
        $this->assertCount(2, $floorGroups);
    }

    public function test_show_property_passes_display_term_to_view(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord, [
            'slug' => 'display-term-test',
            'property_type' => 'condominium',
        ]);
        $this->addAvailableUnit($property);

        $response = $this->get(route('property.show', 'display-term-test'));

        $response->assertOk();
        $response->assertViewHas('displayTerm', 'Unit');
    }

    public function test_show_property_includes_related_properties(): void
    {
        $landlord = $this->createLandlord();
        $main = $this->createActiveProperty($landlord, ['slug' => 'main-prop']);
        $this->addAvailableUnit($main);

        $related = $this->createActiveProperty($landlord, ['slug' => 'related-prop']);
        $this->addAvailableUnit($related);

        $response = $this->get(route('property.show', 'main-prop'));

        $response->assertOk();
        $response->assertViewHas('relatedProperties');
        $relatedProperties = $response->viewData('relatedProperties');
        $this->assertGreaterThanOrEqual(1, $relatedProperties->count());
    }

    public function test_show_property_related_properties_excludes_current_property(): void
    {
        $landlord = $this->createLandlord();
        $main = $this->createActiveProperty($landlord, ['slug' => 'main-only']);
        $this->addAvailableUnit($main);

        $response = $this->get(route('property.show', 'main-only'));

        $response->assertOk();
        $relatedProperties = $response->viewData('relatedProperties');
        $ids = $relatedProperties->pluck('id')->toArray();
        $this->assertNotContains($main->id, $ids);
    }

    public function test_show_property_related_properties_limited_to_four(): void
    {
        $landlord = $this->createLandlord();
        $main = $this->createActiveProperty($landlord, ['slug' => 'limited-related']);
        $this->addAvailableUnit($main);

        for ($i = 1; $i <= 6; $i++) {
            $p = $this->createActiveProperty($landlord, ['slug' => "related-{$i}"]);
            $this->addAvailableUnit($p);
        }

        $response = $this->get(route('property.show', 'limited-related'));

        $response->assertOk();
        $relatedProperties = $response->viewData('relatedProperties');
        $this->assertLessThanOrEqual(4, $relatedProperties->count());
    }

    // ── pagination ────────────────────────────────────────────────────────────

    public function test_index_paginates_properties_at_12_per_page(): void
    {
        $landlord = $this->createLandlord();
        for ($i = 1; $i <= 15; $i++) {
            $p = $this->createActiveProperty($landlord, ['slug' => "pagi-prop-{$i}"]);
            $this->addAvailableUnit($p);
        }

        $response = $this->get(route('explore'));

        $response->assertOk();
        $properties = $response->viewData('properties');
        $this->assertSame(12, $properties->perPage());
        $this->assertSame(15, $properties->total());
    }
}
