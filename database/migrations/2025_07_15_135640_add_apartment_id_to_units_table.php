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
            $table->unsignedBigInteger('apartment_id')->nullable();
            $table->dropColumn('owner_name'); // Remove owner_name as it will be through apartment->landlord
            
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['apartment_id']);
            $table->dropColumn('apartment_id');
            $table->string('owner_name'); // Add back owner_name
        });
    }
};
