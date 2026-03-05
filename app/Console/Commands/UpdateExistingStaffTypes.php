<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\StaffAssignment;
use Illuminate\Console\Command;

class UpdateExistingStaffTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staff:update-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing staff members with their staff types from assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating existing staff types...');

        // Get all staff users who don't have a staff_type set
        $staffUsers = User::where('role', 'staff')
            ->whereNull('staff_type')
            ->get();

        if ($staffUsers->isEmpty()) {
            $this->info('No staff members need updating.');
            return;
        }

        foreach ($staffUsers as $staff) {
            // Get their most recent assignment to determine their staff type
            $assignment = StaffAssignment::where('staff_id', $staff->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($assignment) {
                $staff->update(['staff_type' => $assignment->staff_type]);
                $this->info("Updated {$staff->name} to {$assignment->staff_type}");
            } else {
                // If no assignment exists, we can't determine the type
                $this->warn("No assignment found for {$staff->name} - skipping");
            }
        }

        $this->info('Staff types updated successfully!');
    }
}
