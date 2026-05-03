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
        Schema::table('users', function (Blueprint $table) {
            $table->string('landlord_instapay_quick_response_code_image_path')->nullable();
        });

        DB::table('payments')
            ->whereIn('method', ['bank_transfer', 'gcash', 'other'])
            ->update(['method' => 'instapay']);

        $driverName = Schema::getConnection()->getDriverName();

        if ($driverName === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY `method` VARCHAR(32) NOT NULL DEFAULT 'cash'");
        }

        if ($driverName === 'pgsql') {
            DB::statement('ALTER TABLE payments ALTER COLUMN method TYPE VARCHAR(32)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('landlord_instapay_quick_response_code_image_path');
        });

        // Intentionally not reverting `payments.method`: rows may contain `instapay`, which old ENUMs cannot represent.
    }
};
