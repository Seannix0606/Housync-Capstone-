<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LandlordProfile;
use App\Models\TenantProfile;
use App\Models\StaffProfile;
use App\Models\SuperAdminProfile;

class BackfillRoleProfiles extends Command
{
    protected $signature = 'profiles:backfill 
        {--dry-run : Show what would be created without writing}
        {--landlord-company=N/A : Default company_name for landlord profiles when missing}
        {--staff-type=general : Default staff_type for staff profiles when missing}
        {--super-admin-notes=Backfilled by profiles:backfill : Default notes for super admin profiles}';

    protected $description = 'Create missing role-specific profiles for existing users';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $defaults = [
            'landlord_company' => (string) $this->option('landlord-company'),
            'staff_type' => (string) $this->option('staff-type'),
            'super_admin_notes' => (string) $this->option('super-admin-notes'),
        ];

        $created = [
            'landlord' => 0,
            'tenant' => 0,
            'staff' => 0,
            'super_admin' => 0,
        ];

        User::chunk(200, function ($users) use (&$created, $dryRun, $defaults) {
            foreach ($users as $user) {
                switch ($user->role) {
                    case 'landlord':
                        if (!$user->landlordProfile) {
                            $this->info("Missing landlord profile for user #{$user->id} ({$user->email})");
                            if (!$dryRun) {
                                LandlordProfile::create([
                                    'user_id' => $user->id,
                                    'name' => $user->name ?? 'User',
                                    'phone' => $user->getAttribute('phone'),
                                    'address' => $user->getAttribute('address'),
                                    'business_info' => $user->getAttribute('business_info'),
                                    'company_name' => $defaults['landlord_company'],
                                    'status' => 'pending',
                                ]);
                            }
                            $created['landlord']++;
                        }
                        break;
                    case 'tenant':
                        if (!$user->tenantProfile) {
                            $this->info("Missing tenant profile for user #{$user->id} ({$user->email})");
                            if (!$dryRun) {
                                TenantProfile::create([
                                    'user_id' => $user->id,
                                    'name' => $user->name ?? 'User',
                                    'phone' => $user->getAttribute('phone'),
                                    'address' => $user->getAttribute('address'),
                                    'status' => 'active',
                                ]);
                            }
                            $created['tenant']++;
                        }
                        break;
                    case 'staff':
                        if (!$user->staffProfile) {
                            $this->info("Missing staff profile for user #{$user->id} ({$user->email})");
                            if (!$dryRun) {
                                StaffProfile::create([
                                    'user_id' => $user->id,
                                    'name' => $user->name ?? 'User',
                                    'phone' => $user->getAttribute('phone'),
                                    'address' => $user->getAttribute('address'),
                                    'staff_type' => $user->getAttribute('staff_type') ?? $defaults['staff_type'],
                                    'status' => 'active',
                                ]);
                            }
                            $created['staff']++;
                        }
                        break;
                    case 'super_admin':
                        if (!$user->superAdminProfile) {
                            $this->info("Missing super admin profile for user #{$user->id} ({$user->email})");
                            if (!$dryRun) {
                                SuperAdminProfile::create([
                                    'user_id' => $user->id,
                                    'name' => $user->name ?? 'User',
                                    'phone' => $user->getAttribute('phone'),
                                    'address' => $user->getAttribute('address'),
                                    'notes' => $defaults['super_admin_notes'],
                                    'status' => 'active',
                                ]);
                            }
                            $created['super_admin']++;
                        }
                        break;
                }
            }
        });

        $this->table(['Role', 'Profiles Created'], [
            ['landlord', $created['landlord']],
            ['tenant', $created['tenant']],
            ['staff', $created['staff']],
            ['super_admin', $created['super_admin']],
        ]);

        if ($dryRun) {
            $this->warn('Dry-run mode: No changes were written. Re-run without --dry-run to write.');
        }

        return self::SUCCESS;
    }
}