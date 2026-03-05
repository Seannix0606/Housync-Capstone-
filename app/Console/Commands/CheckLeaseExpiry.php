<?php

namespace App\Console\Commands;

use App\Mail\LeaseExpiryReminderMail;
use App\Models\TenantAssignment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;

class CheckLeaseExpiry extends Command
{
    protected $signature = 'lease:check-expiry {--days=30 : Notify tenants with leases expiring within this many days}';

    protected $description = 'Check for leases expiring soon and notify tenants and landlords';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days)->toDateString();

        $this->info("Checking for leases expiring within {$days} days (by {$targetDate})...");

        $expiringAssignments = TenantAssignment::where('status', 'active')
            ->whereNotNull('lease_end_date')
            ->where('lease_end_date', '<=', $targetDate)
            ->where('lease_end_date', '>=', now()->toDateString())
            ->with(['tenant', 'landlord', 'unit.property'])
            ->get();

        if ($expiringAssignments->isEmpty()) {
            $this->info('No leases expiring within the specified period.');
            return self::SUCCESS;
        }

        $notified = 0;

        foreach ($expiringAssignments as $assignment) {
            $daysRemaining = now()->startOfDay()->diffInDays($assignment->lease_end_date);

            // Only send on specific milestones: 30, 14, 7, 3, 1 days
            if (!in_array($daysRemaining, [30, 14, 7, 3, 1])) {
                continue;
            }

            try {
                // Notify tenant via database notification
                if ($assignment->tenant) {
                    $assignment->tenant->notify(
                        new \App\Notifications\LeaseExpiryReminder($assignment, $daysRemaining)
                    );
                }

                // Notify landlord via database notification
                if ($assignment->landlord) {
                    $assignment->landlord->notify(
                        new \App\Notifications\LeaseExpiryReminder($assignment, $daysRemaining)
                    );
                }

                $notified++;
                $this->line("  Notified: {$assignment->tenant?->name} - Unit {$assignment->unit?->unit_number} - {$daysRemaining} days remaining");
            } catch (\Exception $e) {
                Log::error('Failed to send lease expiry notification', [
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  Failed: {$assignment->tenant?->name} - {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Done! Notified {$notified} tenants/landlords.");

        return self::SUCCESS;
    }
}
