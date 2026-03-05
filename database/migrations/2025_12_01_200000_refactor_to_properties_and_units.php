<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * This migration consolidates the database schema:
 * 
 * BEFORE:
 * - properties (public listings for explore - created by seeders)
 * - apartments (landlord buildings)
 * - units (rentable spaces with apartment_id)
 * 
 * AFTER:
 * - properties (landlord buildings - formerly apartments)
 * - units (rentable spaces with property_id)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop the old properties table (seeder-created listings)
        // We'll recreate listings from units on the explore page
        Schema::dropIfExists('property_amenity'); // Drop pivot table first
        Schema::dropIfExists('properties');
        
        // Step 2: Rename apartments to properties
        Schema::rename('apartments', 'properties');
        
        // Step 3: Add any missing fields to the new properties table
        Schema::table('properties', function (Blueprint $table) {
            // Add slug for URL-friendly names
            if (!Schema::hasColumn('properties', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            
            // Add is_active flag
            if (!Schema::hasColumn('properties', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });
        
        // Step 4: Update units table - rename apartment_id to property_id
        Schema::table('units', function (Blueprint $table) {
            // Drop old foreign key if exists
            try {
                $table->dropForeign(['apartment_id']);
            } catch (\Exception $e) {
                // FK might not exist, ignore
            }
        });
        
        // Rename the column
        if (Schema::hasColumn('units', 'apartment_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->renameColumn('apartment_id', 'property_id');
            });
        }
        
        // Add new foreign key
        Schema::table('units', function (Blueprint $table) {
            $table->foreign('property_id')
                  ->references('id')
                  ->on('properties')
                  ->onDelete('cascade');
        });
        
        // Step 5: Update related tables that reference apartments
        // tenant_assignments - check if it has apartment_id
        if (Schema::hasColumn('tenant_assignments', 'apartment_id')) {
            Schema::table('tenant_assignments', function (Blueprint $table) {
                $table->dropForeign(['apartment_id']);
                $table->renameColumn('apartment_id', 'property_id');
            });
        }
        
        // staff_assignments - check if it has apartment_id
        if (Schema::hasColumn('staff_assignments', 'apartment_id')) {
            Schema::table('staff_assignments', function (Blueprint $table) {
                try {
                    $table->dropForeign(['apartment_id']);
                } catch (\Exception $e) {}
                $table->renameColumn('apartment_id', 'property_id');
            });
        }
        
        // rfid_cards - check if it has apartment_id
        if (Schema::hasColumn('rfid_cards', 'apartment_id')) {
            Schema::table('rfid_cards', function (Blueprint $table) {
                try {
                    $table->dropForeign(['apartment_id']);
                } catch (\Exception $e) {}
                $table->renameColumn('apartment_id', 'property_id');
            });
        }
        
        // access_logs - check if it has apartment_id
        if (Schema::hasColumn('access_logs', 'apartment_id')) {
            Schema::table('access_logs', function (Blueprint $table) {
                try {
                    $table->dropForeign(['apartment_id']);
                } catch (\Exception $e) {}
                $table->renameColumn('apartment_id', 'property_id');
            });
        }
        
        // maintenance_requests - check if it has apartment_id
        if (Schema::hasColumn('maintenance_requests', 'apartment_id')) {
            Schema::table('maintenance_requests', function (Blueprint $table) {
                try {
                    $table->dropForeign(['apartment_id']);
                } catch (\Exception $e) {}
                $table->renameColumn('apartment_id', 'property_id');
            });
        }
        
        // Generate slugs for existing properties
        $properties = DB::table('properties')->get();
        foreach ($properties as $property) {
            $slug = \Illuminate\Support\Str::slug($property->name);
            $count = DB::table('properties')->where('slug', 'LIKE', $slug . '%')->where('id', '!=', $property->id)->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }
            DB::table('properties')->where('id', $property->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        // Reverse the migration
        // Note: This will lose data from the old properties table
        
        // Rename property_id back to apartment_id in units
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->renameColumn('property_id', 'apartment_id');
        });
        
        // Rename properties back to apartments
        Schema::rename('properties', 'apartments');
        
        // Add foreign key back
        Schema::table('units', function (Blueprint $table) {
            $table->foreign('apartment_id')
                  ->references('id')
                  ->on('apartments')
                  ->onDelete('cascade');
        });
        
        // Recreate old properties table structure
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('type')->default('apartment');
            $table->decimal('price', 12, 2);
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->decimal('area', 10, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->string('availability_status')->default('available');
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->foreignId('landlord_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Recreate property_amenity pivot
        Schema::create('property_amenity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('amenity_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }
};



