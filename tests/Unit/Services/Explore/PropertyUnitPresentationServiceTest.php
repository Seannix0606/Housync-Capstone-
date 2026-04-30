<?php

namespace Tests\Unit\Services\Explore;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Services\Explore\PropertyUnitPresentationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PropertyUnitPresentationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PropertyUnitPresentationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PropertyUnitPresentationService;
    }

    private function makeProperty(string $propertyType = 'apartment', array $extra = []): Property
    {
        $landlord = User::factory()->create(['role' => 'landlord']);

        return Property::factory()->create(array_merge([
            'landlord_id' => $landlord->id,
            'property_type' => $propertyType,
            'slug' => 'test-property-'.uniqid(),
            'status' => 'active',
            'is_active' => true,
        ], $extra));
    }

    private function addUnit(Property $property, array $attributes = []): Unit
    {
        return Unit::factory()->create(array_merge([
            'property_id' => $property->id,
            'status' => 'available',
        ], $attributes));
    }

    /**
     * Load property with units relation the same way the controller does.
     */
    private function loadProperty(Property $property): Property
    {
        return $property->fresh(['units']);
    }

    // ── displayTerm ─────────────────────────────────────────────────────────

    public function test_display_term_is_room_for_apartment(): void
    {
        $property = $this->loadProperty($this->makeProperty('apartment'));

        $result = $this->service->build($property);

        $this->assertEquals('Room', $result['displayTerm']);
    }

    public function test_display_term_is_room_for_house(): void
    {
        $property = $this->loadProperty($this->makeProperty('house'));

        $result = $this->service->build($property);

        $this->assertEquals('Room', $result['displayTerm']);
    }

    public function test_display_term_is_unit_for_condominium(): void
    {
        $property = $this->loadProperty($this->makeProperty('condominium'));

        $result = $this->service->build($property);

        $this->assertEquals('Unit', $result['displayTerm']);
    }

    public function test_display_term_is_unit_for_townhouse(): void
    {
        $property = $this->loadProperty($this->makeProperty('townhouse'));

        $result = $this->service->build($property);

        $this->assertEquals('Unit', $result['displayTerm']);
    }

    public function test_display_term_is_unit_for_duplex(): void
    {
        $property = $this->loadProperty($this->makeProperty('duplex'));

        $result = $this->service->build($property);

        $this->assertEquals('Unit', $result['displayTerm']);
    }

    // ── empty units ─────────────────────────────────────────────────────────

    public function test_returns_empty_floor_groups_when_property_has_no_units(): void
    {
        $property = $this->loadProperty($this->makeProperty());

        $result = $this->service->build($property);

        $this->assertIsArray($result['floorGroups']);
        $this->assertEmpty($result['floorGroups']);
    }

    public function test_returns_empty_floor_groups_when_units_relation_is_not_collection(): void
    {
        $property = $this->makeProperty();
        // Do NOT load the relation – property->units will be a HasMany, not a Collection
        // The service guards: $property->units instanceof Collection ? ... : collect()

        $result = $this->service->build($property);

        $this->assertEmpty($result['floorGroups']);
    }

    // ── floor grouping ───────────────────────────────────────────────────────

    public function test_groups_units_by_floor_number(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 5000, 'unit_number' => '101']);
        $this->addUnit($property, ['floor_number' => 2, 'rent_amount' => 6000, 'unit_number' => '201']);

        $result = $this->service->build($this->loadProperty($property));

        $this->assertCount(2, $result['floorGroups']);
        $floorKeys = array_column($result['floorGroups'], 'floor_key');
        $this->assertContains('1', $floorKeys);
        $this->assertContains('2', $floorKeys);
    }

    public function test_null_floor_number_maps_to_unspecified(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => null, 'unit_number' => 'GF1']);

        $result = $this->service->build($this->loadProperty($property));

        $this->assertCount(1, $result['floorGroups']);
        $this->assertEquals('unspecified', $result['floorGroups'][0]['floor_key']);
        $this->assertEquals('Unspecified Floor', $result['floorGroups'][0]['floor_label']);
    }

    public function test_floor_number_zero_maps_to_unspecified(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => 0, 'unit_number' => 'X1']);

        $result = $this->service->build($this->loadProperty($property));

        $this->assertCount(1, $result['floorGroups']);
        $this->assertEquals('unspecified', $result['floorGroups'][0]['floor_key']);
    }

    public function test_negative_floor_number_maps_to_unspecified(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => -1, 'unit_number' => 'B1']);

        $result = $this->service->build($this->loadProperty($property));

        $this->assertEquals('unspecified', $result['floorGroups'][0]['floor_key']);
    }

    public function test_unspecified_floor_group_is_placed_last(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => null, 'rent_amount' => 4000, 'unit_number' => 'G1']);
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 5000, 'unit_number' => '101']);
        $this->addUnit($property, ['floor_number' => 2, 'rent_amount' => 6000, 'unit_number' => '201']);

        $result = $this->service->build($this->loadProperty($property));

        $lastGroup = end($result['floorGroups']);
        $this->assertEquals('unspecified', $lastGroup['floor_key']);
    }

    public function test_floor_groups_are_sorted_numerically_ascending(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => 3, 'unit_number' => '301']);
        $this->addUnit($property, ['floor_number' => 1, 'unit_number' => '101']);
        $this->addUnit($property, ['floor_number' => 2, 'unit_number' => '201']);

        $result = $this->service->build($this->loadProperty($property));

        $floorKeys = array_column($result['floorGroups'], 'floor_key');
        $this->assertEquals(['1', '2', '3'], $floorKeys);
    }

    public function test_floor_label_format_is_floor_n(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => 5, 'unit_number' => '501']);

        $result = $this->service->build($this->loadProperty($property));

        $this->assertEquals('Floor 5', $result['floorGroups'][0]['floor_label']);
    }

    public function test_available_count_reflects_unit_count_per_floor(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => 1, 'unit_number' => '101']);
        $this->addUnit($property, ['floor_number' => 1, 'unit_number' => '102']);
        $this->addUnit($property, ['floor_number' => 2, 'unit_number' => '201']);

        $result = $this->service->build($this->loadProperty($property));

        $floor1 = collect($result['floorGroups'])->firstWhere('floor_key', '1');
        $floor2 = collect($result['floorGroups'])->firstWhere('floor_key', '2');

        $this->assertEquals(2, $floor1['available_count']);
        $this->assertEquals(1, $floor2['available_count']);
    }

    // ── unit sorting ─────────────────────────────────────────────────────────

    public function test_units_sorted_by_rent_amount_ascending(): void
    {
        $property = $this->makeProperty('condominium');
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 9000, 'unit_number' => 'A']);
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 7000, 'unit_number' => 'B']);
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 8000, 'unit_number' => 'C']);

        $result = $this->service->build($this->loadProperty($property));

        $floor1Units = $result['floorGroups'][0]['units'];
        $rents = array_column($floor1Units, 'rent_display');

        $this->assertStringContainsString('7,000', $rents[0]);
        $this->assertStringContainsString('8,000', $rents[1]);
        $this->assertStringContainsString('9,000', $rents[2]);
    }

    public function test_units_with_equal_rent_sorted_by_unit_number_natural(): void
    {
        $property = $this->makeProperty('condominium');
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 8000, 'unit_number' => '110']);
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 8000, 'unit_number' => '9']);
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 8000, 'unit_number' => '101']);

        $result = $this->service->build($this->loadProperty($property));

        $labels = array_column($result['floorGroups'][0]['units'], 'label');
        // Natural sort: 9 < 101 < 110
        $this->assertStringContainsString('9', $labels[0]);
        $this->assertStringContainsString('101', $labels[1]);
        $this->assertStringContainsString('110', $labels[2]);
    }

    // ── unit view model ──────────────────────────────────────────────────────

    public function test_unit_label_uses_display_term_and_unit_number(): void
    {
        $property = $this->makeProperty('apartment');
        $unit = $this->addUnit($property, ['floor_number' => 1, 'unit_number' => '305']);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertEquals('Room 305', $unitVm['label']);
    }

    public function test_unit_rent_display_format(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 12500.00, 'unit_number' => '101']);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertEquals('₱12,500.00/month', $unitVm['rent_display']);
    }

    public function test_unit_details_url_includes_property_slug_and_unit_id(): void
    {
        $property = $this->makeProperty('condominium', ['slug' => 'test-condo']);
        $unit = $this->addUnit($property, ['floor_number' => 1, 'unit_number' => '201']);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertStringContainsString('test-condo', $unitVm['details_url']);
        $this->assertStringContainsString((string) $unit->id, $unitVm['details_url']);
    }

    public function test_unit_vm_includes_bedrooms_bathrooms_floor_area(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, [
            'floor_number' => 1,
            'unit_number' => '101',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'floor_area' => 45.5,
        ]);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertEquals(2, $unitVm['bedrooms']);
        $this->assertEquals(1, $unitVm['bathrooms']);
        $this->assertEquals(45.5, (float) $unitVm['floor_area']);
    }

    public function test_unit_status_is_ucfirst(): void
    {
        $property = $this->makeProperty();
        $this->addUnit($property, ['floor_number' => 1, 'unit_number' => '101', 'status' => 'available']);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertEquals('Available', $unitVm['status']);
    }

    // ── unit image resolution ────────────────────────────────────────────────

    public function test_unit_image_url_uses_unit_cover_image_first(): void
    {
        $property = $this->makeProperty(['cover_image' => 'https://cdn.example.com/property-cover.jpg']);
        $this->addUnit($property, [
            'floor_number' => 1,
            'unit_number' => '101',
            'cover_image' => 'https://cdn.example.com/unit-cover.jpg',
        ]);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertEquals('https://cdn.example.com/unit-cover.jpg', $unitVm['image_url']);
    }

    public function test_unit_image_url_falls_back_to_unit_gallery_when_unit_cover_absent(): void
    {
        $property = $this->makeProperty(['cover_image' => null, 'gallery' => null]);
        $this->addUnit($property, [
            'floor_number' => 1,
            'unit_number' => '101',
            'cover_image' => null,
            'gallery' => ['https://cdn.example.com/unit-gallery.jpg'],
        ]);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertEquals('https://cdn.example.com/unit-gallery.jpg', $unitVm['image_url']);
    }

    public function test_unit_image_url_falls_back_to_property_cover_when_unit_has_no_media(): void
    {
        $property = $this->makeProperty([
            'cover_image' => 'https://cdn.example.com/property-cover.jpg',
            'gallery' => null,
        ]);
        $this->addUnit($property, [
            'floor_number' => 1,
            'unit_number' => '101',
            'cover_image' => null,
            'gallery' => null,
        ]);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertEquals('https://cdn.example.com/property-cover.jpg', $unitVm['image_url']);
    }

    public function test_unit_image_url_falls_back_to_property_gallery_as_last_resort(): void
    {
        $property = $this->makeProperty([
            'cover_image' => null,
            'gallery' => ['https://cdn.example.com/property-gallery.jpg'],
        ]);
        $this->addUnit($property, [
            'floor_number' => 1,
            'unit_number' => '101',
            'cover_image' => null,
            'gallery' => null,
        ]);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertEquals('https://cdn.example.com/property-gallery.jpg', $unitVm['image_url']);
    }

    public function test_unit_image_url_is_null_when_no_media_anywhere(): void
    {
        $property = $this->makeProperty([
            'cover_image' => null,
            'gallery' => null,
        ]);
        $this->addUnit($property, [
            'floor_number' => 1,
            'unit_number' => '101',
            'cover_image' => null,
            'gallery' => null,
        ]);

        $result = $this->service->build($this->loadProperty($property));

        $unitVm = $result['floorGroups'][0]['units'][0];
        $this->assertNull($unitVm['image_url']);
    }

    // ── multiple floors with mixed floors ────────────────────────────────────

    public function test_multiple_floors_with_unspecified_mixed_in(): void
    {
        $property = $this->makeProperty('condominium');
        $this->addUnit($property, ['floor_number' => 3, 'rent_amount' => 9000, 'unit_number' => '301']);
        $this->addUnit($property, ['floor_number' => null, 'rent_amount' => 7000, 'unit_number' => 'G1']);
        $this->addUnit($property, ['floor_number' => 1, 'rent_amount' => 8000, 'unit_number' => '101']);

        $result = $this->service->build($this->loadProperty($property));

        $floorKeys = array_column($result['floorGroups'], 'floor_key');
        // Expect: 1, 3, unspecified
        $this->assertEquals('1', $floorKeys[0]);
        $this->assertEquals('3', $floorKeys[1]);
        $this->assertEquals('unspecified', $floorKeys[2]);
    }
}