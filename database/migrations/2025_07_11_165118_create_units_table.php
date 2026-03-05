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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number')->unique();
            $table->string('owner_name');
            $table->string('unit_type'); // Studio, 1 Bedroom, 2 Bedroom, etc.
            $table->decimal('rent_amount', 10, 2);
            $table->enum('status', ['occupied', 'available', 'maintenance'])->default('available');
            $table->enum('leasing_type', ['separate', 'inclusive'])->default('separate'); // Separate bills vs inclusive
            $table->integer('tenant_count')->default(0);
            $table->text('description')->nullable();
            $table->decimal('floor_area', 8, 2)->nullable(); // in square meters
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(1);
            $table->boolean('is_furnished')->default(false);
            $table->json('amenities')->nullable(); // AC, WiFi, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
