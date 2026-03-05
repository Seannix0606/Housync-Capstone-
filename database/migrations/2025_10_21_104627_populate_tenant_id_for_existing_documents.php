<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Populate tenant_id for all existing documents based on their assignment
     */
    public function up(): void
    {
        // Get all documents that have tenant_assignment_id but no tenant_id
        $documents = DB::table('tenant_documents')
            ->whereNotNull('tenant_assignment_id')
            ->whereNull('tenant_id')
            ->get();

        foreach ($documents as $document) {
            // Get the assignment to find the tenant_id
            $assignment = DB::table('tenant_assignments')
                ->where('id', $document->tenant_assignment_id)
                ->first();

            if ($assignment) {
                // Update the document with the tenant_id
                DB::table('tenant_documents')
                    ->where('id', $document->id)
                    ->update([
                        'tenant_id' => $assignment->tenant_id,
                        'updated_at' => now()
                    ]);
            }
        }

        // Log the migration results
        $updatedCount = DB::table('tenant_documents')
            ->whereNotNull('tenant_id')
            ->whereNotNull('tenant_assignment_id')
            ->count();

        \Log::info('Populated tenant_id for existing documents', [
            'documents_updated' => $updatedCount,
            'timestamp' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you can clear tenant_id for documents that have tenant_assignment_id
        // This is a destructive operation, so be cautious
        DB::table('tenant_documents')
            ->whereNotNull('tenant_assignment_id')
            ->update(['tenant_id' => null]);
    }
};
