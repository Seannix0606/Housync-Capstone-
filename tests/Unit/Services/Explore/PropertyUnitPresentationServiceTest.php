<?php

namespace Tests\Unit\Services\Explore;

use App\Models\Property;
use App\Models\Unit;
use App\Services\Explore\PropertyUnitPresentationService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PropertyUnitPresentationServiceTest extends TestCase
{
    private PropertyUnitPresentationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PropertyUnitPresentationService;
    }

    /**
     * Create a Property instance without hitting the DB.
     */
    private function makeProperty(array $attributes = [], array $units = []): Property
    {
        $property = new Property;
        $property->setRawAttributes(array_merge([
            'id' => 1,
            'name' => 'Test Property',
            'slug' => 'test-property',
            'property_type' => 'apartment',
            'cover_image' => null,
            'gallery' => null,
        ], $attributes));

        $unitCollection = collect($units);
        $property->setRelation('units', $unitCollection);

        return $property;
    }

    /**
     * Create a Unit instance without hitting the DB.
     */
    private function makeUnit(array $attributes = []): Unit
    {
        $unit = new Unit;
        $unit->setRawAttributes(array_merge([
            'id' => 1,
            'property_id' => 1,
            'unit_number' => '101',
            'unit_type' => 'Studio',
            'rent_amount' => '5000.00',
            'status' => 'available',
            'floor_number' => 1,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'floor_area' => null,
            'cover_image' => null,
            'gallery' => null,
        ], $attributes));

        return $unit;
    }

    // ── displayTerm ──────────────────────────────────────────────────────────

    public function test_display_term_is_room_for_apartment(): void
    {
        $property = $this->makeProperty(['property_type' => 'apartment']);

        $result = $this->service->build($property);

        $this->assertSame('Room', $result['displayTerm']);
    }

    public function test_display_term_is_room_for_house(): void
    {
        $property = $this->makeProperty(['property_type' => 'house']);

        $result = $this->service->build($property);

        $this->assertSame('Room', $result['displayTerm']);
    }

    public function test_display_term_is_unit_for_condominium(): void
    {
        $property = $this->makeProperty(['property_type' => 'condominium']);

        $result = $this->service->build($property);

        $this->assertSame('Unit', $result['displayTerm']);
    }

    public function test_display_term_is_unit_for_townhouse(): void
    {
        $property = $this->makeProperty(['property_type' => 'townhouse']);

        $result = $this->service->build($property);

        $this->assertSame('Unit', $result['displayTerm']);
    }

    public function test_display_term_is_unit_when_property_type_is_null(): void
    {
        $property = $this->makeProperty(['property_type' => null]);

        $result = $this->service->build($property);

        $this->assertSame('Unit', $result['displayTerm']);
    }

    // ── empty units ───────────────────────────────────────────────────────────

    public function test_build_returns_empty_floor_groups_when_property_has_no_units(): void
    {
        $property = $this->makeProperty();

        $result = $this->service->build($property);

        $this->assertSame([], $result['floorGroups']);
    }

    public function test_build_handles_non_collection_units_gracefully(): void
    {
        // Simulate a property where the units relation is explicitly set to null (not a Collection)
        $property = new Property;
        $property->setRawAttributes(['id' => 99, 'property_type' => 'apartment', 'slug' => 'test']);
        // Set the units relation to null to simulate a non-Collection value
        $property->setRelation('units', null);

        $result = $this->service->build($property);

        $this->assertSame([], $result['floorGroups']);
    }

    // ── floor grouping ────────────────────────────────────────────────────────

    public function test_build_groups_units_by_floor_number(): void
    {
        $unit1 = $this->makeUnit(['id' => 1, 'unit_number' => '101', 'floor_number' => 1, 'rent_amount' => '5000.00']);
        $unit2 = $this->makeUnit(['id' => 2, 'unit_number' => '201', 'floor_number' => 2, 'rent_amount' => '6000.00']);
        $property = $this->makeProperty(['property_type' => 'condominium'], [$unit1, $unit2]);

        $result = $this->service->build($property);

        $floorKeys = array_column($result['floorGroups'], 'floor_key');
        $this->assertContains('1', $floorKeys);
        $this->assertContains('2', $floorKeys);
        $this->assertCount(2, $result['floorGroups']);
    }

    public function test_build_places_unspecified_floor_last_when_floor_is_null(): void
    {
        $unitNoFloor = $this->makeUnit(['id' => 10, 'unit_number' => 'G1', 'floor_number' => null, 'rent_amount' => '3000.00']);
        $unitFloor1 = $this->makeUnit(['id' => 11, 'unit_number' => '101', 'floor_number' => 1, 'rent_amount' => '5000.00']);
        $property = $this->makeProperty([], [$unitNoFloor, $unitFloor1]);

        $result = $this->service->build($property);

        $this->assertCount(2, $result['floorGroups']);
        $lastGroup = end($result['floorGroups']);
        $this->assertSame('unspecified', $lastGroup['floor_key']);
        $this->assertSame('Unspecified Floor', $lastGroup['floor_label']);
    }

    public function test_build_places_floor_zero_in_unspecified_group(): void
    {
        $unitFloor0 = $this->makeUnit(['id' => 20, 'unit_number' => 'B1', 'floor_number' => 0, 'rent_amount' => '4000.00']);
        $property = $this->makeProperty([], [$unitFloor0]);

        $result = $this->service->build($property);

        $this->assertCount(1, $result['floorGroups']);
        $this->assertSame('unspecified', $result['floorGroups'][0]['floor_key']);
    }

    public function test_build_sorts_floor_groups_numerically_ascending(): void
    {
        $unit3 = $this->makeUnit(['id' => 31, 'unit_number' => '301', 'floor_number' => 3, 'rent_amount' => '7000.00']);
        $unit1 = $this->makeUnit(['id' => 32, 'unit_number' => '101', 'floor_number' => 1, 'rent_amount' => '5000.00']);
        $unit2 = $this->makeUnit(['id' => 33, 'unit_number' => '201', 'floor_number' => 2, 'rent_amount' => '6000.00']);
        $property = $this->makeProperty([], [$unit3, $unit1, $unit2]);

        $result = $this->service->build($property);

        $floorKeys = array_column($result['floorGroups'], 'floor_key');
        $this->assertSame(['1', '2', '3'], $floorKeys);
    }

    // ── within-floor unit sorting ─────────────────────────────────────────────

    public function test_build_sorts_units_by_rent_ascending_within_floor(): void
    {
        $cheapUnit = $this->makeUnit(['id' => 41, 'unit_number' => '105', 'floor_number' => 1, 'rent_amount' => '4000.00']);
        $expensiveUnit = $this->makeUnit(['id' => 42, 'unit_number' => '102', 'floor_number' => 1, 'rent_amount' => '8000.00']);
        $property = $this->makeProperty([], [$expensiveUnit, $cheapUnit]);

        $result = $this->service->build($property);

        $units = $result['floorGroups'][0]['units'];
        $this->assertSame(41, $units[0]['id']);
        $this->assertSame(42, $units[1]['id']);
    }

    public function test_build_breaks_rent_ties_by_unit_number_natural_sort(): void
    {
        $unitA = $this->makeUnit(['id' => 51, 'unit_number' => '109', 'floor_number' => 1, 'rent_amount' => '5000.00']);
        $unitB = $this->makeUnit(['id' => 52, 'unit_number' => '101', 'floor_number' => 1, 'rent_amount' => '5000.00']);
        $property = $this->makeProperty([], [$unitA, $unitB]);

        $result = $this->service->build($property);

        $units = $result['floorGroups'][0]['units'];
        // Natural sort: '101' < '109'
        $this->assertSame(52, $units[0]['id']);
        $this->assertSame(51, $units[1]['id']);
    }

    // ── unit view-model fields ────────────────────────────────────────────────

    public function test_build_unit_label_uses_display_term_and_unit_number(): void
    {
        $unit = $this->makeUnit(['id' => 61, 'unit_number' => '201', 'floor_number' => 2, 'rent_amount' => '5500.00']);
        $property = $this->makeProperty(['property_type' => 'condominium', 'slug' => 'condo-x'], [$unit]);

        $result = $this->service->build($property);

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertSame('Unit 201', $unitVm['label']);
    }

    public function test_build_unit_label_uses_room_term_for_apartment(): void
    {
        $unit = $this->makeUnit(['id' => 62, 'unit_number' => '101', 'floor_number' => 1, 'rent_amount' => '4000.00']);
        $property = $this->makeProperty(['property_type' => 'apartment', 'slug' => 'apt-y'], [$unit]);

        $result = $this->service->build($property);

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertSame('Room 101', $unitVm['label']);
    }

    public function test_build_rent_display_is_formatted_correctly(): void
    {
        $unit = $this->makeUnit(['id' => 71, 'unit_number' => '301', 'floor_number' => 3, 'rent_amount' => '12500.00']);
        $property = $this->makeProperty([], [$unit]);

        $result = $this->service->build($property);

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertSame('₱12,500.00/month', $unitVm['rent_display']);
    }

    public function test_build_available_count_reflects_unit_count_per_floor(): void
    {
        $unitA = $this->makeUnit(['id' => 81, 'unit_number' => '101', 'floor_number' => 1, 'rent_amount' => '5000.00']);
        $unitB = $this->makeUnit(['id' => 82, 'unit_number' => '102', 'floor_number' => 1, 'rent_amount' => '5500.00']);
        $unitC = $this->makeUnit(['id' => 83, 'unit_number' => '201', 'floor_number' => 2, 'rent_amount' => '6000.00']);
        $property = $this->makeProperty([], [$unitA, $unitB, $unitC]);

        $result = $this->service->build($property);

        $floor1 = $result['floorGroups'][0];
        $floor2 = $result['floorGroups'][1];
        $this->assertSame(2, $floor1['available_count']);
        $this->assertSame(1, $floor2['available_count']);
    }

    public function test_build_floor_label_is_formatted_as_floor_n(): void
    {
        $unit = $this->makeUnit(['id' => 91, 'unit_number' => '501', 'floor_number' => 5, 'rent_amount' => '9000.00']);
        $property = $this->makeProperty([], [$unit]);

        $result = $this->service->build($property);

        $this->assertSame('Floor 5', $result['floorGroups'][0]['floor_label']);
    }

    // ── image resolution ──────────────────────────────────────────────────────

    public function test_unit_image_url_uses_unit_cover_when_present(): void
    {
        $unit = $this->makeUnit([
            'id' => 101,
            'unit_number' => '101',
            'floor_number' => 1,
            'rent_amount' => '5000.00',
            'cover_image' => 'https://cdn.example.com/unit-cover.jpg',
            'gallery' => null,
        ]);
        $property = $this->makeProperty([], [$unit]);

        $result = $this->service->build($property);

        $this->assertSame('https://cdn.example.com/unit-cover.jpg', $result['floorGroups'][0]['units'][0]['image_url']);
    }

    public function test_unit_image_url_falls_back_to_unit_gallery_when_cover_is_null(): void
    {
        $unit = $this->makeUnit([
            'id' => 102,
            'unit_number' => '102',
            'floor_number' => 1,
            'rent_amount' => '5000.00',
            'cover_image' => null,
            'gallery' => ['https://cdn.example.com/unit-gallery-0.jpg'],
        ]);
        $property = $this->makeProperty(['cover_image' => null, 'gallery' => null], [$unit]);

        $result = $this->service->build($property);

        $this->assertSame('https://cdn.example.com/unit-gallery-0.jpg', $result['floorGroups'][0]['units'][0]['image_url']);
    }

    public function test_unit_image_url_falls_back_to_property_cover_when_unit_has_no_media(): void
    {
        $unit = $this->makeUnit([
            'id' => 103,
            'unit_number' => '103',
            'floor_number' => 1,
            'rent_amount' => '5000.00',
            'cover_image' => null,
            'gallery' => null,
        ]);
        $property = $this->makeProperty([
            'cover_image' => 'https://cdn.example.com/property-cover.jpg',
            'gallery' => null,
        ], [$unit]);

        $result = $this->service->build($property);

        $this->assertSame('https://cdn.example.com/property-cover.jpg', $result['floorGroups'][0]['units'][0]['image_url']);
    }

    public function test_unit_image_url_falls_back_to_property_gallery_when_unit_and_property_cover_are_null(): void
    {
        $unit = $this->makeUnit([
            'id' => 104,
            'unit_number' => '104',
            'floor_number' => 1,
            'rent_amount' => '5000.00',
            'cover_image' => null,
            'gallery' => null,
        ]);
        $property = $this->makeProperty([
            'cover_image' => null,
            'gallery' => ['https://cdn.example.com/property-gallery-0.jpg'],
        ], [$unit]);

        $result = $this->service->build($property);

        $this->assertSame('https://cdn.example.com/property-gallery-0.jpg', $result['floorGroups'][0]['units'][0]['image_url']);
    }

    public function test_unit_image_url_is_null_when_all_media_sources_are_empty(): void
    {
        $unit = $this->makeUnit([
            'id' => 105,
            'unit_number' => '105',
            'floor_number' => 1,
            'rent_amount' => '5000.00',
            'cover_image' => null,
            'gallery' => null,
        ]);
        $property = $this->makeProperty(['cover_image' => null, 'gallery' => null], [$unit]);

        $result = $this->service->build($property);

        $this->assertNull($result['floorGroups'][0]['units'][0]['image_url']);
    }

    // ── details_url ───────────────────────────────────────────────────────────

    public function test_unit_details_url_contains_property_slug_and_unit_id(): void
    {
        $unit = $this->makeUnit(['id' => 111, 'unit_number' => '111', 'floor_number' => 1, 'rent_amount' => '5000.00']);
        $property = $this->makeProperty(['slug' => 'my-property'], [$unit]);

        $result = $this->service->build($property);

        $detailsUrl = $result['floorGroups'][0]['units'][0]['details_url'];
        $this->assertStringContainsString('my-property', $detailsUrl);
        $this->assertStringContainsString('111', $detailsUrl);
    }
}