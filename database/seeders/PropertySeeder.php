<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * One sample property per property_type so local/demo DB covers every listing shape.
     * Unit counts match {@see \App\Services\Landlord\PropertyTypeUnitRules} (house/townhouse: 1, duplex: 2; apartment/condo: ≥2).
     */
    public function run(): void
    {
        $landlords = User::where('role', 'landlord')
            ->whereHas('landlordProfile', function ($query) {
                $query->where('status', 'approved');
            })->get();

        if ($landlords->isEmpty()) {
            $this->command->info('No approved landlords found. Skipping property seeding.');

            return;
        }

        $primaryLandlord = User::where('email', 'landlord@example.com')
            ->whereHas('landlordProfile', fn ($q) => $q->where('status', 'approved'))
            ->first()
            ?? $landlords->first();

        $landlordId = $primaryLandlord->id;

        $properties = [
            [
                'name' => 'Sunrise Apartments',
                'slug' => 'sunrise-apartments',
                'property_type' => 'apartment',
                'address' => '123 Main Street, Manila',
                'description' => 'Modern apartment complex with excellent amenities.',
                'total_units' => 6,
                'floors' => 3,
                'building_floors' => 3,
                'contact_person' => 'John Manager',
                'contact_phone' => '09171234567',
                'amenities' => ['wifi', 'parking', 'security'],
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Green Valley Condos',
                'slug' => 'green-valley-condos',
                'property_type' => 'condominium',
                'address' => '456 Ayala Avenue, Makati',
                'description' => 'Premium condominium in the heart of the business district.',
                'total_units' => 6,
                'floors' => 4,
                'building_floors' => 4,
                'contact_person' => 'Jane Admin',
                'contact_phone' => '09187654321',
                'amenities' => ['pool', 'gym', 'wifi', 'parking', 'security', '24/7 reception'],
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Oak Lane Townhouse',
                'slug' => 'demo-townhouse-oak',
                'property_type' => 'townhouse',
                'address' => '210 Oak Lane, Quezon City',
                'description' => 'End-unit townhome; one dwelling, one rental unit.',
                'total_units' => 1,
                'floors' => 2,
                'building_floors' => 2,
                'contact_person' => 'Sample Landlord',
                'contact_phone' => '09170001111',
                'amenities' => ['parking', 'security'],
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Elm Street Single Family',
                'slug' => 'demo-house-elm',
                'property_type' => 'house',
                'address' => '88 Elm Street, Pasig',
                'description' => 'Detached single-family home listed as one rental unit.',
                'total_units' => 1,
                'floors' => 2,
                'building_floors' => 2,
                'bedrooms' => 3,
                'contact_person' => 'Sample Landlord',
                'contact_phone' => '09170002222',
                'amenities' => ['parking', 'garden'],
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Maple Duplex',
                'slug' => 'demo-duplex-maple',
                'property_type' => 'duplex',
                'address' => '45 Maple Avenue, Taguig',
                'description' => 'Two-unit duplex building (one property, two rentals).',
                'total_units' => 2,
                'floors' => 2,
                'building_floors' => 2,
                'contact_person' => 'Sample Landlord',
                'contact_phone' => '09170003333',
                'amenities' => ['parking'],
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Pine Boarding House',
                'slug' => 'demo-others-pine',
                'property_type' => 'others',
                'address' => '7 Pine Road, Mandaluyong',
                'description' => 'Other / mixed layout: sample multi-room rental with three units.',
                'total_units' => 3,
                'floors' => 2,
                'building_floors' => 2,
                'contact_person' => 'Sample Landlord',
                'contact_phone' => '09170004444',
                'amenities' => ['wifi', 'shared kitchen'],
                'status' => 'active',
                'is_active' => true,
            ],
        ];

        foreach ($properties as $propertyData) {
            Property::updateOrCreate(
                ['slug' => $propertyData['slug']],
                array_merge($propertyData, [
                    'landlord_id' => $landlordId,
                ])
            );
        }

        $this->command->info('Created or updated '.count($properties).' sample properties (all property types).');
    }
}
