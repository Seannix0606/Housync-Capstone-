<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\UnitMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UnitMediaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function fakeJpeg(string $name = 'cover.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name, 100, 100);
    }

    // ── no files ──────────────────────────────────────────────────────────────

    public function test_upload_for_unit_returns_empty_array_when_no_files_given(): void
    {
        $service = new UnitMediaService;

        $payload = $service->uploadForUnit(1, null, []);

        $this->assertSame([], $payload);
    }

    public function test_upload_for_unit_returns_empty_array_when_cover_null_and_gallery_empty(): void
    {
        $service = new UnitMediaService;

        $payload = $service->uploadForUnit(99, null);

        $this->assertSame([], $payload);
    }

    // ── cover image ──────────────────────────────────────────────────────────

    public function test_upload_for_unit_returns_cover_image_key_when_cover_provided(): void
    {
        $service = new UnitMediaService;
        $cover = $this->fakeJpeg('unit-cover.jpg');

        $payload = $service->uploadForUnit(5, $cover);

        $this->assertArrayHasKey('cover_image', $payload);
        $this->assertNotEmpty($payload['cover_image']);
    }

    public function test_upload_for_unit_stores_cover_in_unit_specific_cover_directory(): void
    {
        $service = new UnitMediaService;
        $cover = $this->fakeJpeg('cover.jpg');

        $payload = $service->uploadForUnit(42, $cover);

        $this->assertStringContainsString('units/42/cover', $payload['cover_image']);
    }

    public function test_upload_for_unit_cover_image_file_exists_in_storage(): void
    {
        $service = new UnitMediaService;
        $cover = $this->fakeJpeg('unit-cover.jpg');

        $payload = $service->uploadForUnit(7, $cover);

        Storage::disk('public')->assertExists($payload['cover_image']);
    }

    // ── gallery ───────────────────────────────────────────────────────────────

    public function test_upload_for_unit_returns_gallery_key_when_gallery_images_provided(): void
    {
        $service = new UnitMediaService;
        $gallery = [$this->fakeJpeg('g1.jpg'), $this->fakeJpeg('g2.jpg')];

        $payload = $service->uploadForUnit(3, null, $gallery);

        $this->assertArrayHasKey('gallery', $payload);
        $this->assertCount(2, $payload['gallery']);
    }

    public function test_upload_for_unit_stores_gallery_in_unit_specific_gallery_directory(): void
    {
        $service = new UnitMediaService;
        $gallery = [$this->fakeJpeg('g1.jpg')];

        $payload = $service->uploadForUnit(15, null, $gallery);

        $this->assertStringContainsString('units/15/gallery', $payload['gallery'][0]);
    }

    public function test_upload_for_unit_caps_gallery_at_12_images(): void
    {
        $service = new UnitMediaService;
        $gallery = [];
        for ($i = 0; $i < 15; $i++) {
            $gallery[] = $this->fakeJpeg("g{$i}.jpg");
        }

        $payload = $service->uploadForUnit(20, null, $gallery);

        $this->assertCount(12, $payload['gallery']);
    }

    public function test_upload_for_unit_skips_non_uploaded_file_gallery_entries(): void
    {
        $service = new UnitMediaService;
        $gallery = [
            $this->fakeJpeg('valid.jpg'),
            'not-a-file',
            null,
            $this->fakeJpeg('also-valid.jpg'),
        ];

        $payload = $service->uploadForUnit(25, null, $gallery);

        $this->assertCount(2, $payload['gallery']);
    }

    public function test_upload_for_unit_does_not_add_gallery_key_when_all_entries_invalid(): void
    {
        $service = new UnitMediaService;
        $gallery = ['not-a-file', null, 123];

        $payload = $service->uploadForUnit(30, null, $gallery);

        $this->assertArrayNotHasKey('gallery', $payload);
    }

    // ── both cover and gallery ────────────────────────────────────────────────

    public function test_upload_for_unit_handles_both_cover_and_gallery_simultaneously(): void
    {
        $service = new UnitMediaService;
        $cover = $this->fakeJpeg('cover.jpg');
        $gallery = [$this->fakeJpeg('g1.jpg'), $this->fakeJpeg('g2.jpg')];

        $payload = $service->uploadForUnit(50, $cover, $gallery);

        $this->assertArrayHasKey('cover_image', $payload);
        $this->assertArrayHasKey('gallery', $payload);
        $this->assertCount(2, $payload['gallery']);
    }

    // ── different unit IDs ────────────────────────────────────────────────────

    public function test_upload_for_unit_uses_unit_id_in_storage_path(): void
    {
        $service = new UnitMediaService;

        $payloadA = $service->uploadForUnit(100, $this->fakeJpeg('a.jpg'));
        $payloadB = $service->uploadForUnit(200, $this->fakeJpeg('b.jpg'));

        $this->assertStringContainsString('units/100/', $payloadA['cover_image']);
        $this->assertStringContainsString('units/200/', $payloadB['cover_image']);
    }

    // ── namespace separation from property paths ───────────────────────────────

    public function test_upload_for_unit_path_never_contains_properties_prefix(): void
    {
        $service = new UnitMediaService;
        $cover = $this->fakeJpeg('cover.jpg');

        $payload = $service->uploadForUnit(10, $cover);

        $this->assertStringNotContainsString('properties/', $payload['cover_image']);
    }

    // ── regression: empty gallery array does not produce gallery key ──────────

    public function test_upload_for_unit_does_not_return_gallery_key_for_empty_array(): void
    {
        $service = new UnitMediaService;

        $payload = $service->uploadForUnit(1, null, []);

        $this->assertArrayNotHasKey('gallery', $payload);
    }
}
