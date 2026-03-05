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
        Schema::table('units', function (Blueprint $table) {
            // Drop the global unique constraint on unit_number
            $table->dropUnique(['unit_number']);
            
            // Add a composite unique constraint on (apartment_id, unit_number)
            // This allows the same unit number across different properties
            $table->unique(['apartment_id', 'unit_number'], 'units_apartment_unit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('units_apartment_unit_unique');
            
            // Restore the global unique constraint on unit_number
            $table->unique('unit_number');
        });
    }
};