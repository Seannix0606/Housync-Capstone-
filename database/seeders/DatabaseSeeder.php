<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo users and sample portfolio (safe to re-run: seeders use updateOrCreate where needed).
        $this->call([
            SuperAdminSeeder::class,
            LandlordSeeder::class,
            TenantSeeder::class,
            AmenitySeeder::class,
            PropertySeeder::class,
            UnitSeeder::class,
        ]);
    }
}
