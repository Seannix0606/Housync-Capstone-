<?php

namespace App\Services\Landlord;

use App\Models\Property;
use App\Services\Media\UnitMediaService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Maps unit_media[*] uploads from the property-create form onto freshly created units (by stable creation order).
 */
class PropertyCreationUnitMediaApplicator
{
    public function __construct(
        private UnitMediaService $unitMediaService
    ) {}

    public function applyFromRequest(Property $property, Request $request): void
    {
        $units = $property->units()->orderBy('id')->get();
        if ($units->isEmpty()) {
            return;
        }

        foreach ($units->values() as $index => $unit) {
            $cover = $request->file("unit_media.$index.cover");
            $galleryRaw = $request->file("unit_media.$index.gallery", []);
            $gallery = is_array($galleryRaw)
                ? array_values(array_filter($galleryRaw, fn ($f) => $f instanceof UploadedFile))
                : [];

            $payload = $this->unitMediaService->uploadForUnit(
                $unit->id,
                $cover instanceof UploadedFile ? $cover : null,
                $gallery
            );

            if ($payload !== []) {
                $unit->update($payload);
            }
        }
    }
}
