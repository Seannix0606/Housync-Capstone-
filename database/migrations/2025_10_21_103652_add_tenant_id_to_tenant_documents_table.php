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
            // Add tenant_id field to support personal documents not tied to a specific assignment
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            
            // Make tenant_assignment_id nullable so documents can exist without assignment
            $table->foreignId('tenant_assignment_id')->nullable()->change();
            
            // Add index for tenant_id queries
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_documents', function (Blueprint $table) {
            // Remove the tenant_id field
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
            
            // Make tenant_assignment_id required again (cannot actually make it NOT NULL if there's nullable data)
            // This is a destructive change - data with null tenant_assignment_id will cause issues
        });
    }
};
