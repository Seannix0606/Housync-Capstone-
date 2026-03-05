<?php

namespace Database\Seeders;

use App\Models\TenantAssignment;
use App\Models\TenantDocument;
use Illuminate\Database\Seeder;

class TenantDocumentSeeder extends Seeder
{
    public function run()
    {
        // Get active tenant assignments
        $assignments = TenantAssignment::where('documents_uploaded', true)->get();

        foreach ($assignments as $assignment) {
            // Create sample documents for each assignment
            $documentTypes = [
                'government_id' => 'Government ID',
                'proof_of_income' => 'Proof of Income',
                'bank_statement' => 'Bank Statement',
                'character_reference' => 'Character Reference',
            ];

            foreach ($documentTypes as $type => $label) {
                TenantDocument::updateOrCreate(
                    [
                        'tenant_id' => $assignment->tenant_id,
                        'document_type' => $type,
                    ],
                    [
                        'file_name' => $label . '.pdf',
                        'file_path' => 'tenant-documents/sample_' . $type . '.pdf',
                        'file_size' => rand(100000, 500000), // Random file size between 100KB-500KB
                        'mime_type' => 'application/pdf',
                        'verification_status' => 'verified',
                        'verified_by' => $assignment->landlord_id,
                        'verified_at' => now()->subDays(rand(1, 30)),
                        'verification_notes' => 'Document verified successfully',
                    ]
                );
            }
        }

        // Create some pending documents for testing
        $pendingAssignment = TenantAssignment::where('documents_uploaded', false)->first();
        if ($pendingAssignment) {
            TenantDocument::updateOrCreate(
                [
                    'tenant_id' => $pendingAssignment->tenant_id,
                    'document_type' => 'government_id',
                ],
                [
                    'file_name' => 'Government ID.pdf',
                    'file_path' => 'tenant-documents/pending_gov_id.pdf',
                    'file_size' => 250000,
                    'mime_type' => 'application/pdf',
                    'verification_status' => 'pending',
                ]
            );
        }
    }
} 