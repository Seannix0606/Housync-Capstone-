<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE landlord_documents MODIFY COLUMN document_type ENUM(
            'business_permit',
            'mayors_permit',
            'bir_certificate',
            'barangay_clearance',
            'lease_contract_sample',
            'valid_id',
            'other',
            'fire_safety_certificate',
            'certificate_of_occupancy'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE landlord_documents MODIFY COLUMN document_type ENUM(
            'business_permit',
            'mayors_permit',
            'bir_certificate',
            'barangay_clearance',
            'lease_contract_sample',
            'valid_id',
            'other'
        ) NOT NULL");
    }
};
