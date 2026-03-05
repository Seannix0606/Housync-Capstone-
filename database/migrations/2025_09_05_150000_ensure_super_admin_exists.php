<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure a super admin account always exists (idempotent)
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@housesync.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not delete the super admin on rollback to avoid accidental lockout
        // If you need to remove it explicitly, uncomment the line below.
        // DB::table('users')->where('email', 'admin@housesync.com')->delete();
    }
};


