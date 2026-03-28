<?php

namespace App\Console\Commands;

use App\Models\LandlordProfile;
use App\Models\StaffProfile;
use App\Models\SuperAdminProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Console\Command;

class BackfillRoleProfiles extends Command
{
    protected $signature = 'profiles:backfill
        {--dry-run : Preview what would be created without writing any changes}
        {--force : Skip the confirmation prompt (required for CI / non-interactive use)}
        {--landlord-company=N/A : Default company_name for landlord profiles when missing}
        {--landlord-status=pending : Status assigned to new landlord profiles (pending|approved|rejected). Use "approved" only when all affected landlords are known-good. Defaults to "pending" to be safe.}
        {--staff-type=general : Default staff_type for staff profiles when missing}
        {--super-admin-notes=Backfilled by profiles:backfill : Default notes for super admin profiles}';

    protected $description = 'Create missing role-specific profiles for existing users';

    private const VALID_LANDLORD_STATUSES = ['pending', 'approved', 'rejected'];

    public function handle(): int
    {
        $dryRun         = (bool) $this->option('dry-run');
        $force          = (bool) $this->option('force');
        $landlordStatus = (string) $this->option('landlord-status');

        if (! in_array($landlordStatus, self::VALID_LANDLORD_STATUSES, true)) {
            $this->error("Invalid --landlord-status value \"{$landlordStatus}\". Allowed: " . implode(', ', self::VALID_LANDLORD_STATUSES));
            return self::FAILURE;
        }

        $defaults = [
            'landlord_company'  => (string) $this->option('landlord-company'),
            'landlord_status'   => $landlordStatus,
            'staff_type'        => (string) $this->option('staff-type'),
            'super_admin_notes' => (string) $this->option('super-admin-notes'),
        ];

        // --- Safety warning -------------------------------------------------------
        $this->warn('╔══════════════════════════════════════════════════════════════╗');
        $this->warn('║              profiles:backfill — SAFETY WARNING              ║');
        $this->warn('╠══════════════════════════════════════════════════════════════╣');
        $this->warn('║ This command creates missing role profiles for existing       ║');
        $this->warn('║ users. Landlord profiles are created with:                   ║');
        $this->warn("║   status = \"{$landlordStatus}\"" . str_repeat(' ', 46 - strlen($landlordStatus)) . '║');
        $this->warn('║                                                               ║');
        $this->warn('║ ⚠  If previously-approved landlords have lost their profiles  ║');
        $this->warn('║    and you pass the default (pending), they will be LOCKED   ║');
        $this->warn('║    out until a super admin re-approves them.                 ║');
        $this->warn('║                                                               ║');
        $this->warn('║ Run with --dry-run first to review affected users.            ║');
        $this->warn('║ Override status with: --landlord-status=approved             ║');
        $this->warn('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // --- Dry-run or confirmation gate -----------------------------------------
        if (! $dryRun && ! $force) {
            if (! $this->confirm('Proceed and write changes to the database?', false)) {
                $this->info('Aborted. No changes written.');
                return self::SUCCESS;
            }
            $this->newLine();
        }

        $created = [
            'landlord'    => 0,
            'tenant'      => 0,
            'staff'       => 0,
            'super_admin' => 0,
        ];

        User::chunk(200, function ($users) use (&$created, $dryRun, $defaults) {
            foreach ($users as $user) {
                switch ($user->role) {
                    case 'landlord':
                        if (! $user->landlordProfile) {
                            $this->line("  [landlord]    #{$user->id} {$user->email}  →  status={$defaults['landlord_status']}");
                            if (! $dryRun) {
                                LandlordProfile::create([
                                    'user_id'       => $user->id,
                                    'name'          => $user->name ?? 'User',
                                    'phone'         => $user->getAttribute('phone'),
                                    'address'       => $user->getAttribute('address'),
                                    'business_info' => $user->getAttribute('business_info'),
                                    'company_name'  => $defaults['landlord_company'],
                                    'status'        => $defaults['landlord_status'],
                                ]);
                            }
                            $created['landlord']++;
                        }
                        break;

                    case 'tenant':
                        if (! $user->tenantProfile) {
                            $this->line("  [tenant]      #{$user->id} {$user->email}  →  status=active");
                            if (! $dryRun) {
                                TenantProfile::create([
                                    'user_id' => $user->id,
                                    'name'    => $user->name ?? 'User',
                                    'phone'   => $user->getAttribute('phone'),
                                    'address' => $user->getAttribute('address'),
                                    'status'  => 'active',
                                ]);
                            }
                            $created['tenant']++;
                        }
                        break;

                    case 'staff':
                        if (! $user->staffProfile) {
                            $this->line("  [staff]       #{$user->id} {$user->email}  →  status=active");
                            if (! $dryRun) {
                                StaffProfile::create([
                                    'user_id'    => $user->id,
                                    'name'       => $user->name ?? 'User',
                                    'phone'      => $user->getAttribute('phone'),
                                    'address'    => $user->getAttribute('address'),
                                    'staff_type' => $user->getAttribute('staff_type') ?? $defaults['staff_type'],
                                    'status'     => 'active',
                                ]);
                            }
                            $created['staff']++;
                        }
                        break;

                    case 'super_admin':
                        if (! $user->superAdminProfile) {
                            $this->line("  [super_admin] #{$user->id} {$user->email}  →  status=active");
                            if (! $dryRun) {
                                SuperAdminProfile::create([
                                    'user_id' => $user->id,
                                    'name'    => $user->name ?? 'User',
                                    'phone'   => $user->getAttribute('phone'),
                                    'address' => $user->getAttribute('address'),
                                    'notes'   => $defaults['super_admin_notes'],
                                    'status'  => 'active',
                                ]);
                            }
                            $created['super_admin']++;
                        }
                        break;
                }
            }
        });

        $this->newLine();
        $this->table(
            ['Role', $dryRun ? 'Profiles That Would Be Created' : 'Profiles Created'],
            [
                ['landlord',    $created['landlord']],
                ['tenant',      $created['tenant']],
                ['staff',       $created['staff']],
                ['super_admin', $created['super_admin']],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry-run mode: No changes were written. Re-run without --dry-run to apply.');
        } else {
            $total = array_sum($created);
            $this->info("Done. {$total} profile(s) created.");
        }

        return self::SUCCESS;
    }
}
