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
        Schema::create('tenant_rfid_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfid_card_id');
            $table->unsignedBigInteger('tenant_assignment_id');
            $table->datetime('assigned_at');
            $table->datetime('expires_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'revoked'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('rfid_card_id')->references('id')->on('rfid_cards')->onDelete('cascade');
            $table->foreign('tenant_assignment_id')->references('id')->on('tenant_assignments')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['rfid_card_id', 'status']);
            $table->index(['tenant_assignment_id', 'status']);
            $table->index(['status', 'assigned_at']);
            
            // Note: We'll use application logic to ensure only one active assignment per card
            // A database constraint for this would be complex since we only want to restrict 'active' status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_rfid_assignments');
    }
};
