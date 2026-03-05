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
        Schema::create('tenant_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('lease_start_date');
            $table->date('lease_end_date');
            $table->decimal('rent_amount', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->enum('status', ['pending', 'active', 'terminated'])->default('pending');
            $table->text('notes')->nullable();
            $table->boolean('documents_uploaded')->default(false);
            $table->boolean('documents_verified')->default(false);
            $table->text('verification_notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['unit_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['landlord_id', 'status']);
            $table->index('documents_uploaded');
            $table->index('documents_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_assignments');
    }
};
