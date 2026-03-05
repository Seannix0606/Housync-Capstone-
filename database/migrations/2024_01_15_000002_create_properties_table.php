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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->enum('type', ['apartment', 'house', 'condo', 'studio'])->default('apartment');
            $table->decimal('price', 10, 2);
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->decimal('area', 8, 2)->nullable(); // in square meters
            $table->string('image_path')->nullable();
            $table->enum('availability_status', ['available', 'occupied'])->default('available');
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->foreignId('landlord_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

