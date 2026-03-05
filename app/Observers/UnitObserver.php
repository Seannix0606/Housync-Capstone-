<?php

namespace App\Observers;

use App\Models\Unit;
use App\Models\Property;
use App\Models\Amenity;
use Illuminate\Support\Str;

class UnitObserver
{
    /**
     * Handle the Unit "created" event.
     */
    public function created(Unit $unit): void
    {
        $this->syncToProperty($unit);
    }

    /**
     * Handle the Unit "updated" event.
     */
    public function updated(Unit $unit): void
    {
        $this->syncToProperty($unit);
    }

    /**
     * Handle the Unit "deleted" event.
     */
    public function deleted(Unit $unit): void
    {
        // Find and soft delete the corresponding property
        $slug = Str::slug($this->generateTitle($unit) . '-' . $unit->id);
        
        $property = Property::where('slug', $slug)->first();
        if ($property) {
            $property->delete();
        }
    }

    /**
     * Handle the Unit "restored" event.
     */
    public function restored(Unit $unit): void
    {
        // Restore the corresponding property
        $slug = Str::slug($this->generateTitle($unit) . '-' . $unit->id);
        
        $property = Property::withTrashed()->where('slug', $slug)->first();
        if ($property) {
            $property->restore();
        }
        
        $this->syncToProperty($unit);
    }

    /**
     * Sync unit data to property
     */
    protected function syncToProperty(Unit $unit)
    {
        // Load apartment relationship if not loaded
        if (!$unit->relationLoaded('apartment')) {
            $unit->load('apartment');
        }

        $apartment = $unit->apartment;
        
        if (!$apartment) {
            return; // Skip if apartment doesn't exist
        }

        $title = $this->generateTitle($unit);
        $slug = Str::slug($title . '-' . $unit->id);

        // Map unit type to property type
        $type = $this->mapUnitTypeToPropertyType($unit->unit_type);

        // Determine availability
        $availabilityStatus = $unit->status === 'available' ? 'available' : 'occupied';

        // Create or update property
        $property = Property::withTrashed()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'description' => $unit->description ?: $apartment->description ?: "Beautiful {$unit->bedrooms} bedroom unit in {$apartment->name}",
                'type' => $type,
                'price' => $unit->rent_amount,
                'address' => $apartment->address,
                'city' => $this->extractCity($apartment->address),
                'bedrooms' => $unit->bedrooms,
                'bathrooms' => $unit->bathrooms,
                'area' => $unit->floor_area,
                // Store raw storage-relative path (e.g., unit-covers/abc.jpg)
                'image_path' => $this->normalizeImagePath($unit->cover_image ?: $apartment->cover_image),
                'availability_status' => $availabilityStatus,
                'landlord_id' => $apartment->landlord_id,
                'is_featured' => false,
                'is_active' => $apartment->status === 'active' && $unit->status !== 'maintenance',
                'deleted_at' => null, // Ensure it's not soft deleted when updating
            ]
        );

        // Sync amenities
        $this->syncAmenities($property, $unit, $apartment);
    }

    /**
     * Generate property title
     */
    protected function generateTitle(Unit $unit)
    {
        $apartment = $unit->apartment;
        return $apartment ? "{$apartment->name} - Unit {$unit->unit_number}" : "Unit {$unit->unit_number}";
    }

    /**
     * Map unit type to property type
     */
    protected function mapUnitTypeToPropertyType($unitType)
    {
        $mapping = [
            'studio' => 'studio',
            'apartment' => 'apartment',
            '1-bedroom' => 'apartment',
            '2-bedroom' => 'apartment',
            '3-bedroom' => 'apartment',
            'condo' => 'condo',
            'house' => 'house',
        ];

        $unitTypeLower = strtolower($unitType);

        foreach ($mapping as $key => $value) {
            if (str_contains($unitTypeLower, $key)) {
                return $value;
            }
        }

        return 'apartment';
    }

    /**
     * Extract city from address
     */
    protected function extractCity($address)
    {
        if (empty($address)) {
            return 'Metro Manila';
        }

        // Try to extract city from address
        $parts = explode(',', $address);
        
        // Common pattern: "Street, City, State"
        if (count($parts) >= 2) {
            $city = trim($parts[1]);
            // Remove any state/country info if present
            $cityParts = explode(' ', $city);
            return $cityParts[0];
        }

        return 'Metro Manila';
    }

    /**
     * Sync amenities
     */
    protected function syncAmenities(Property $property, Unit $unit, $apartment)
    {
        $amenityNames = [];

        // Get amenities from unit
        if ($unit->amenities && is_array($unit->amenities)) {
            $amenityNames = array_merge($amenityNames, $unit->amenities);
        }

        // Get amenities from apartment
        if ($apartment->amenities && is_array($apartment->amenities)) {
            $amenityNames = array_merge($amenityNames, $apartment->amenities);
        }

        // Add furnished if applicable
        if ($unit->is_furnished && !in_array('Furnished', $amenityNames)) {
            $amenityNames[] = 'Furnished';
        }

        // Remove duplicates and empty values
        $amenityNames = array_filter(array_unique($amenityNames));

        // Find matching amenity IDs
        $amenityIds = [];
        
        foreach ($amenityNames as $amenityName) {
            $amenity = Amenity::where('name', 'LIKE', "%{$amenityName}%")
                ->orWhere('slug', Str::slug($amenityName))
                ->first();

            if ($amenity) {
                $amenityIds[] = $amenity->id;
            }
        }

        // Sync amenities (this will add new and remove old ones)
        if (!empty($amenityIds)) {
            $property->amenities()->sync($amenityIds);
        } else {
            // If no amenities, clear them
            $property->amenities()->detach();
        }
    }

    /**
     * Normalize stored image path to be storage-disk relative (no leading storage/ or public/)
     */
    protected function normalizeImagePath($path)
    {
        if (empty($path)) {
            return null;
        }

        // If path already includes storage/ prefix, strip it
        if (str_starts_with($path, 'storage/')) {
            return ltrim(substr($path, strlen('storage/')), '/');
        }

        // If starts with public/ (some apps store that), strip it
        if (str_starts_with($path, 'public/')) {
            return ltrim(substr($path, strlen('public/')), '/');
        }

        // If it is a full URL, just return as-is (model accessor will pass it through)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Otherwise, assume already disk-relative such as unit-covers/xyz.jpg
        return ltrim($path, '/');
    }
}

