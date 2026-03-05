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
        Schema::create('tenant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_assignment_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', [
                'government_id',
                'proof_of_income',
                'employment_contract',
                'bank_statement',
                'character_reference',
                'rental_history',
                'other'
            ]);
            $table->string('file_name');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['tenant_assignment_id', 'document_type']);
            $table->index('verification_status');
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_documents');
    }
};
