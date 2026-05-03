<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Replace nullOnDelete with cascadeOnDelete so deleting a unit does not leave
     * bookings with a null unit_id (inconsistent with unit-centric booking rules).
     */
    public function up(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        DB::table('bookings')->whereNull('unit_id')->delete();

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->nullOnDelete();
        });
    }
};
