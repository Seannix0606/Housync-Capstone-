<?php

namespace Tests\Unit\Services\Explore;

use App\Models\Property;
use App\Services\Explore\PropertyHeroPresentationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyHeroPresentationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PropertyHeroPresentationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PropertyHeroPresentationService;
    }

    private function makeProperty(array $attributes = []): Property
    {
        $landlord = \App\Models\User::factory()->create(['role' => 'landlord']);

        return Property::factory()->create(array_merge([
            'landlord_id' => $landlord->id,
        ], $attributes));
    }

    // ── build() return shape ────────────────────────────────────────────────

    public function test_build_returns_expected_keys(): void
    {
        $property = $this->makeProperty();

        $result = $this->service->build($property);

        $this->assertArrayHasKey('hero_image_url', $result);
        $this->assertArrayHasKey('has_hero_image', $result);
        $this->assertArrayHasKey('hero_alt_text', $result);
    }

    // ── hero_image_url resolution ───────────────────────────────────────────

    public function test_uses_cover_image_url_when_present(): void
    {
        $property = $this->makeProperty([
            'cover_image' => 'https://cdn.example.com/property-cover.jpg',
            'gallery' => null,
        ]);

        $result = $this->service->build($property);

        $this->assertEquals('https://cdn.example.com/property-cover.jpg', $result['hero_image_url']);
        $this->assertTrue($result['has_hero_image']);
    }

    public function test_prefers_cover_image_over_gallery_when_both_present(): void
    {
        $property = $this->makeProperty([
            'cover_image' => 'https://cdn.example.com/cover.jpg',
            'gallery' => ['https://cdn.example.com/gallery-1.jpg'],
        ]);

        $result = $this->service->build($property);

        $this->assertEquals('https://cdn.example.com/cover.jpg', $result['hero_image_url']);
    }

    public function test_falls_back_to_first_gallery_url_when_cover_is_null(): void
    {
        $property = $this->makeProperty([
            'cover_image' => null,
            'gallery' => [
                'https://cdn.example.com/gallery-1.jpg',
                'https://cdn.example.com/gallery-2.jpg',
            ],
        ]);

        $result = $this->service->build($property);

        $this->assertEquals('https://cdn.example.com/gallery-1.jpg', $result['hero_image_url']);
        $this->assertTrue($result['has_hero_image']);
    }

    public function test_falls_back_to_first_gallery_url_when_cover_is_empty_string(): void
    {
        $property = $this->makeProperty([
            'cover_image' => '',
            'gallery' => ['https://cdn.example.com/gallery-only.jpg'],
        ]);

        $result = $this->service->build($property);

        $this->assertEquals('https://cdn.example.com/gallery-only.jpg', $result['hero_image_url']);
    }

    public function test_returns_null_hero_url_when_no_media_present(): void
    {
        $property = $this->makeProperty([
            'cover_image' => null,
            'gallery' => null,
        ]);

        $result = $this->service->build($property);

        $this->assertNull($result['hero_image_url']);
        $this->assertFalse($result['has_hero_image']);
    }

    public function test_returns_null_hero_url_when_gallery_is_empty_array(): void
    {
        $property = $this->makeProperty([
            'cover_image' => null,
            'gallery' => [],
        ]);

        $result = $this->service->build($property);

        $this->assertNull($result['hero_image_url']);
        $this->assertFalse($result['has_hero_image']);
    }

    // ── hero_alt_text ───────────────────────────────────────────────────────

    public function test_alt_text_uses_property_name(): void
    {
        $property = $this->makeProperty(['name' => 'Sunrise Tower']);

        $result = $this->service->build($property);

        $this->assertEquals('Sunrise Tower hero image', $result['hero_alt_text']);
    }

    public function test_alt_text_defaults_to_property_when_name_is_null(): void
    {
        $property = $this->makeProperty();
        // Force name to null to simulate edge case
        $property->name = null;

        $result = $this->service->build($property);

        $this->assertEquals('Property hero image', $result['hero_alt_text']);
    }

    // ── has_hero_image flag ─────────────────────────────────────────────────

    public function test_has_hero_image_is_true_when_cover_image_set(): void
    {
        $property = $this->makeProperty([
            'cover_image' => 'https://cdn.example.com/cover.jpg',
        ]);

        $result = $this->service->build($property);

        $this->assertTrue($result['has_hero_image']);
    }

    public function test_has_hero_image_is_false_when_no_media(): void
    {
        $property = $this->makeProperty([
            'cover_image' => null,
            'gallery' => null,
        ]);

        $result = $this->service->build($property);

        $this->assertFalse($result['has_hero_image']);
    }

    // ── local-path cover images ─────────────────────────────────────────────

    public function test_local_path_cover_image_is_resolved_to_url(): void
    {
        $property = $this->makeProperty([
            'cover_image' => 'properties/1/cover/property-cover-12345.jpg',
            'gallery' => null,
        ]);

        $result = $this->service->build($property);

        // cover_image_url accessor converts local path to an absolute URL
        $this->assertNotNull($result['hero_image_url']);
        $this->assertStringContainsString('property-cover-12345.jpg', $result['hero_image_url']);
        $this->assertTrue($result['has_hero_image']);
    }
}