<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN or ENUM
            // The status column is already TEXT in SQLite, so we just need to ensure
            // the application logic handles 'completed' status
            // No schema change needed for SQLite
        } else {
            // For MySQL/MariaDB, use raw SQL to modify the enum
            DB::statement("ALTER TABLE staff_assignments MODIFY COLUMN status ENUM('active', 'inactive', 'terminated', 'completed') NOT NULL DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // No schema change needed for SQLite
        } else {
            // Revert back to original enum values for MySQL/MariaDB
            DB::statement("ALTER TABLE staff_assignments MODIFY COLUMN status ENUM('active', 'inactive', 'terminated') NOT NULL DEFAULT 'active'");
        }
    }
};
