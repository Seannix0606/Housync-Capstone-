<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LandlordProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LandlordSeeder extends Seeder
{
    public function run()
    {
        $user = User::updateOrCreate(
            ['email' => 'landlord@example.com'],
            [
                'password' => Hash::make('password'),
                'role' => 'landlord',
            ]
        );

        LandlordProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Sample Landlord',
                'status' => 'approved',
                'phone' => '1234567890',
                'address' => 'Sample Address',
                'business_info' => 'Sample landlord business',
            ]
        );
    }
}
