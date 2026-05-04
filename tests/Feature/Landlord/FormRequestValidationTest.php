<?php

namespace Tests\Feature\Landlord;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Property;
use App\Support\UnitTypeBedroomMapping;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests for the changed form request validation rules in:
 *   - StorePropertyRequest
 *   - UpdatePropertyRequest
 *   - StoreUnitRequest
 *
 * This file focuses on validation rules NOT already covered by
 * LandlordMediaOwnershipSeparationTest (media field prohibition/acceptance).
 */
class FormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(RoleMiddleware::class);
        Storage::fake('public');
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function createLandlord(): User
    {
        return User::factory()->create(['role' => 'landlord']);
    }

    private function createProperty(User $landlord, array $overrides = []): Property
    {
        return Property::factory()->create(array_merge([
            'landlord_id' => $landlord->id,
            'status' => 'active',
            'is_active' => true,
        ], $overrides));
    }

    private function validStorePropertyPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Property',
            'property_type' => 'apartment',
            'address' => '123 Test Street',
            'floors' => 2,
        ], $overrides);
    }

    private function validUpdatePropertyPayload(Property $property, array $overrides = []): array
    {
        return array_merge([
            'name' => $property->name,
            'property_type' => $property->property_type,
            'address' => $property->address,
            'total_units' => 1,
            'status' => 'active',
        ], $overrides);
    }

    private function validStoreUnitPayload(array $overrides = []): array
    {
        return array_merge([
            'unit_number' => 'A101',
            'unit_type' => 'studio',
            'rent_amount' => 8000,
            'status' => 'available',
            'leasing_type' => 'separate',
            'bedrooms' => 0,
            'bathrooms' => 1,
        ], $overrides);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // StorePropertyRequest
    // ─────────────────────────────────────────────────────────────────────────

    public function test_store_property_passes_with_minimal_valid_payload(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload());

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('landlord.apartments'));
    }

    public function test_store_property_requires_name(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload(['name' => '']));

        $response->assertSessionHasErrors('name');
    }

    public function test_store_property_requires_property_type(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload(['property_type' => '']));

        $response->assertSessionHasErrors('property_type');
    }

    public function test_store_property_rejects_invalid_property_type(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload(['property_type' => 'mansion']));

        $response->assertSessionHasErrors('property_type');
    }

    public function test_store_property_accepts_all_valid_property_types(): void
    {
        $landlord = $this->createLandlord();
        $validTypes = ['apartment', 'condominium', 'townhouse', 'house', 'duplex', 'others'];

        foreach ($validTypes as $index => $type) {
            $payload = [
                'name' => "Property {$index}",
                'property_type' => $type,
            ];

            if (in_array($type, ['apartment', 'condominium'], true)) {
                $payload['floors'] = 2;
            } elseif ($type === 'duplex') {
                $payload['building_floors'] = 2;
                $payload['unit_bedrooms'] = [0 => 2, 1 => 2];
                $payload['unit_stories'] = [0 => 1, 1 => 1];
            } elseif ($type === 'house') {
                $payload['floors'] = null;
                $payload['bedrooms'] = 2;
                $payload['building_floors'] = 2;
            } elseif ($type === 'townhouse') {
                $payload['floors'] = null;
                $payload['building_floors'] = 2;
                $payload['bedrooms'] = 2;
            } else {
                $payload['floors'] = 1;
            }

            $response = $this->actingAs($landlord)
                ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload($payload));

            $response->assertSessionHasNoErrors();
            $errors = session('errors');
            $this->assertTrue(
                $errors === null || $errors->isEmpty(),
                "Failed for property type: {$type}"
            );
        }
    }

    public function test_store_property_requires_address(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload(['address' => '']));

        $response->assertSessionHasErrors('address');
    }

    public function test_store_property_rejects_old_cover_image_field_name(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), array_merge(
                $this->validStorePropertyPayload(),
                ['cover_image' => UploadedFile::fake()->image('cover.jpg')]
            ));

        $response->assertSessionHasErrors('cover_image');
    }

    public function test_store_property_rejects_old_gallery_field_name(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), array_merge(
                $this->validStorePropertyPayload(),
                ['gallery' => [UploadedFile::fake()->image('gallery.jpg')]]
            ));

        $response->assertSessionHasErrors('gallery');
    }

    public function test_store_property_rejects_unit_cover_image_field(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), array_merge(
                $this->validStorePropertyPayload(),
                ['unit_cover_image' => UploadedFile::fake()->image('unit.jpg')]
            ));

        $response->assertSessionHasErrors('unit_cover_image');
    }

    public function test_store_property_rejects_unit_gallery_field(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), array_merge(
                $this->validStorePropertyPayload(),
                ['unit_gallery' => [UploadedFile::fake()->image('ugallery.jpg')]]
            ));

        $response->assertSessionHasErrors('unit_gallery');
    }

    public function test_store_property_accepts_property_cover_image(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), array_merge(
                $this->validStorePropertyPayload(),
                ['property_cover_image' => UploadedFile::fake()->image('pcover.jpg')]
            ));

        $response->assertSessionHasNoErrors();
    }

    public function test_store_property_accepts_property_gallery(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), array_merge(
                $this->validStorePropertyPayload(),
                ['property_gallery' => [UploadedFile::fake()->image('g1.jpg')]]
            ));

        $response->assertSessionHasNoErrors();
    }

    public function test_store_property_rejects_property_gallery_exceeding_12(): void
    {
        $landlord = $this->createLandlord();
        $gallery = [];
        for ($i = 0; $i < 13; $i++) {
            $gallery[] = UploadedFile::fake()->image("g{$i}.jpg");
        }

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), array_merge(
                $this->validStorePropertyPayload(),
                ['property_gallery' => $gallery]
            ));

        $response->assertSessionHasErrors('property_gallery');
    }

    public function test_store_property_validates_floors_is_integer_min_1(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload(['floors' => 0]));

        $response->assertSessionHasErrors('floors');
    }

    public function test_store_property_house_rejects_multiple_units_via_floors(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'house',
                'floors' => 3,
                'bedrooms' => 2,
                'building_floors' => 2,
            ]));

        $response->assertSessionHasErrors('floors');
    }

    public function test_store_property_townhouse_rejects_multiple_units_via_floors(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'townhouse',
                'floors' => 2,
                'bedrooms' => 2,
                'building_floors' => 2,
            ]));

        $response->assertSessionHasErrors('floors');
    }

    public function test_store_property_duplex_requires_unit_count_minimum_two(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'duplex',
                'floors' => 1,
                'building_floors' => 2,
                'unit_bedrooms' => [0 => 2, 1 => 2],
                'unit_stories' => [0 => 1, 1 => 1],
            ]));

        $response->assertSessionHasErrors('floors');
    }

    public function test_store_property_duplex_allows_omitted_floors_and_creates_two_units(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'duplex',
                'floors' => null,
                'building_floors' => 2,
                'unit_bedrooms' => [0 => 2, 1 => 3],
                'unit_stories' => [0 => 1, 1 => 2],
            ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('landlord.apartments'));

        $property = \App\Models\Property::query()->where('landlord_id', $landlord->id)->latest('id')->first();
        $this->assertNotNull($property);
        $this->assertSame('duplex', $property->property_type);
        $this->assertSame(2, $property->units()->count());
        $units = $property->units()->orderBy('id')->get();
        $this->assertSame(2, (int) $units[0]->bedrooms);
        $this->assertSame(3, (int) $units[1]->bedrooms);
        $this->assertSame(3, (int) $property->bedrooms);
        $this->assertSame(1, (int) $units[0]->unit_stories);
        $this->assertSame(2, (int) $units[1]->unit_stories);
    }

    public function test_store_property_duplex_rejects_unit_count_other_than_two(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'duplex',
                'floors' => 3,
                'building_floors' => 2,
                'unit_bedrooms' => [0 => 2, 1 => 2],
                'unit_stories' => [0 => 1, 1 => 1],
            ]));

        $response->assertSessionHasErrors('floors');
    }

    public function test_store_property_duplex_rejects_top_level_bedrooms_field(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'duplex',
                'floors' => null,
                'building_floors' => 2,
                'bedrooms' => 2,
                'unit_bedrooms' => [0 => 2, 1 => 2],
                'unit_stories' => [0 => 1, 1 => 1],
            ]));

        $response->assertSessionHasErrors('bedrooms');
    }

    public function test_store_property_duplex_requires_interior_stories_per_unit(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'duplex',
                'floors' => null,
                'building_floors' => 2,
                'unit_bedrooms' => [0 => 2, 1 => 2],
                'unit_stories' => [0 => 1],
            ]));

        $response->assertSessionHasErrors('unit_stories.1');
    }

    public function test_store_property_duplex_allows_omitted_building_floors_when_per_unit_stories_given(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'duplex',
                'floors' => null,
                'building_floors' => null,
                'unit_bedrooms' => [0 => 2, 1 => 2],
                'unit_stories' => [0 => 1, 1 => 1],
            ]));

        $response->assertSessionHasNoErrors();

        $property = \App\Models\Property::query()->where('landlord_id', $landlord->id)->latest('id')->first();
        $this->assertNotNull($property);
        $this->assertNull($property->building_floors);
    }

    public function test_store_property_house_rejects_duplex_style_unit_stories_array(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'house',
                'floors' => null,
                'bedrooms' => 2,
                'building_floors' => 2,
                'unit_stories' => [0 => 2],
            ]));

        $response->assertSessionHasErrors('unit_stories');
    }

    public function test_store_property_house_optional_dwelling_stories_seeds_unit_row(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'house',
                'floors' => null,
                'bedrooms' => 2,
                'building_floors' => 2,
                'dwelling_stories' => 3,
            ]));

        $response->assertSessionHasNoErrors();

        $property = \App\Models\Property::query()->where('landlord_id', $landlord->id)->latest('id')->first();
        $unit = $property->units()->first();
        $this->assertNotNull($unit);
        $this->assertSame(3, (int) $unit->unit_stories);
    }

    public function test_store_property_duplex_rejects_dwelling_stories_field(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'duplex',
                'floors' => null,
                'building_floors' => 2,
                'unit_bedrooms' => [0 => 2, 1 => 2],
                'unit_stories' => [0 => 1, 1 => 1],
                'dwelling_stories' => 2,
            ]));

        $response->assertSessionHasErrors('dwelling_stories');
    }

    public function test_store_property_apartment_requires_at_least_two_units(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'apartment',
                'floors' => 1,
            ]));

        $response->assertSessionHasErrors('floors');
    }

    public function test_store_property_condominium_requires_at_least_two_units(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'property_type' => 'condominium',
                'floors' => 1,
            ]));

        $response->assertSessionHasErrors('floors');
    }

    public function test_store_property_rejects_invalid_contact_email(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-apartment'), $this->validStorePropertyPayload([
                'contact_email' => 'not-an-email',
            ]));

        $response->assertSessionHasErrors('contact_email');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UpdatePropertyRequest
    // ─────────────────────────────────────────────────────────────────────────

    public function test_update_property_passes_with_valid_payload(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), $this->validUpdatePropertyPayload($property));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('landlord.apartments'));
    }

    public function test_update_property_requires_status(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), $this->validUpdatePropertyPayload($property, [
                'status' => '',
            ]));

        $response->assertSessionHasErrors('status');
    }

    public function test_update_property_rejects_invalid_status(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), $this->validUpdatePropertyPayload($property, [
                'status' => 'sold',
            ]));

        $response->assertSessionHasErrors('status');
    }

    public function test_update_property_accepts_all_valid_statuses(): void
    {
        $landlord = $this->createLandlord();
        $validStatuses = ['active', 'inactive', 'maintenance'];

        foreach ($validStatuses as $status) {
            $property = $this->createProperty($landlord);

            $response = $this->actingAs($landlord)
                ->put(route('landlord.update-apartment', $property->id), $this->validUpdatePropertyPayload($property, [
                    'status' => $status,
                ]));

            $response->assertSessionHasNoErrors("Failed for status: {$status}");
        }
    }

    public function test_update_property_rejects_total_units_less_than_current_unit_count(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        Unit::factory()->count(3)->create(['property_id' => $property->id]);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), $this->validUpdatePropertyPayload($property, [
                'total_units' => 2,
            ]));

        $response->assertSessionHasErrors('total_units');
    }

    public function test_update_property_accepts_total_units_equal_to_current_unit_count(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        Unit::factory()->count(3)->create(['property_id' => $property->id]);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), $this->validUpdatePropertyPayload($property, [
                'total_units' => 3,
            ]));

        $response->assertSessionHasNoErrors();
    }

    public function test_update_property_rejects_old_cover_image_field_name(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), array_merge(
                $this->validUpdatePropertyPayload($property),
                ['cover_image' => UploadedFile::fake()->image('old-cover.jpg')]
            ));

        $response->assertSessionHasErrors('cover_image');
    }

    public function test_update_property_rejects_old_gallery_field_name(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), array_merge(
                $this->validUpdatePropertyPayload($property),
                ['gallery' => [UploadedFile::fake()->image('old-gallery.jpg')]]
            ));

        $response->assertSessionHasErrors('gallery');
    }

    public function test_update_property_rejects_unit_cover_image_field(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), array_merge(
                $this->validUpdatePropertyPayload($property),
                ['unit_cover_image' => UploadedFile::fake()->image('unit-cover.jpg')]
            ));

        $response->assertSessionHasErrors('unit_cover_image');
    }

    public function test_update_property_rejects_year_built_before_1900(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), $this->validUpdatePropertyPayload($property, [
                'year_built' => 1899,
            ]));

        $response->assertSessionHasErrors('year_built');
    }

    public function test_update_property_rejects_year_built_in_future(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), $this->validUpdatePropertyPayload($property, [
                'year_built' => (int) date('Y') + 1,
            ]));

        $response->assertSessionHasErrors('year_built');
    }

    public function test_update_property_accepts_property_gallery(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->put(route('landlord.update-apartment', $property->id), array_merge(
                $this->validUpdatePropertyPayload($property),
                ['property_gallery' => [UploadedFile::fake()->image('pg.jpg')]]
            ));

        $response->assertSessionHasNoErrors();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // StoreUnitRequest
    // ─────────────────────────────────────────────────────────────────────────

    public function test_store_unit_passes_with_valid_payload(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload());

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('landlord.units', $property->id));
    }

    public function test_store_unit_requires_unit_number(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload(['unit_number' => '']));

        $response->assertSessionHasErrors('unit_number');
    }

    public function test_store_unit_requires_unique_unit_number_per_property(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'A101']);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload(['unit_number' => 'A101']));

        $response->assertSessionHasErrors('unit_number');
    }

    public function test_store_unit_allows_same_unit_number_in_different_property(): void
    {
        $landlord = $this->createLandlord();
        $property1 = $this->createProperty($landlord);
        $property2 = $this->createProperty($landlord);
        Unit::factory()->create(['property_id' => $property1->id, 'unit_number' => 'A101']);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property2->id), $this->validStoreUnitPayload(['unit_number' => 'A101']));

        $response->assertSessionHasNoErrors();
    }

    public function test_store_unit_requires_rent_amount(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload(['rent_amount' => '']));

        $response->assertSessionHasErrors('rent_amount');
    }

    public function test_store_unit_rejects_negative_rent_amount(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload(['rent_amount' => -100]));

        $response->assertSessionHasErrors('rent_amount');
    }

    public function test_store_unit_requires_valid_status(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload(['status' => 'rented']));

        $response->assertSessionHasErrors('status');
    }

    public function test_store_unit_accepts_available_and_maintenance_statuses(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        foreach (['available', 'maintenance'] as $index => $status) {
            $response = $this->actingAs($landlord)
                ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload([
                    'unit_number' => "A{$index}01",
                    'status' => $status,
                ]));

            $response->assertSessionHasNoErrors("Failed for status: {$status}");
        }
    }

    public function test_store_unit_rejects_property_cover_image_field(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), array_merge(
                $this->validStoreUnitPayload(),
                ['property_cover_image' => UploadedFile::fake()->image('pc.jpg')]
            ));

        $response->assertSessionHasErrors('property_cover_image');
    }

    public function test_store_unit_rejects_property_gallery_field(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), array_merge(
                $this->validStoreUnitPayload(),
                ['property_gallery' => [UploadedFile::fake()->image('pg.jpg')]]
            ));

        $response->assertSessionHasErrors('property_gallery');
    }

    public function test_store_unit_rejects_old_cover_image_field_name(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), array_merge(
                $this->validStoreUnitPayload(),
                ['cover_image' => UploadedFile::fake()->image('old.jpg')]
            ));

        $response->assertSessionHasErrors('cover_image');
    }

    public function test_store_unit_rejects_old_gallery_field_name(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), array_merge(
                $this->validStoreUnitPayload(),
                ['gallery' => [UploadedFile::fake()->image('old.jpg')]]
            ));

        $response->assertSessionHasErrors('gallery');
    }

    public function test_store_unit_accepts_unit_cover_image(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), array_merge(
                $this->validStoreUnitPayload(),
                ['unit_cover_image' => UploadedFile::fake()->image('unit.jpg')]
            ));

        $response->assertSessionHasNoErrors();
    }

    public function test_store_unit_accepts_unit_gallery(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), array_merge(
                $this->validStoreUnitPayload(),
                ['unit_gallery' => [UploadedFile::fake()->image('g.jpg')]]
            ));

        $response->assertSessionHasNoErrors();
    }

    public function test_store_unit_rejects_unit_gallery_exceeding_12(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        $gallery = [];
        for ($i = 0; $i < 13; $i++) {
            $gallery[] = UploadedFile::fake()->image("g{$i}.jpg");
        }

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), array_merge(
                $this->validStoreUnitPayload(),
                ['unit_gallery' => $gallery]
            ));

        $response->assertSessionHasErrors('unit_gallery');
    }

    public function test_store_unit_requires_bedrooms_min_zero(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload(['bedrooms' => -1]));

        $response->assertSessionHasErrors('bedrooms');
    }

    public function test_store_unit_rejects_unknown_unit_type_slug(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload([
                'unit_type' => 'Duplex',
            ]));

        $response->assertSessionHasErrors('unit_type');
    }

    public function test_store_unit_accepts_all_allowed_unit_type_slugs(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        $defaultBedrooms = UnitTypeBedroomMapping::defaultBedroomsByType();

        foreach (UnitTypeBedroomMapping::allowedUnitTypeKeys() as $index => $slug) {
            $response = $this->actingAs($landlord)
                ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload([
                    'unit_type' => $slug,
                    'unit_number' => "U{$index}-{$slug}",
                    'bedrooms' => $defaultBedrooms[$slug],
                ]));

            $response->assertSessionHasNoErrors("Failed for unit_type slug: {$slug}");
        }
    }

    public function test_store_unit_requires_bathrooms_min_one(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload(['bathrooms' => 0]));

        $response->assertSessionHasErrors('bathrooms');
    }

    public function test_store_unit_rejects_invalid_leasing_type(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)
            ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload(['leasing_type' => 'flat-rate']));

        $response->assertSessionHasErrors('leasing_type');
    }

    public function test_store_unit_accepts_both_leasing_types(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        foreach (['separate', 'inclusive'] as $index => $leasingType) {
            $response = $this->actingAs($landlord)
                ->post(route('landlord.store-unit', $property->id), $this->validStoreUnitPayload([
                    'unit_number' => "L{$index}01",
                    'leasing_type' => $leasingType,
                ]));

            $response->assertSessionHasNoErrors("Failed for leasing type: {$leasingType}");
        }
    }
}
