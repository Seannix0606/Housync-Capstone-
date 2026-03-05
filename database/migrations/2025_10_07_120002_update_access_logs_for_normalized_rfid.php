<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update access logs to use the new normalized structure
        // This migration ensures access logs reference the correct tenant_assignment_id
        // from the new tenant_rfid_assignments table
        
        $this->updateAccessLogsWithNormalizedData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed as this just updates existing data
        // The original data structure is preserved in the access logs
    }
    
    /**
     * Update access logs to reference tenant assignments through the normalized structure
     */
    private function updateAccessLogsWithNormalizedData(): void
    {
        // Update access logs where tenant_assignment_id is null but rfid_card_id exists
        // and there's an active tenant assignment for that card
        DB::statement("
            UPDATE access_logs 
            SET tenant_assignment_id = (
                SELECT tra.tenant_assignment_id 
                FROM tenant_rfid_assignments tra 
                WHERE tra.rfid_card_id = access_logs.rfid_card_id 
                AND tra.status = 'active'
                AND tra.assigned_at <= access_logs.access_time
                AND (tra.expires_at IS NULL OR tra.expires_at >= access_logs.access_time)
                ORDER BY tra.assigned_at DESC
                LIMIT 1
            )
            WHERE access_logs.tenant_assignment_id IS NULL 
            AND access_logs.rfid_card_id IS NOT NULL
        ");
    }
};
