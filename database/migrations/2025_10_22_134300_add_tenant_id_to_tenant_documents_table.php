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
        // Only proceed if tenant_documents table exists
        if (!Schema::hasTable('tenant_documents')) {
            return;
        }
        
        // Add tenant_id column if it doesn't exist
        if (!Schema::hasColumn('tenant_documents', 'tenant_id')) {
            Schema::table('tenant_documents', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->nullable()->constrained('users')->onDelete('cascade');
                $table->index('tenant_id');
            });
        }
        
        // Make tenant_assignment_id nullable only if it exists
        if (Schema::hasColumn('tenant_documents', 'tenant_assignment_id')) {
            Schema::table('tenant_documents', function (Blueprint $table) {
                $table->foreignId('tenant_assignment_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_documents', function (Blueprint $table) {
            // Check if tenant_id column exists before dropping
            if (Schema::hasColumn('tenant_documents', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
            
            // Make tenant_assignment_id non-nullable again
            $table->foreignId('tenant_assignment_id')->nullable(false)->change();
        });
    }
};
