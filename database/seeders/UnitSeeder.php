<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $property = Property::first();
        $landlord = User::where('role', 'landlord')->first();
        if (!$property || !$landlord) return;

        $units = [
            [
                'unit_number' => 'Unit 01',
                'property_id' => $property->id,
                'unit_type' => '1 Bedroom',
                'rent_amount' => 8500.00,
                'status' => 'available',
                'leasing_type' => 'separate',
                'tenant_count' => 0,
                'description' => 'Modern 1-bedroom unit with city view',
                'floor_area' => 35.5,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'is_furnished' => true,
                'amenities' => ['AC', 'WiFi', 'Cable TV'],
                'notes' => 'Excellent location',
            ],
            [
                'unit_number' => 'Unit 02',
                'property_id' => $property->id,
                'unit_type' => 'Studio',
                'rent_amount' => 6000.00,
                'status' => 'available',
                'leasing_type' => 'inclusive',
                'tenant_count' => 0,
                'description' => 'Compact studio perfect for professionals',
                'floor_area' => 25.0,
                'bedrooms' => 0,
                'bathrooms' => 1,
                'is_furnished' => false,
                'amenities' => ['WiFi'],
                'notes' => 'Recently renovated',
            ],
            [
                'unit_number' => 'Unit 03',
                'property_id' => $property->id,
                'unit_type' => '2 Bedroom',
                'rent_amount' => 12000.00,
                'status' => 'available',
                'leasing_type' => 'separate',
                'tenant_count' => 0,
                'description' => 'Spacious 2-bedroom unit perfect for families',
                'floor_area' => 55.0,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'is_furnished' => true,
                'amenities' => ['AC', 'WiFi', 'Cable TV', 'Washing Machine'],
                'notes' => 'Ready for immediate occupancy'
            ],
            [
                'unit_number' => 'Unit 04',
                'property_id' => $property->id,
                'unit_type' => '2 Bedroom',
                'rent_amount' => 11500.00,
                'status' => 'available',
                'leasing_type' => 'inclusive',
                'tenant_count' => 0,
                'description' => 'Well-maintained 2-bedroom unit',
                'floor_area' => 52.0,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'is_furnished' => true,
                'amenities' => ['AC', 'WiFi', 'Cable TV'],
                'notes' => 'Great for families'
            ],
            [
                'unit_number' => 'Unit 05',
                'property_id' => $property->id,
                'unit_type' => '1 Bedroom',
                'rent_amount' => 9000.00,
                'status' => 'available',
                'leasing_type' => 'separate',
                'tenant_count' => 0,
                'description' => 'Bright 1-bedroom with balcony',
                'floor_area' => 40.0,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'is_furnished' => false,
                'amenities' => ['WiFi', 'Balcony'],
                'notes' => 'Recently painted and cleaned'
            ],
        ];

        foreach ($units as $unitData) {
            Unit::create($unitData);
        }

        $this->command->info('Created ' . count($units) . ' sample units.');
    }
}
