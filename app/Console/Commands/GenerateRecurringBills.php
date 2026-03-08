<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Notifications\BillCreated;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateRecurringBills extends Command
{
    protected $signature = 'billing:generate-recurring {--type=rent : Bill type to generate} {--dry-run : Preview without creating}';

    protected $description = 'Generate recurring monthly bills for all active tenant assignments';

    public function handle(): int
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');
        $now = now();
        $billingPeriodStart = $now->copy()->startOfMonth();
        $billingPeriodEnd = $now->copy()->endOfMonth();
        $dueDate = $now->copy()->startOfMonth()->addDays(14);

        $this->info("Generating {$type} bills for {$billingPeriodStart->format('F Y')}...");

        $assignments = TenantAssignment::where('status', 'active')
            ->with(['tenant', 'unit.property', 'landlord'])
            ->get();

        if ($assignments->isEmpty()) {
            $this->info('No active tenant assignments found.');
            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;
        $hadFailure = false;

        foreach ($assignments as $assignment) {
            // Skip if bill already exists for this period
            $existingBill = Bill::where('tenant_assignment_id', $assignment->id)
                ->where('type', $type)
                ->where('billing_period_start', $billingPeriodStart->toDateString())
                ->where('billing_period_end', $billingPeriodEnd->toDateString())
                ->exists();

            if ($existingBill) {
                $skipped++;
                $this->line("  Skipped: {$assignment->tenant?->name} (Unit {$assignment->unit?->unit_number}) - Bill already exists");
                continue;
            }

            $amount = $assignment->rent_amount ?? $assignment->unit?->rent_amount ?? 0;

            if ($amount <= 0) {
                $skipped++;
                $this->line("  Skipped: {$assignment->tenant?->name} - No rent amount set");
                continue;
            }

            if ($dryRun) {
                $this->line("  [DRY RUN] Would create: {$assignment->tenant?->name} - ₱" . number_format($amount, 2));
                $created++;
                continue;
            }

            $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
            while (Bill::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
            }

            try {
                $bill = Bill::create([
                    'landlord_id' => $assignment->landlord_id,
                    'tenant_id' => $assignment->tenant_id,
                    'tenant_assignment_id' => $assignment->id,
                    'unit_id' => $assignment->unit_id,
                    'invoice_number' => $invoiceNumber,
                    'type' => $type,
                    'description' => ucfirst($type) . ' for ' . $billingPeriodStart->format('F Y') . ' - Unit ' . ($assignment->unit?->unit_number ?? 'N/A'),
                    'billing_period_start' => $billingPeriodStart,
                    'billing_period_end' => $billingPeriodEnd,
                    'amount' => $amount,
                    'amount_paid' => 0,
                    'balance' => $amount,
                    'status' => 'unpaid',
                    'due_date' => $dueDate,
                    'currency' => 'PHP',
                ]);

                // Notify tenant
                if ($assignment->tenant) {
                    $assignment->tenant->notify(new BillCreated($bill));
                }

                $created++;
                $this->line("  Created: {$assignment->tenant?->name} - {$invoiceNumber} - ₱" . number_format($amount, 2));
            } catch (\Exception $exception) {
                $hadFailure = true;
                Log::error('Failed to generate recurring bill', [
                    'assignment_id' => $assignment->id,
                    'error' => $exception->getMessage(),
                ]);
                $this->error("  Failed: {$assignment->tenant?->name} - {$exception->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Done! Created: {$created}, Skipped: {$skipped}");

        return $hadFailure ? self::FAILURE : self::SUCCESS;
    }
}
