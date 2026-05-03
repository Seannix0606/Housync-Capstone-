<?php

namespace App\Console\Commands;

use Database\Seeders\SuperAdminSeeder;
use Illuminate\Console\Command;

class EnsureSuperAdmin extends Command
{
    protected $signature = 'superadmin:ensure';

    protected $description = 'Create or reset the default super admin (admin@housesync.com). Use after wiping user rows while migrations stay applied; migrations only run this logic once.';

    public function handle(): int
    {
        $this->info('Ensuring super admin user + profile exist (same as SuperAdminSeeder)...');
        $this->call('db:seed', ['--class' => SuperAdminSeeder::class, '--force' => true]);

        return self::SUCCESS;
    }
}
