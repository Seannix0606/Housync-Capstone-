<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SuperAdminProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        $user = User::updateOrCreate(
            ['email' => 'admin@housesync.com'],
            [
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        // Create or update the super admin profile
        SuperAdminProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Super Admin',
                'status' => 'active',
                'phone' => '+1234567890',
                'address' => 'HouseSync Headquarters',
            ]
        );
    }
}
