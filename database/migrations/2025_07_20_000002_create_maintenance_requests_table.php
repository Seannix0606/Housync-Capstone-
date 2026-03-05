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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('category', ['plumbing', 'electrical', 'hvac', 'appliance', 'structural', 'cleaning', 'other'])->default('other');
            $table->date('requested_date');
            $table->date('completed_date')->nullable();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('staff_notes')->nullable();
            $table->text('tenant_notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['unit_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['landlord_id', 'status']);
            $table->index(['assigned_staff_id', 'status']);
            $table->index('priority');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
}; 