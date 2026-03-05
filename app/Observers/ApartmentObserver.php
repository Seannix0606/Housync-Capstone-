<?php

namespace App\Observers;

use App\Models\Apartment;
use App\Models\Property;
use Illuminate\Support\Str;

class ApartmentObserver
{
    /**
     * Handle the Apartment "updated" event.
     * When apartment details change, update all related properties
     */
    public function updated(Apartment $apartment): void
    {
        // When apartment is updated, sync all its units' properties
        $apartment->load('units');
        
        foreach ($apartment->units as $unit) {
            $slug = Str::slug("{$apartment->name} - Unit {$unit->unit_number}" . '-' . $unit->id);
            
            $property = Property::where('slug', $slug)->first();
            
            if ($property) {
                // Update fields that come from the apartment
                $property->update([
                    'title' => "{$apartment->name} - Unit {$unit->unit_number}",
                    'address' => $apartment->address,
                    'city' => $this->extractCity($apartment->address),
                    'is_active' => $apartment->status === 'active' && $property->availability_status === 'available',
                ]);

                // Update description if unit doesn't have its own
                if (empty($unit->description) && !empty($apartment->description)) {
                    $property->update([
                        'description' => $apartment->description,
                    ]);
                }

                // Update image if unit doesn't have its own
                if (empty($unit->cover_image) && !empty($apartment->cover_image)) {
                    $normalized = $this->normalizeImagePath($apartment->cover_image);
                    $property->update([
                        'image_path' => $normalized,
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Apartment "deleted" event.
     * When an apartment is deleted, soft delete all related properties
     */
    public function deleted(Apartment $apartment): void
    {
        // Soft delete all properties associated with this apartment's units
        $unitIds = $apartment->units()->pluck('id');
        
        foreach ($unitIds as $unitId) {
            $properties = Property::where('slug', 'LIKE', "%-{$unitId}")->get();
            
            foreach ($properties as $property) {
                $property->delete();
            }
        }
    }

    /**
     * Handle the Apartment "restored" event.
     */
    public function restored(Apartment $apartment): void
    {
        // Restore all properties associated with this apartment's units
        $unitIds = $apartment->units()->pluck('id');
        
        foreach ($unitIds as $unitId) {
            $properties = Property::withTrashed()->where('slug', 'LIKE', "%-{$unitId}")->get();
            
            foreach ($properties as $property) {
                $property->restore();
            }
        }
    }

    /**
     * Extract city from address
     */
    protected function extractCity($address)
    {
        if (empty($address)) {
            return 'Metro Manila';
        }

        $parts = explode(',', $address);
        
        if (count($parts) >= 2) {
            $city = trim($parts[1]);
            $cityParts = explode(' ', $city);
            return $cityParts[0];
        }

        return 'Metro Manila';
    }
    /**
     * Normalize stored image path to be storage-disk relative (no leading storage/ or public/)
     */
    protected function normalizeImagePath($path)
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'storage/')) {
            return ltrim(substr($path, strlen('storage/')), '/');
        }

        if (str_starts_with($path, 'public/')) {
            return ltrim(substr($path, strlen('public/')), '/');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return ltrim($path, '/');
    }
}

