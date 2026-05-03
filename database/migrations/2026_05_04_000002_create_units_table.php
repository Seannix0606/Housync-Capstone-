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
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')
                    ->constrained('properties')
                    ->cascadeOnDelete();
                $table->string('name');
                $table->decimal('price', 10, 2)->nullable();
                $table->string('status')->default('available');
                $table->timestamps();
            });

            return;
        }

        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'name')) {
                $table->string('name')->nullable()->after('property_id');
            }
            if (! Schema::hasColumn('units', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        // Legacy schema: only remove columns this migration adds.
        if (Schema::hasColumn('units', 'unit_number')) {
            Schema::table('units', function (Blueprint $table) {
                if (Schema::hasColumn('units', 'price')) {
                    $table->dropColumn('price');
                }
                if (Schema::hasColumn('units', 'name')) {
                    $table->dropColumn('name');
                }
            });

            return;
        }

        Schema::dropIfExists('units');
    }
};
