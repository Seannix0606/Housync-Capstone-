<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * This seeder now creates Properties (formerly Apartments)
 * @deprecated Use PropertySeeder instead
 */
class ApartmentSeeder extends Seeder
{
    public function run()
    {
        $landlord = User::where('email', 'landlord@example.com')->first();
        if (!$landlord) return;

        Property::updateOrCreate(
            ['slug' => 'sunset-apartments'],
            [
                'name' => 'Sunset Apartments',
                'slug' => 'sunset-apartments',
                'property_type' => 'apartment',
                'address' => '123 Main St',
                'description' => 'Modern apartment complex',
                'total_units' => 10,
                'landlord_id' => $landlord->id,
                'contact_person' => $landlord->name,
                'contact_phone' => $landlord->phone,
                'contact_email' => $landlord->email,
                'amenities' => ['parking', 'pool', 'gym'],
                'status' => 'active',
                'is_active' => true,
            ]
        );

        Property::updateOrCreate(
            ['slug' => 'greenview-residences'],
            [
                'name' => 'Greenview Residences',
                'slug' => 'greenview-residences',
                'property_type' => 'apartment',
                'address' => '456 Oak Ave',
                'description' => 'Family-friendly apartments',
                'total_units' => 8,
                'landlord_id' => $landlord->id,
                'contact_person' => $landlord->name,
                'contact_phone' => $landlord->phone,
                'contact_email' => $landlord->email,
                'amenities' => ['garden', 'playground'],
                'status' => 'active',
                'is_active' => true,
            ]
        );
    }
}
