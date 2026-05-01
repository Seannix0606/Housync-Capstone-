<?php

namespace App\Services\Media;

use App\Services\SupabaseService;
use Illuminate\Http\UploadedFile;

class UnitMediaService
{
    /**
     * Upload unit media and return normalized DB payload.
     */
    public function uploadForUnit(int $unitId, ?UploadedFile $coverImage, array $galleryImages = []): array
    {
        $payload = [];

        if ($coverImage instanceof UploadedFile) {
            $payload['cover_image'] = $this->uploadSingle(
                file: $coverImage,
                supabasePath: "units/{$unitId}/cover/".$this->buildFilename('unit-cover', $coverImage),
                localPath: "units/{$unitId}/cover"
            );
        }

        if (! empty($galleryImages)) {
            $galleryPaths = [];
            foreach ($galleryImages as $index => $galleryImage) {
                if (! $galleryImage instanceof UploadedFile) {
                    continue;
                }

                $galleryPaths[] = $this->uploadSingle(
                    file: $galleryImage,
                    supabasePath: "units/{$unitId}/gallery/".time()."-{$index}-".$this->buildFilename('unit-gallery', $galleryImage),
                    localPath: "units/{$unitId}/gallery"
                );
            }

            if (! empty($galleryPaths)) {
                $payload['gallery'] = array_slice($galleryPaths, 0, 12);
            }
        }

        return $payload;
    }

    private function uploadSingle(UploadedFile $file, string $supabasePath, string $localPath): string
    {
        if (! app()->environment('testing')) {
            $supabase = new SupabaseService;
            $result = $supabase->uploadFile(
                config('services.supabase.bucket'),
                $supabasePath,
                $file->getRealPath()
            );

            if ($result['success'] ?? false) {
                return $result['url'];
            }
        }

        return $file->store($localPath, 'public');
    }

    private function buildFilename(string $prefix, UploadedFile $file): string
    {
        return $prefix.'-'.time().'-'.uniqid().'.'.$file->getClientOriginalExtension();
    }
}
