<?php

namespace Tests\Unit\Services\Explore;

use App\Models\Property;
use App\Services\Explore\PropertyHeroPresentationService;
use Tests\TestCase;

class PropertyHeroPresentationServiceTest extends TestCase
{
    private PropertyHeroPresentationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PropertyHeroPresentationService;
    }

    private function makeProperty(array $attributes = []): Property
    {
        $property = new Property;
        foreach ($attributes as $key => $value) {
            $property->$key = $value;
        }

        return $property;
    }

    // ── build() return shape ─────────────────────────────────────────────────

    public function test_build_returns_required_keys(): void
    {
        $property = $this->makeProperty(['name' => 'Test Property', 'cover_image' => null]);

        $result = $this->service->build($property);

        $this->assertArrayHasKey('hero_image_url', $result);
        $this->assertArrayHasKey('has_hero_image', $result);
        $this->assertArrayHasKey('hero_alt_text', $result);
    }

    // ── hero_image_url resolution ─────────────────────────────────────────────

    public function test_build_uses_cover_image_url_when_present(): void
    {
        // cover_image starting with 'http' is returned as-is by getCoverImageUrlAttribute
        $property = $this->makeProperty([
            'name' => 'Hero Property',
            'cover_image' => 'https://cdn.example.com/property-cover.jpg',
            'gallery' => ['https://cdn.example.com/gallery-0.jpg'],
        ]);

        $result = $this->service->build($property);

        $this->assertSame('https://cdn.example.com/property-cover.jpg', $result['hero_image_url']);
        $this->assertTrue($result['has_hero_image']);
    }

    public function test_build_falls_back_to_first_gallery_url_when_cover_is_null(): void
    {
        $property = $this->makeProperty([
            'name' => 'Gallery Property',
            'cover_image' => null,
            'gallery' => ['https://cdn.example.com/gallery-first.jpg', 'https://cdn.example.com/gallery-second.jpg'],
        ]);

        $result = $this->service->build($property);

        $this->assertSame('https://cdn.example.com/gallery-first.jpg', $result['hero_image_url']);
        $this->assertTrue($result['has_hero_image']);
    }

    public function test_build_returns_null_hero_url_when_property_has_no_media(): void
    {
        $property = $this->makeProperty([
            'name' => 'No Media Property',
            'cover_image' => null,
            'gallery' => null,
        ]);

        $result = $this->service->build($property);

        $this->assertNull($result['hero_image_url']);
        $this->assertFalse($result['has_hero_image']);
    }

    public function test_build_returns_null_hero_url_when_gallery_is_empty_array(): void
    {
        $property = $this->makeProperty([
            'name' => 'Empty Gallery Property',
            'cover_image' => null,
            'gallery' => [],
        ]);

        $result = $this->service->build($property);

        $this->assertNull($result['hero_image_url']);
        $this->assertFalse($result['has_hero_image']);
    }

    public function test_build_prefers_cover_image_over_gallery_even_when_both_present(): void
    {
        $property = $this->makeProperty([
            'name' => 'Both Media Property',
            'cover_image' => 'https://cdn.example.com/cover.jpg',
            'gallery' => ['https://cdn.example.com/gallery-ignored.jpg'],
        ]);

        $result = $this->service->build($property);

        $this->assertSame('https://cdn.example.com/cover.jpg', $result['hero_image_url']);
    }

    // ── alt text ──────────────────────────────────────────────────────────────

    public function test_build_uses_property_name_in_alt_text(): void
    {
        $property = $this->makeProperty(['name' => 'Sunset Villas', 'cover_image' => null]);

        $result = $this->service->build($property);

        $this->assertSame('Sunset Villas hero image', $result['hero_alt_text']);
    }

    public function test_build_falls_back_to_property_word_in_alt_text_when_name_is_null(): void
    {
        $property = $this->makeProperty(['name' => null, 'cover_image' => null]);

        $result = $this->service->build($property);

        $this->assertSame('Property hero image', $result['hero_alt_text']);
    }

    // ── edge cases ───────────────────────────────────────────────────────────

    public function test_build_ignores_empty_string_cover_image(): void
    {
        $property = $this->makeProperty([
            'name' => 'Edge Case',
            'cover_image' => '',
            'gallery' => ['https://cdn.example.com/gallery-fallback.jpg'],
        ]);

        $result = $this->service->build($property);

        // Empty string cover_image resolves to null via getCoverImageUrlAttribute
        $this->assertSame('https://cdn.example.com/gallery-fallback.jpg', $result['hero_image_url']);
    }
}
