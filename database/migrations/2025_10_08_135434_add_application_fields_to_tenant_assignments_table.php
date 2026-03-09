<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        // Check if columns already exist before adding
        $columns = Schema::getColumnListing('tenant_assignments');

        Schema::table('tenant_assignments', function (Blueprint $table) use ($columns) {
            // Add new columns for tenant applications only if they don't exist
            if (! in_array('occupation', $columns)) {
                $table->string('occupation')->nullable()->after('notes');
            }
            if (! in_array('monthly_income', $columns)) {
                $table->decimal('monthly_income', 10, 2)->nullable()->after('occupation');
            }
        });

        if ($driver !== 'sqlite') {
            // Modify status enum to include 'pending_approval' (MySQL/MariaDB only)
            // Note: Laravel doesn't support modifying enums directly, so we use raw SQL
            DB::statement("ALTER TABLE tenant_assignments MODIFY COLUMN status ENUM('pending', 'active', 'terminated', 'pending_approval') DEFAULT 'pending'");

            // Make lease dates nullable for applications (MySQL/MariaDB)
            Schema::table('tenant_assignments', function (Blueprint $table) {
                $table->date('lease_start_date')->nullable()->change();
                $table->date('lease_end_date')->nullable()->change();
            });
        }
        // For SQLite: status is TEXT, so it already supports any value including 'pending_approval'
        // Lease dates are already nullable in SQLite schema, so no changes needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        Schema::table('tenant_assignments', function (Blueprint $table) {
            $table->dropColumn(['occupation', 'monthly_income']);
        });

        if ($driver !== 'sqlite') {
            // Revert status enum (MySQL/MariaDB only)
            DB::statement("ALTER TABLE tenant_assignments MODIFY COLUMN status ENUM('pending', 'active', 'terminated') DEFAULT 'pending'");

            // Revert lease dates back to not nullable (MySQL/MariaDB)
            Schema::table('tenant_assignments', function (Blueprint $table) {
                $table->date('lease_start_date')->nullable(false)->change();
                $table->date('lease_end_date')->nullable(false)->change();
            });
        }
    }
};
