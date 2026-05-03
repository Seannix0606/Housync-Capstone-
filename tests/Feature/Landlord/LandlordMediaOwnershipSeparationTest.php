<?php

namespace Tests\Feature\Landlord;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LandlordMediaOwnershipSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(RoleMiddleware::class);
    }

    private function createLandlord(): User
    {
        $user = User::factory()->create([
            'role' => 'landlord',
        ]);

        return $user;
    }

    private function fakeImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 120, 'image/jpeg');
    }

    private function createProperty(User $landlord): Property
    {
        return Property::factory()->create([
            'landlord_id' => $landlord->id,
            'property_type' => 'apartment',
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function test_property_create_stores_only_property_media_fields(): void
    {
        $landlord = $this->createLandlord();

        $payload = [
            'name' => 'Strict Property',
            'property_type' => 'apartment',
            'address' => 'Test Address',
            'floors' => 3,
            'property_cover_image' => $this->fakeImage('property-cover.jpg'),
            'property_gallery' => [
                $this->fakeImage('property-gallery-1.jpg'),
                $this->fakeImage('property-gallery-2.jpg'),
            ],
        ];

        $response = $this->actingAs($landlord)->post(route('landlord.store-apartment'), $payload);

        $response->assertRedirect(route('landlord.apartments'));
        $property = Property::where('name', 'Strict Property')->firstOrFail();
        $this->assertNotNull($property->cover_image);
        $this->assertNotNull($property->gallery);
    }

    public function test_property_edit_updates_only_property_media_fields(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $payload = [
            'name' => $property->name,
            'property_type' => $property->property_type,
            'address' => $property->address,
            'total_units' => 1,
            'status' => 'active',
            'property_cover_image' => $this->fakeImage('updated-property-cover.jpg'),
            'property_gallery' => [
                $this->fakeImage('updated-property-gallery.jpg'),
            ],
        ];

        $response = $this->actingAs($landlord)->put(route('landlord.update-apartment', $property->id), $payload);

        $response->assertRedirect(route('landlord.apartments'));
        $property->refresh();
        $this->assertNotNull($property->cover_image);
        $this->assertNotNull($property->gallery);
    }

    public function test_unit_create_stores_only_unit_media_fields(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $payload = [
            'unit_number' => 'A101',
            'unit_type' => 'studio',
            'rent_amount' => 12000,
            'status' => 'available',
            'leasing_type' => 'separate',
            'bedrooms' => 0,
            'bathrooms' => 1,
            'floor_number' => 1,
            'unit_cover_image' => $this->fakeImage('unit-cover.jpg'),
            'unit_gallery' => [
                $this->fakeImage('unit-gallery-1.jpg'),
            ],
        ];

        $response = $this->actingAs($landlord)->post(route('landlord.store-unit', $property->id), $payload);

        $response->assertRedirect(route('landlord.units', $property->id));
        $unit = Unit::where('property_id', $property->id)->where('unit_number', 'A101')->firstOrFail();
        $this->assertNotNull($unit->cover_image);
        $this->assertNotNull($unit->gallery);
    }

    public function test_unit_edit_updates_only_unit_media_fields(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        $unit = Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => 'A201',
            'status' => 'available',
        ]);

        $payload = [
            'unit_number' => $unit->unit_number,
            'unit_type' => $unit->unit_type,
            'rent_amount' => 14000,
            'status' => 'available',
            'leasing_type' => 'separate',
            'description' => 'Updated',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'unit_cover_image' => $this->fakeImage('unit-cover-updated.jpg'),
            'unit_gallery' => [
                $this->fakeImage('unit-gallery-updated.jpg'),
            ],
        ];

        $response = $this->actingAs($landlord)->put(route('landlord.update-unit', $unit->id), $payload);

        $response->assertRedirect(route('landlord.units'));
        $unit->refresh();
        $this->assertNotNull($unit->cover_image);
        $this->assertNotNull($unit->gallery);
    }

    public function test_property_form_rejects_unit_media_field_names(): void
    {
        $landlord = $this->createLandlord();

        $payload = [
            'name' => 'Wrong Media Property',
            'property_type' => 'apartment',
            'address' => 'Test Address',
            'floors' => 2,
            'unit_cover_image' => $this->fakeImage('wrong-unit-cover.jpg'),
        ];

        $response = $this->actingAs($landlord)->post(route('landlord.store-apartment'), $payload);
        $response->assertSessionHasErrors('unit_cover_image');
    }

    public function test_unit_form_rejects_property_media_field_names(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $payload = [
            'unit_number' => 'A301',
            'unit_type' => 'studio',
            'rent_amount' => 10000,
            'status' => 'available',
            'leasing_type' => 'separate',
            'bedrooms' => 0,
            'bathrooms' => 1,
            'property_cover_image' => $this->fakeImage('wrong-property-cover.jpg'),
        ];

        $response = $this->actingAs($landlord)->post(route('landlord.store-unit', $property->id), $payload);
        $response->assertSessionHasErrors('property_cover_image');
    }

    public function test_landlord_property_create_flow_still_returns_success(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)->post(route('landlord.store-apartment'), [
            'name' => 'Flow Success Property',
            'property_type' => 'apartment',
            'address' => 'Flow Address',
            'floors' => 3,
        ]);

        $response->assertRedirect(route('landlord.apartments'));
    }

    public function test_landlord_unit_create_flow_still_returns_success(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)->post(route('landlord.store-unit', $property->id), [
            'unit_number' => 'A401',
            'unit_type' => 'studio',
            'rent_amount' => 10000,
            'status' => 'available',
            'leasing_type' => 'separate',
            'bedrooms' => 0,
            'bathrooms' => 1,
        ]);

        $response->assertRedirect(route('landlord.units', $property->id));
    }

    public function test_media_paths_are_written_to_context_specific_directories(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $this->actingAs($landlord)->put(route('landlord.update-apartment', $property->id), [
            'name' => $property->name,
            'property_type' => $property->property_type,
            'address' => $property->address,
            'total_units' => 1,
            'status' => 'active',
            'property_cover_image' => $this->fakeImage('property-path.jpg'),
        ])->assertRedirect(route('landlord.apartments'));

        $property->refresh();
        $this->assertStringContainsString('properties/'.$property->id.'/cover/', (string) $property->cover_image);

        $this->actingAs($landlord)->post(route('landlord.store-unit', $property->id), [
            'unit_number' => 'A501',
            'unit_type' => 'studio',
            'rent_amount' => 10000,
            'status' => 'available',
            'leasing_type' => 'separate',
            'bedrooms' => 0,
            'bathrooms' => 1,
            'unit_cover_image' => $this->fakeImage('unit-path.jpg'),
        ])->assertRedirect(route('landlord.units', $property->id));

        $unit = Unit::where('property_id', $property->id)->where('unit_number', 'A501')->firstOrFail();
        $this->assertStringContainsString('units/'.$unit->id.'/cover/', (string) $unit->cover_image);
    }

    public function test_existing_property_and_unit_media_rendering_still_works_after_refactor(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        $property->update([
            'cover_image' => 'https://cdn.example.com/properties/cover.jpg',
            'gallery' => ['https://cdn.example.com/properties/gallery.jpg'],
        ]);

        $unit = Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => 'A601',
            'cover_image' => 'https://cdn.example.com/units/cover.jpg',
            'gallery' => ['https://cdn.example.com/units/gallery.jpg'],
        ]);

        $this->assertStringContainsString('properties/cover', $property->cover_image_url ?? $property->cover_image);
        $this->assertStringContainsString('units/cover', $unit->cover_image_url ?? $unit->cover_image);
    }
}
