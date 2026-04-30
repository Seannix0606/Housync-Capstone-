<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\PropertyMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyMediaServiceTest extends TestCase
{
    private PropertyMediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new PropertyMediaService;
    }

    private function fakeImage(string $name = 'photo.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name);
    }

    // ── no-op cases ──────────────────────────────────────────────────────────

    public function test_returns_empty_payload_when_no_files_provided(): void
    {
        $payload = $this->service->uploadForProperty(1, null, []);

        $this->assertEmpty($payload);
    }

    public function test_returns_empty_payload_when_cover_is_null_and_gallery_is_empty(): void
    {
        $payload = $this->service->uploadForProperty(42, null, []);

        $this->assertArrayNotHasKey('cover_image', $payload);
        $this->assertArrayNotHasKey('gallery', $payload);
    }

    // ── cover image ───────────────────────────────────────────────────────────

    public function test_cover_image_key_present_when_cover_file_provided(): void
    {
        $payload = $this->service->uploadForProperty(5, $this->fakeImage('cover.jpg'), []);

        $this->assertArrayHasKey('cover_image', $payload);
    }

    public function test_cover_image_is_stored_to_local_path_in_testing_env(): void
    {
        $file = $this->fakeImage('cover.jpg');
        $payload = $this->service->uploadForProperty(7, $file, []);

        $this->assertNotEmpty($payload['cover_image']);
        // In testing env, uploadSingle returns the local path from Storage::fake
        Storage::disk('public')->assertExists($payload['cover_image']);
    }

    public function test_cover_image_path_contains_property_id_and_cover_directory(): void
    {
        $propertyId = 99;
        $payload = $this->service->uploadForProperty($propertyId, $this->fakeImage('cover.jpg'), []);

        $this->assertStringContainsString("properties/{$propertyId}/cover", $payload['cover_image']);
    }

    public function test_no_cover_image_key_when_null_passed(): void
    {
        $payload = $this->service->uploadForProperty(1, null, [$this->fakeImage('gallery.jpg')]);

        $this->assertArrayNotHasKey('cover_image', $payload);
    }

    // ── gallery ───────────────────────────────────────────────────────────────

    public function test_gallery_key_present_when_gallery_files_provided(): void
    {
        $gallery = [$this->fakeImage('g1.jpg'), $this->fakeImage('g2.jpg')];
        $payload = $this->service->uploadForProperty(10, null, $gallery);

        $this->assertArrayHasKey('gallery', $payload);
        $this->assertCount(2, $payload['gallery']);
    }

    public function test_gallery_paths_are_stored_in_correct_directory(): void
    {
        $propertyId = 15;
        $gallery = [$this->fakeImage('g1.jpg')];
        $payload = $this->service->uploadForProperty($propertyId, null, $gallery);

        $this->assertStringContainsString("properties/{$propertyId}/gallery", $payload['gallery'][0]);
    }

    public function test_gallery_is_capped_at_12_items(): void
    {
        $gallery = [];
        for ($i = 0; $i < 15; $i++) {
            $gallery[] = $this->fakeImage("g{$i}.jpg");
        }

        $payload = $this->service->uploadForProperty(20, null, $gallery);

        $this->assertCount(12, $payload['gallery']);
    }

    public function test_no_gallery_key_when_empty_array_passed(): void
    {
        $payload = $this->service->uploadForProperty(1, $this->fakeImage('cover.jpg'), []);

        $this->assertArrayNotHasKey('gallery', $payload);
    }

    public function test_non_uploaded_file_items_in_gallery_are_skipped(): void
    {
        $gallery = [
            $this->fakeImage('valid.jpg'),
            'not-a-file',
            null,
            $this->fakeImage('another-valid.jpg'),
        ];

        $payload = $this->service->uploadForProperty(30, null, $gallery);

        // Only the two UploadedFile items should be stored
        $this->assertCount(2, $payload['gallery']);
    }

    public function test_all_non_uploaded_file_gallery_items_results_in_no_gallery_key(): void
    {
        $payload = $this->service->uploadForProperty(30, null, ['not-a-file', null, 42]);

        $this->assertArrayNotHasKey('gallery', $payload);
    }

    // ── both cover and gallery ────────────────────────────────────────────────

    public function test_returns_both_cover_and_gallery_when_both_provided(): void
    {
        $payload = $this->service->uploadForProperty(
            50,
            $this->fakeImage('cover.jpg'),
            [$this->fakeImage('g1.jpg'), $this->fakeImage('g2.jpg')]
        );

        $this->assertArrayHasKey('cover_image', $payload);
        $this->assertArrayHasKey('gallery', $payload);
        $this->assertCount(2, $payload['gallery']);
    }

    // ── property ID isolation ─────────────────────────────────────────────────

    public function test_different_property_ids_produce_separate_paths(): void
    {
        $payload1 = $this->service->uploadForProperty(1, $this->fakeImage('cover.jpg'), []);
        $payload2 = $this->service->uploadForProperty(2, $this->fakeImage('cover.jpg'), []);

        $this->assertStringContainsString('properties/1/', $payload1['cover_image']);
        $this->assertStringContainsString('properties/2/', $payload2['cover_image']);
        $this->assertNotEquals($payload1['cover_image'], $payload2['cover_image']);
    }

    // ── gallery exactly at cap ────────────────────────────────────────────────

    public function test_gallery_with_exactly_12_items_is_not_truncated(): void
    {
        $gallery = array_fill(0, 12, $this->fakeImage('g.jpg'));

        $payload = $this->service->uploadForProperty(60, null, $gallery);

        $this->assertCount(12, $payload['gallery']);
    }
}