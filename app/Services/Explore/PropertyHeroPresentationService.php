<?php

namespace App\Services\Explore;

use App\Models\Property;

class PropertyHeroPresentationService
{
    /**
     * Build property-only hero media view model.
     */
    public function build(Property $property): array
    {
        $heroImageUrl = $this->resolveHeroImageUrl($property);

        return [
            'hero_image_url' => $heroImageUrl,
            'has_hero_image' => ! is_null($heroImageUrl),
            'hero_alt_text' => ($property->name ?? 'Property').' hero image',
        ];
    }

    private function resolveHeroImageUrl(Property $property): ?string
    {
        if (! empty($property->cover_image_url)) {
            return $property->cover_image_url;
        }

        $galleryUrls = $property->gallery_urls ?? [];
        if (is_array($galleryUrls) && ! empty($galleryUrls[0])) {
            return $galleryUrls[0];
        }

        return null;
    }
}
