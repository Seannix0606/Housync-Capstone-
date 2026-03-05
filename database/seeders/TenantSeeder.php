<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TenantProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $user1 = User::updateOrCreate(
            ['email' => 'tenant1@example.com'],
            [
                'password' => Hash::make('password'),
                'role' => 'tenant',
            ]
        );

        TenantProfile::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'name' => 'Sample Tenant',
                'status' => 'active',
                'phone' => '5551234567',
                'address' => 'Tenant Address 1',
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'tenant2@example.com'],
            [
                'password' => Hash::make('password'),
                'role' => 'tenant',
            ]
        );

        TenantProfile::updateOrCreate(
            ['user_id' => $user2->id],
            [
                'name' => 'Second Tenant',
                'status' => 'active',
                'phone' => '5559876543',
                'address' => 'Tenant Address 2',
            ]
        );
    }
}
