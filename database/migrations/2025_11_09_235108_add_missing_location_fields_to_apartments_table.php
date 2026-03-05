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
        Schema::table('apartments', function (Blueprint $table) {
            // Add property_type if it doesn't exist
            if (!Schema::hasColumn('apartments', 'property_type')) {
                $table->enum('property_type', ['apartment', 'condominium', 'townhouse', 'house', 'duplex', 'others'])->nullable()->after('name');
            }
            // Add location fields
            if (!Schema::hasColumn('apartments', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('apartments', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('apartments', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('state');
            }
            // Add property details
            if (!Schema::hasColumn('apartments', 'year_built')) {
                $table->integer('year_built')->nullable()->after('bedrooms');
            }
            if (!Schema::hasColumn('apartments', 'parking_spaces')) {
                $table->integer('parking_spaces')->nullable()->after('year_built');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            if (Schema::hasColumn('apartments', 'property_type')) {
                $table->dropColumn('property_type');
            }
            if (Schema::hasColumn('apartments', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('apartments', 'state')) {
                $table->dropColumn('state');
            }
            if (Schema::hasColumn('apartments', 'postal_code')) {
                $table->dropColumn('postal_code');
            }
            if (Schema::hasColumn('apartments', 'year_built')) {
                $table->dropColumn('year_built');
            }
            if (Schema::hasColumn('apartments', 'parking_spaces')) {
                $table->dropColumn('parking_spaces');
            }
        });
    }
};
