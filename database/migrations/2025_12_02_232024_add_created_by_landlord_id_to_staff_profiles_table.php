<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('staff_profiles', 'created_by_landlord_id')) {
            Schema::table('staff_profiles', function (Blueprint $table) {
                $table->foreignId('created_by_landlord_id')
                      ->nullable()
                      ->after('user_id')
                      ->constrained('users')
                      ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropForeign(['created_by_landlord_id']);
            $table->dropColumn('created_by_landlord_id');
        });
    }
};
