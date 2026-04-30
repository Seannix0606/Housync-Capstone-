<?php

namespace Tests\Feature\Explore;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreControllerFilterTest extends TestCase
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

    private function createActiveProperty(User $landlord, array $propertyAttributes = []): Property
    {
        return Property::factory()->create(array_merge([
            'landlord_id' => $landlord->id,
            'status' => 'active',
            'is_active' => true,
        ], $propertyAttributes));
    }

    private function addAvailableUnit(Property $property, array $unitAttributes = []): Unit
    {
        return Unit::factory()->create(array_merge([
            'property_id' => $property->id,
            'status' => 'available',
        ], $unitAttributes));
    }

    private function addOccupiedUnit(Property $property, array $unitAttributes = []): Unit
    {
        return Unit::factory()->create(array_merge([
            'property_id' => $property->id,
            'status' => 'occupied',
        ], $unitAttributes));
    }

    // ── index – view routing ──────────────────────────────────────────────────

    public function test_index_returns_explore_view_for_unauthenticated_guests(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);

        $response = $this->get(route('explore'));

        $response->assertOk();
        $response->assertViewIs('explore');
    }

    public function test_index_returns_tenant_explore_view_for_authenticated_tenant(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get(route('explore'));

        $response->assertOk();
        $response->assertViewIs('tenant.explore');
    }

    public function test_index_passes_properties_variable_to_view(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);

        $response = $this->get(route('explore'));

        $response->assertOk();
        $response->assertViewHas('properties');
    }

    public function test_index_returns_only_active_properties_with_available_units(): void
    {
        $landlord = $this->createLandlord();

        $activeProperty = $this->createActiveProperty($landlord, ['name' => 'Active With Units']);
        $this->addAvailableUnit($activeProperty);

        $inactiveProperty = $this->createActiveProperty($landlord, [
            'name' => 'Inactive Property',
            'status' => 'inactive',
        ]);
        $this->addAvailableUnit($inactiveProperty);

        $noUnitsProperty = $this->createActiveProperty($landlord, ['name' => 'No Available Units']);
        $this->addOccupiedUnit($noUnitsProperty);

        $response = $this->get(route('explore'));

        $response->assertOk();
        $response->assertSee('Active With Units');
        $response->assertDontSee('Inactive Property');
        $response->assertDontSee('No Available Units');
    }

    // ── index – AJAX routing ──────────────────────────────────────────────────

    public function test_index_delegates_to_filter_properties_for_ajax_requests(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);

        $response = $this->get(route('explore'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'html', 'pagination', 'count']);
    }

    // ── filterProperties – AJAX response ─────────────────────────────────────

    public function test_filter_properties_returns_json_for_ajax_request(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);

        $response = $this->get(route('explore'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'html',
            'pagination',
            'count',
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_filter_properties_count_reflects_matching_properties(): void
    {
        $landlord = $this->createLandlord();

        $property1 = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property1);

        $property2 = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property2);

        $response = $this->get(route('explore'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson(['count' => 2]);
    }

    // ── buildPropertyQuery – type filter ─────────────────────────────────────

    public function test_type_filter_returns_only_matching_property_type(): void
    {
        $landlord = $this->createLandlord();

        $apartmentProperty = $this->createActiveProperty($landlord, [
            'property_type' => 'apartment',
            'name' => 'Apartment Property',
        ]);
        $this->addAvailableUnit($apartmentProperty);

        $condoProperty = $this->createActiveProperty($landlord, [
            'property_type' => 'condominium',
            'name' => 'Condo Property',
        ]);
        $this->addAvailableUnit($condoProperty);

        $response = $this->get(route('explore'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        parse_str('type=apartment', $query);

        $response = $this->get(route('explore').'?type=apartment', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertEquals(1, $data['count']);
        $this->assertStringContainsString('Apartment Property', $data['html']);
        $this->assertStringNotContainsString('Condo Property', $data['html']);
    }

    // ── buildPropertyQuery – search filter ───────────────────────────────────

    public function test_search_filter_matches_property_name(): void
    {
        $landlord = $this->createLandlord();

        $matchProp = $this->createActiveProperty($landlord, ['name' => 'Greenfield Residences']);
        $this->addAvailableUnit($matchProp);

        $noMatchProp = $this->createActiveProperty($landlord, ['name' => 'Blue Tower']);
        $this->addAvailableUnit($noMatchProp);

        $response = $this->get(route('explore').'?search=Greenfield', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
        $this->assertStringContainsString('Greenfield', $response->json('html'));
    }

    public function test_search_filter_matches_city(): void
    {
        $landlord = $this->createLandlord();

        $matchProp = $this->createActiveProperty($landlord, [
            'name' => 'Some Property',
            'city' => 'Makati',
        ]);
        $this->addAvailableUnit($matchProp);

        $noMatchProp = $this->createActiveProperty($landlord, [
            'name' => 'Other Property',
            'city' => 'Taguig',
        ]);
        $this->addAvailableUnit($noMatchProp);

        $response = $this->get(route('explore').'?search=Makati', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
    }

    public function test_search_filter_matches_address(): void
    {
        $landlord = $this->createLandlord();

        $matchProp = $this->createActiveProperty($landlord, [
            'name' => 'Address Property',
            'address' => '123 Rizal Avenue',
        ]);
        $this->addAvailableUnit($matchProp);

        $noMatchProp = $this->createActiveProperty($landlord, [
            'name' => 'Another Property',
            'address' => '456 Other Street',
        ]);
        $this->addAvailableUnit($noMatchProp);

        $response = $this->get(route('explore').'?search=Rizal+Avenue', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
    }

    public function test_search_filter_matches_landlord_name(): void
    {
        $matchingLandlord = User::factory()->create([
            'role' => 'landlord',
            'name' => 'Carlos Santos',
        ]);
        $otherLandlord = $this->createLandlord();

        $matchProp = $this->createActiveProperty($matchingLandlord, ['name' => 'Santos Property']);
        $this->addAvailableUnit($matchProp);

        $noMatchProp = $this->createActiveProperty($otherLandlord, ['name' => 'Other Property']);
        $this->addAvailableUnit($noMatchProp);

        $response = $this->get(route('explore').'?search=Carlos+Santos', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
    }

    // ── buildPropertyQuery – price filters ───────────────────────────────────

    public function test_min_price_filter_excludes_properties_with_no_units_at_or_above_min(): void
    {
        $landlord = $this->createLandlord();

        $cheapProperty = $this->createActiveProperty($landlord, ['name' => 'Cheap Property']);
        $this->addAvailableUnit($cheapProperty, ['rent_amount' => 3000]);

        $expensiveProperty = $this->createActiveProperty($landlord, ['name' => 'Expensive Property']);
        $this->addAvailableUnit($expensiveProperty, ['rent_amount' => 10000]);

        $response = $this->get(route('explore').'?min_price=8000', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
        $this->assertStringContainsString('Expensive Property', $response->json('html'));
        $this->assertStringNotContainsString('Cheap Property', $response->json('html'));
    }

    public function test_max_price_filter_excludes_properties_with_no_units_at_or_below_max(): void
    {
        $landlord = $this->createLandlord();

        $cheapProperty = $this->createActiveProperty($landlord, ['name' => 'Budget Property']);
        $this->addAvailableUnit($cheapProperty, ['rent_amount' => 5000]);

        $expensiveProperty = $this->createActiveProperty($landlord, ['name' => 'Luxury Property']);
        $this->addAvailableUnit($expensiveProperty, ['rent_amount' => 25000]);

        $response = $this->get(route('explore').'?max_price=10000', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
        $this->assertStringContainsString('Budget Property', $response->json('html'));
        $this->assertStringNotContainsString('Luxury Property', $response->json('html'));
    }

    public function test_min_and_max_price_combined_filter_works(): void
    {
        $landlord = $this->createLandlord();

        $tooLow = $this->createActiveProperty($landlord, ['name' => 'Too Cheap']);
        $this->addAvailableUnit($tooLow, ['rent_amount' => 2000]);

        $inRange = $this->createActiveProperty($landlord, ['name' => 'In Range']);
        $this->addAvailableUnit($inRange, ['rent_amount' => 7000]);

        $tooHigh = $this->createActiveProperty($landlord, ['name' => 'Too Expensive']);
        $this->addAvailableUnit($tooHigh, ['rent_amount' => 20000]);

        $response = $this->get(route('explore').'?min_price=5000&max_price=10000', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
        $this->assertStringContainsString('In Range', $response->json('html'));
    }

    // ── buildPropertyQuery – availability filter ─────────────────────────────

    public function test_default_availability_shows_properties_with_available_units_only(): void
    {
        $landlord = $this->createLandlord();

        $withAvailable = $this->createActiveProperty($landlord, ['name' => 'Has Available']);
        $this->addAvailableUnit($withAvailable);

        $fullyOccupied = $this->createActiveProperty($landlord, ['name' => 'Fully Occupied']);
        $this->addOccupiedUnit($fullyOccupied);

        $response = $this->get(route('explore').'?availability=available', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
        $this->assertStringContainsString('Has Available', $response->json('html'));
    }

    public function test_occupied_availability_shows_properties_without_available_units(): void
    {
        $landlord = $this->createLandlord();

        $withAvailable = $this->createActiveProperty($landlord, ['name' => 'Has Available']);
        $this->addAvailableUnit($withAvailable);

        $fullyOccupied = $this->createActiveProperty($landlord, ['name' => 'Fully Occupied']);
        $this->addOccupiedUnit($fullyOccupied);

        $response = $this->get(route('explore').'?availability=occupied', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 1]);
        $this->assertStringContainsString('Fully Occupied', $response->json('html'));
    }

    // ── buildPropertyQuery – sorting ──────────────────────────────────────────

    public function test_sort_by_latest_is_the_default_order(): void
    {
        $landlord = $this->createLandlord();

        // Creating in order, so later-created property should appear first
        $olderProperty = $this->createActiveProperty($landlord, ['name' => 'Older Property']);
        $this->addAvailableUnit($olderProperty);

        $newerProperty = $this->createActiveProperty($landlord, ['name' => 'Newer Property']);
        $this->addAvailableUnit($newerProperty);

        $response = $this->get(route('explore'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $html = $response->json('html');
        // Newer should appear before older in the HTML
        $this->assertLessThan(strpos($html, 'Older Property'), strpos($html, 'Newer Property'));
    }

    // ── showProperty – 404 handling ───────────────────────────────────────────

    public function test_show_returns_404_for_non_existent_property_slug(): void
    {
        $response = $this->get(route('property.show', 'non-existent-slug-abc123'));

        $response->assertNotFound();
    }

    // ── showProperty – active property with units ─────────────────────────────

    public function test_show_property_renders_property_details_view(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord, [
            'name' => 'Show Property Test',
            'slug' => 'show-property-test',
        ]);
        $this->addAvailableUnit($property, ['floor_number' => 1]);

        $response = $this->get(route('property.show', $property->slug));

        $response->assertOk();
        $response->assertViewIs('property-details');
        $response->assertViewHas('property');
        $response->assertViewHas('propertyHero');
        $response->assertViewHas('displayTerm');
        $response->assertViewHas('floorGroups');
    }

    public function test_show_property_passes_related_properties_in_view_data(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord, ['slug' => 'main-property']);
        $this->addAvailableUnit($property);

        $response = $this->get(route('property.show', $property->slug));

        $response->assertViewHas('relatedProperties');
    }

    // ── show() – unit ID suffix routing ──────────────────────────────────────

    public function test_show_with_unit_id_suffix_routes_to_unit_details(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord, ['slug' => 'some-property']);
        $unit = $this->addAvailableUnit($property, ['floor_number' => 1]);

        // The slug format for unit deep-link is "{property-slug}-unit-{unitId}"
        $slug = $property->slug.'-unit-'.$unit->id;
        $response = $this->get(route('property.show', $slug));

        $response->assertOk();
        $response->assertViewIs('unit-details');
    }

    // ── active scope enforcement ──────────────────────────────────────────────

    public function test_inactive_properties_not_returned_in_explore(): void
    {
        $landlord = $this->createLandlord();

        $inactive = $this->createActiveProperty($landlord, [
            'name' => 'Inactive Building',
            'status' => 'inactive',
        ]);
        $this->addAvailableUnit($inactive);

        $response = $this->get(route('explore'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 0]);
    }

    public function test_is_active_false_properties_not_returned_in_explore(): void
    {
        $landlord = $this->createLandlord();

        $deactivated = $this->createActiveProperty($landlord, [
            'name' => 'Deactivated Building',
            'is_active' => false,
            'status' => 'active',
        ]);
        $this->addAvailableUnit($deactivated);

        $response = $this->get(route('explore'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertJson(['count' => 0]);
    }

    // ── aggregate columns in view data ───────────────────────────────────────

    public function test_properties_have_available_units_count_aggregate(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property);
        $this->addAvailableUnit($property);
        $this->addOccupiedUnit($property);

        $response = $this->get(route('explore'));

        $response->assertOk();
        $viewProperties = $response->viewData('properties');
        $firstProperty = $viewProperties->first();
        // available_units_count should be 2 (only available, not occupied)
        $this->assertEquals(2, $firstProperty->available_units_count);
    }

    public function test_properties_have_min_available_rent_aggregate(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createActiveProperty($landlord);
        $this->addAvailableUnit($property, ['rent_amount' => 9000]);
        $this->addAvailableUnit($property, ['rent_amount' => 6000]);
        $this->addOccupiedUnit($property, ['rent_amount' => 4000]); // occupied, should be excluded

        $response = $this->get(route('explore'));

        $response->assertOk();
        $viewProperties = $response->viewData('properties');
        $firstProperty = $viewProperties->first();
        // min_available_rent should be 6000, not 4000 (occupied)
        $this->assertEquals(6000, (float) $firstProperty->min_available_rent);
    }
}