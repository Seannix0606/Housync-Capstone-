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
        // No sample data: landlords, tenants, units, and documents are not seeded.
        // After `migrate:fresh`, you get schema + migration defaults only (super admin
        // from ensure_super_admin_exists, settings rows from create_settings_table).
        // To seed demo data again, run individual seeders, e.g.:
        // php artisan db:seed --class=LandlordSeeder
    }
}
