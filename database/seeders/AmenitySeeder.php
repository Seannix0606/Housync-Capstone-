<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'WiFi', 'icon' => 'fas fa-wifi'],
            ['name' => 'Parking', 'icon' => 'fas fa-parking'],
            ['name' => 'Air Conditioning', 'icon' => 'fas fa-snowflake'],
            ['name' => 'Pool', 'icon' => 'fas fa-swimming-pool'],
            ['name' => 'Pet Friendly', 'icon' => 'fas fa-paw'],
            ['name' => 'Furnished', 'icon' => 'fas fa-couch'],
            ['name' => 'Gym', 'icon' => 'fas fa-dumbbell'],
            ['name' => 'Security', 'icon' => 'fas fa-shield-alt'],
            ['name' => 'Laundry', 'icon' => 'fas fa-tshirt'],
            ['name' => 'Balcony', 'icon' => 'fas fa-home'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create([
                'name' => $amenity['name'],
                'icon' => $amenity['icon'],
                'slug' => Str::slug($amenity['name']),
            ]);
        }
    }
}

