<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\UnitMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UnitMediaServiceTest extends TestCase
{
    private UnitMediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new UnitMediaService;
    }

    private function fakeImage(string $name = 'photo.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name);
    }

    // ── no-op cases ──────────────────────────────────────────────────────────

    public function test_returns_empty_payload_when_no_files_provided(): void
    {
        $payload = $this->service->uploadForUnit(1, null, []);

        $this->assertEmpty($payload);
    }

    public function test_returns_empty_payload_when_cover_null_and_gallery_empty(): void
    {
        $payload = $this->service->uploadForUnit(99, null, []);

        $this->assertArrayNotHasKey('cover_image', $payload);
        $this->assertArrayNotHasKey('gallery', $payload);
    }

    // ── cover image ───────────────────────────────────────────────────────────

    public function test_cover_image_key_present_when_cover_file_provided(): void
    {
        $payload = $this->service->uploadForUnit(5, $this->fakeImage('unit-cover.jpg'), []);

        $this->assertArrayHasKey('cover_image', $payload);
    }

    public function test_cover_image_stored_to_local_path_in_testing_env(): void
    {
        $file = $this->fakeImage('unit-cover.jpg');
        $payload = $this->service->uploadForUnit(7, $file, []);

        $this->assertNotEmpty($payload['cover_image']);
        Storage::disk('public')->assertExists($payload['cover_image']);
    }

    public function test_cover_image_path_contains_unit_id_and_cover_directory(): void
    {
        $unitId = 77;
        $payload = $this->service->uploadForUnit($unitId, $this->fakeImage('cover.jpg'), []);

        $this->assertStringContainsString("units/{$unitId}/cover", $payload['cover_image']);
    }

    public function test_no_cover_image_key_when_null_passed(): void
    {
        $payload = $this->service->uploadForUnit(1, null, [$this->fakeImage('gallery.jpg')]);

        $this->assertArrayNotHasKey('cover_image', $payload);
    }

    // ── gallery ───────────────────────────────────────────────────────────────

    public function test_gallery_key_present_when_gallery_files_provided(): void
    {
        $gallery = [$this->fakeImage('g1.jpg'), $this->fakeImage('g2.jpg')];
        $payload = $this->service->uploadForUnit(10, null, $gallery);

        $this->assertArrayHasKey('gallery', $payload);
        $this->assertCount(2, $payload['gallery']);
    }

    public function test_gallery_paths_stored_in_unit_specific_directory(): void
    {
        $unitId = 22;
        $gallery = [$this->fakeImage('g1.jpg')];
        $payload = $this->service->uploadForUnit($unitId, null, $gallery);

        $this->assertStringContainsString("units/{$unitId}/gallery", $payload['gallery'][0]);
    }

    public function test_gallery_is_capped_at_12_items(): void
    {
        $gallery = [];
        for ($i = 0; $i < 15; $i++) {
            $gallery[] = $this->fakeImage("g{$i}.jpg");
        }

        $payload = $this->service->uploadForUnit(33, null, $gallery);

        $this->assertCount(12, $payload['gallery']);
    }

    public function test_no_gallery_key_when_empty_array_passed(): void
    {
        $payload = $this->service->uploadForUnit(1, $this->fakeImage('cover.jpg'), []);

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

        $payload = $this->service->uploadForUnit(40, null, $gallery);

        $this->assertCount(2, $payload['gallery']);
    }

    public function test_all_non_uploaded_file_gallery_items_results_in_no_gallery_key(): void
    {
        $payload = $this->service->uploadForUnit(50, null, ['string', null, 999]);

        $this->assertArrayNotHasKey('gallery', $payload);
    }

    // ── both cover and gallery ────────────────────────────────────────────────

    public function test_returns_both_keys_when_both_provided(): void
    {
        $payload = $this->service->uploadForUnit(
            55,
            $this->fakeImage('unit-cover.jpg'),
            [$this->fakeImage('g1.jpg'), $this->fakeImage('g2.jpg')]
        );

        $this->assertArrayHasKey('cover_image', $payload);
        $this->assertArrayHasKey('gallery', $payload);
        $this->assertCount(2, $payload['gallery']);
    }

    // ── unit ID isolation ─────────────────────────────────────────────────────

    public function test_different_unit_ids_produce_separate_paths(): void
    {
        $payload1 = $this->service->uploadForUnit(1, $this->fakeImage('cover.jpg'), []);
        $payload2 = $this->service->uploadForUnit(2, $this->fakeImage('cover.jpg'), []);

        $this->assertStringContainsString('units/1/', $payload1['cover_image']);
        $this->assertStringContainsString('units/2/', $payload2['cover_image']);
        $this->assertNotEquals($payload1['cover_image'], $payload2['cover_image']);
    }

    // ── path does not bleed into property namespace ───────────────────────────

    public function test_unit_cover_path_does_not_contain_properties_prefix(): void
    {
        $payload = $this->service->uploadForUnit(10, $this->fakeImage('cover.jpg'), []);

        $this->assertStringNotContainsString('properties/', $payload['cover_image']);
    }

    public function test_gallery_with_exactly_12_items_not_truncated(): void
    {
        $gallery = array_fill(0, 12, $this->fakeImage('g.jpg'));

        $payload = $this->service->uploadForUnit(70, null, $gallery);

        $this->assertCount(12, $payload['gallery']);
    }
}
