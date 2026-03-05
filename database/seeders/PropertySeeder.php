<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use App\Models\LandlordProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Note: Properties are now landlord buildings (formerly apartments).
     * Public listings come from Units, not this table.
     */
    public function run(): void
    {
        // Find landlords to create properties for
        $landlords = User::where('role', 'landlord')
            ->whereHas('landlordProfile', function($q) {
                $q->where('status', 'approved');
            })->get();

        if ($landlords->isEmpty()) {
            $this->command->info('No approved landlords found. Skipping property seeding.');
            return;
        }

        $properties = [
            [
                'name' => 'Sunrise Apartments',
                'slug' => 'sunrise-apartments',
                'property_type' => 'apartment',
                'address' => '123 Main Street, Manila',
                'description' => 'Modern apartment complex with excellent amenities.',
                'total_units' => 10,
                'floors' => 5,
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
                'total_units' => 20,
                'floors' => 10,
                'contact_person' => 'Jane Admin',
                'contact_phone' => '09187654321',
                'amenities' => ['pool', 'gym', 'wifi', 'parking', 'security', '24/7 reception'],
                'status' => 'active',
                'is_active' => true,
            ],
        ];

        foreach ($properties as $propertyData) {
            Property::updateOrCreate(
                ['slug' => $propertyData['slug']],
                array_merge($propertyData, [
                    'landlord_id' => $landlords->random()->id,
                ])
            );
        }

        $this->command->info('Created ' . count($properties) . ' sample properties.');
    }
}
