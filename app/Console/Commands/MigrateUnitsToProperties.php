<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use App\Models\Unit;
use App\Models\Property;
use App\Models\Amenity;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateUnitsToProperties extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrate:units-to-properties {--fresh : Delete existing properties first}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate existing apartments and units to the new properties system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of units to properties...');

        // Optional: Clear existing properties
        if ($this->option('fresh')) {
            $this->warn('Deleting existing properties...');
            Property::truncate();
            \DB::table('property_amenity')->truncate();
            $this->info('Existing properties cleared.');
        }

        // Get all apartments with their units and landlord
        $apartments = Apartment::with(['units', 'landlord'])->get();

        if ($apartments->isEmpty()) {
            $this->error('No apartments found to migrate.');
            return 1;
        }

        $this->info("Found {$apartments->count()} apartments to migrate.");

        $totalUnits = 0;
        $migratedCount = 0;

        foreach ($apartments as $apartment) {
            $this->info("\nProcessing: {$apartment->name}");

            foreach ($apartment->units as $unit) {
                $totalUnits++;
                
                try {
                    $property = $this->migrateUnit($unit, $apartment);
                    $migratedCount++;
                    $this->line("  ✓ Migrated: {$property->title}");
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to migrate unit {$unit->unit_number}: {$e->getMessage()}");
                }
            }
        }

        $this->newLine();
        $this->info("Migration completed!");
        $this->info("Total units found: {$totalUnits}");
        $this->info("Successfully migrated: {$migratedCount}");

        if ($migratedCount < $totalUnits) {
            $this->warn("Failed to migrate: " . ($totalUnits - $migratedCount) . " units");
        }

        return 0;
    }

    /**
     * Migrate a single unit to a property
     */
    protected function migrateUnit(Unit $unit, Apartment $apartment)
    {
        // Create property title
        $title = "{$apartment->name} - Unit {$unit->unit_number}";

        // Map unit_type to property type
        $type = $this->mapUnitTypeToPropertyType($unit->unit_type);

        // Determine availability
        $availabilityStatus = $unit->status === 'available' ? 'available' : 'occupied';

        // Create or update property
        $property = Property::updateOrCreate(
            [
                'slug' => Str::slug($title . '-' . $unit->id),
            ],
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
                'image_path' => $unit->cover_image ?: $apartment->cover_image,
                'availability_status' => $availabilityStatus,
                'landlord_id' => $apartment->landlord_id,
                'is_featured' => false,
                'is_active' => $apartment->status === 'active',
            ]
        );

        // Attach amenities
        $this->attachAmenities($property, $unit, $apartment);

        return $property;
    }

    /**
     * Map unit_type to property type
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

        // Default based on bedrooms
        return 'apartment';
    }

    /**
     * Extract city from address
     */
    protected function extractCity($address)
    {
        // Try to extract city from address (simple logic)
        $parts = explode(',', $address);
        return isset($parts[1]) ? trim($parts[1]) : 'Metro Manila';
    }

    /**
     * Attach amenities to property
     */
    protected function attachAmenities(Property $property, Unit $unit, Apartment $apartment)
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

        // Remove duplicates
        $amenityNames = array_unique($amenityNames);

        if (empty($amenityNames)) {
            // If unit is furnished, add furnished amenity
            if ($unit->is_furnished) {
                $amenityNames[] = 'Furnished';
            }
        }

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

        // Attach amenities
        if (!empty($amenityIds)) {
            $property->amenities()->sync($amenityIds);
        }
    }
}

