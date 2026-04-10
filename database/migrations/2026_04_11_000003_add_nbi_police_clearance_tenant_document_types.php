<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add NBI and police clearance to tenant_documents.document_type (MySQL ENUM).
     * SQLite stores enums as unconstrained strings; new values work without ALTER.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE tenant_documents MODIFY COLUMN document_type ENUM(
            'government_id',
            'proof_of_income',
            'employment_contract',
            'bank_statement',
            'character_reference',
            'rental_history',
            'other',
            'nbi_clearance',
            'police_clearance'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE tenant_documents MODIFY COLUMN document_type ENUM(
            'government_id',
            'proof_of_income',
            'employment_contract',
            'bank_statement',
            'character_reference',
            'rental_history',
            'other'
        ) NOT NULL");
    }
};
