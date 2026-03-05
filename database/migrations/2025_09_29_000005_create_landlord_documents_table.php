<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->enum('document_type', [
                'business_permit',
                'mayors_permit',
                'bir_certificate',
                'barangay_clearance',
                'lease_contract_sample',
                'valid_id',
                'other',
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

            $table->index(['landlord_id', 'document_type']);
            $table->index('verification_status');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_documents');
    }
};


