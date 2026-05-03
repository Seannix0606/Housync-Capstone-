<?php

namespace Tests\Feature\Landlord;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyCreateUnitMediaFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(RoleMiddleware::class);
    }

    public function test_store_property_rejects_unit_media_slot_beyond_created_unit_count(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);

        $response = $this->actingAs($landlord)
            ->from(route('landlord.create-apartment'))
            ->post(route('landlord.store-apartment'), [
                'name' => 'Test Prop',
                'property_type' => 'duplex',
                'address' => '123 St',
                'building_floors' => '2',
                'unit_bedrooms' => [0 => 2, 1 => 2],
                'unit_stories' => [0 => 1, 1 => 1],
                // Non-empty nested payload so the test client keeps index `2` (empty arrays may be dropped).
                'unit_media' => [
                    2 => ['_slot' => '1'],
                ],
            ]);

        $response->assertRedirect(route('landlord.create-apartment'));
        $response->assertSessionHasErrors('unit_media.2');
        $this->assertDatabaseCount('properties', 0);
    }
}
