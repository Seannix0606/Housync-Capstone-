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
        // Add a partial unique index to ensure only one active assignment per card
        // This uses raw SQL because Laravel doesn't support partial unique indexes directly
        
        if (DB::getDriverName() === 'mysql') {
            // For MySQL, we'll use a trigger or application logic since partial indexes aren't fully supported
            // For now, we'll rely on application logic in the TenantRfidAssignment model
        } elseif (DB::getDriverName() === 'pgsql') {
            // PostgreSQL supports partial unique indexes
            DB::statement('CREATE UNIQUE INDEX CONCURRENTLY unique_active_rfid_assignment ON tenant_rfid_assignments (rfid_card_id) WHERE status = \'active\'');
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite supports partial unique indexes
            DB::statement('CREATE UNIQUE INDEX unique_active_rfid_assignment ON tenant_rfid_assignments (rfid_card_id) WHERE status = \'active\'');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // No index to drop for MySQL
        } else {
            DB::statement('DROP INDEX IF EXISTS unique_active_rfid_assignment');
        }
    }
};
