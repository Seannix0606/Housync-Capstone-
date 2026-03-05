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
        Schema::create('staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->string('staff_type'); // maintenance_worker, plumber, electrician, cleaner, etc.
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('assignment_start_date');
            $table->date('assignment_end_date')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->string('generated_password')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['unit_id', 'status']);
            $table->index(['staff_id', 'status']);
            $table->index(['landlord_id', 'status']);
            $table->index('staff_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_assignments');
    }
}; 