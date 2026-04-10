<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQLite stores Laravel enums with a CHECK constraint; MySQL-only ENUM
     * updates do not run on SQLite, so newer document_type values fail tests.
     * Use a plain string column on SQLite only (MySQL keeps ENUM via other migrations).
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::rename('landlord_documents', 'landlord_documents_legacy');

        $legacyIndexes = DB::select("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'landlord_documents_legacy'");
        foreach ($legacyIndexes as $index) {
            if (! str_starts_with((string) $index->name, 'sqlite_')) {
                DB::statement('DROP INDEX IF EXISTS "'.str_replace('"', '""', $index->name).'"');
            }
        }

        Schema::create('landlord_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->string('document_type');
            $table->string('file_name');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('verification_status')->default('pending');
            $table->text('verification_notes')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->index(['landlord_id', 'document_type']);
            $table->index('verification_status');
            $table->index('expiry_date');
        });

        $columns = [
            'id', 'landlord_id', 'document_type', 'file_name', 'file_path', 'file_size', 'mime_type',
            'uploaded_at', 'verified_at', 'verified_by', 'verification_status', 'verification_notes',
            'expiry_date', 'created_at', 'updated_at',
        ];

        $list = implode(', ', $columns);
        DB::statement("INSERT INTO landlord_documents ({$list}) SELECT {$list} FROM landlord_documents_legacy");

        Schema::drop('landlord_documents_legacy');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::rename('landlord_documents', 'landlord_documents_legacy');

        $legacyIndexes = DB::select("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'landlord_documents_legacy'");
        foreach ($legacyIndexes as $index) {
            if (! str_starts_with((string) $index->name, 'sqlite_')) {
                DB::statement('DROP INDEX IF EXISTS "'.str_replace('"', '""', $index->name).'"');
            }
        }

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

        $columns = [
            'id', 'landlord_id', 'document_type', 'file_name', 'file_path', 'file_size', 'mime_type',
            'uploaded_at', 'verified_at', 'verified_by', 'verification_status', 'verification_notes',
            'expiry_date', 'created_at', 'updated_at',
        ];

        $list = implode(', ', $columns);
        DB::statement("INSERT INTO landlord_documents ({$list}) SELECT {$list} FROM landlord_documents_legacy");

        Schema::drop('landlord_documents_legacy');

        Schema::enableForeignKeyConstraints();
    }
};
