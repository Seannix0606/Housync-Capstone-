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
        Schema::table('tenant_documents', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['tenant_assignment_id']);
            
            // Drop index if it exists
            $table->dropIndex(['tenant_assignment_id', 'document_type']);
            
            // Drop the column
            $table->dropColumn('tenant_assignment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_documents', function (Blueprint $table) {
            // Re-add the column (nullable since we removed it)
            $table->foreignId('tenant_assignment_id')->nullable()->after('tenant_id')
                  ->constrained('tenant_assignments')->onDelete('cascade');
            
            // Re-add index
            $table->index(['tenant_assignment_id', 'document_type']);
        });
    }
};
