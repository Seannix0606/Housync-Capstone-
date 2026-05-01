<?php

namespace App\Services\Media;

use App\Services\SupabaseService;
use Illuminate\Http\UploadedFile;

class PropertyMediaService
{
    /**
     * Upload property media and return normalized DB payload.
     */
    public function uploadForProperty(int $propertyId, ?UploadedFile $coverImage, array $galleryImages = []): array
    {
        $payload = [];

        if ($coverImage instanceof UploadedFile) {
            $payload['cover_image'] = $this->uploadSingle(
                file: $coverImage,
                supabasePath: "properties/{$propertyId}/cover/".$this->buildFilename('property-cover', $coverImage),
                localPath: "properties/{$propertyId}/cover"
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
                    supabasePath: "properties/{$propertyId}/gallery/".time()."-{$index}-".$this->buildFilename('property-gallery', $galleryImage),
                    localPath: "properties/{$propertyId}/gallery"
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
