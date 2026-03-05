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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'landlord', 'tenant'])->default('tenant');
            $table->enum('status', ['pending', 'approved', 'rejected', 'active'])->default('active');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('business_info')->nullable(); // For landlords
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'role',
                'status', 
                'phone',
                'address',
                'business_info',
                'approved_at',
                'approved_by',
                'rejection_reason'
            ]);
        });
    }
};
