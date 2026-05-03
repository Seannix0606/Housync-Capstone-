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
                );
            }

            if (! empty($galleryPaths)) {
                $payload['gallery'] = array_slice($galleryPaths, 0, 12);
            }
        }

        return $payload;
    }

    private function uploadSingle(UploadedFile $file, string $supabasePath): string
    {
        if (app()->environment('testing')) {
            return $supabasePath;
        }

        $supabase = new SupabaseService;
        $result = $supabase->uploadFile(
            config('services.supabase.bucket'),
            $supabasePath,
            $file->getRealPath()
        );

        if ($result['success'] ?? false) {
            return $result['url'];
        }

        \Illuminate\Support\Facades\Log::error('Supabase property media upload failed', [
            'path'        => $supabasePath,
            'message'     => $result['message'] ?? 'unknown error',
            'error'       => $result['error'] ?? null,
            'status_code' => $result['status_code'] ?? null,
            'response'    => $result['response'] ?? null,
        ]);

        throw new \RuntimeException(
            'Failed to upload property image to Supabase storage. '.
            ($result['message'] ?? 'Unknown error').
            ' (path: '.$supabasePath.')'
        );
    }

    private function buildFilename(string $prefix, UploadedFile $file): string
    {
        return $prefix.'-'.time().'-'.uniqid().'.'.$file->getClientOriginalExtension();
    }
}
