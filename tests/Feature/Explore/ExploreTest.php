<?php

namespace Tests\Feature\Explore;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_explore_page()
    {
        $response = $this->get('/explore');

        $response->assertStatus(200);
        $response->assertViewIs('explore');
    }

    public function test_explore_page_returns_only_active_properties()
    {
        // Active property with an available unit
        $activeProperty = Property::factory()->create(['status' => 'active', 'is_active' => true]);
        $activeUnit = Unit::factory()->create(['property_id' => $activeProperty->id, 'status' => 'available']);

        // Inactive property with an available unit
        $inactiveProperty = Property::factory()->create(['status' => 'inactive', 'is_active' => false]);
        $inactiveUnit = Unit::factory()->create(['property_id' => $inactiveProperty->id, 'status' => 'available']);

        $response = $this->get('/explore');

        $response->assertStatus(200);
        
        // Assert active unit's property is present (assuming it renders something identifiable like property name)
        $response->assertSee($activeProperty->name);
        
        // Assert inactive unit's property is NOT present
        $response->assertDontSee($inactiveProperty->name);
    }

    public function test_guest_can_view_property_detail_page()
    {
        $property = Property::factory()->create([
            'status' => 'active', 
            'is_active' => true,
            'slug' => 'test-active-property'
        ]);

        $unit = Unit::factory()->create([
            'property_id' => $property->id,
            'status' => 'available'
        ]);

        $response = $this->get("/property/{$property->slug}");

        $response->assertStatus(200);
        $response->assertViewIs('unit-details');
        $response->assertSee($property->name);
    }

    public function test_visiting_non_existent_property_slug_returns_404()
    {
        $response = $this->get('/property/this-slug-does-not-exist-12345');

        $response->assertStatus(404);
    }
}
