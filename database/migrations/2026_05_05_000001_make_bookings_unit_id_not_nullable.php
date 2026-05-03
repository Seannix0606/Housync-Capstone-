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
        if (! Schema::hasTable('bookings')) {
            return;
        }

        DB::table('bookings')->whereNull('unit_id')->delete();

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();
            Schema::rename('bookings', 'bookings_unit_id_not_null_legacy');
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
                $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
                $table->timestamps();
            });
            DB::statement(
                'INSERT INTO bookings (id, property_id, unit_id, created_at, updated_at) '
                .'SELECT id, property_id, unit_id, created_at, updated_at FROM bookings_unit_id_not_null_legacy '
                .'WHERE unit_id IS NOT NULL'
            );
            Schema::drop('bookings_unit_id_not_null_legacy');
            Schema::enableForeignKeyConstraints();

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE bookings MODIFY unit_id BIGINT UNSIGNED NOT NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bookings ALTER COLUMN unit_id SET NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE bookings MODIFY unit_id BIGINT UNSIGNED NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bookings ALTER COLUMN unit_id DROP NOT NULL');
        }
    }
};
