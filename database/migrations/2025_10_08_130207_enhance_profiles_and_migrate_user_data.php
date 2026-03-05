<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add missing fields to landlord_profiles
        Schema::table('landlord_profiles', function (Blueprint $table) {
            $table->string('name')->after('user_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'active'])->default('pending')->after('business_info');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('approved_by');
        });

        // Step 2: Add missing fields to tenant_profiles  
        Schema::table('tenant_profiles', function (Blueprint $table) {
            $table->string('name')->after('user_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->after('emergency_contact_phone');
        });

        // Step 3: Add missing fields to staff_profiles
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('name')->after('user_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->after('license_number');
        });

        // Step 4: Add missing fields to super_admin_profiles
        Schema::table('super_admin_profiles', function (Blueprint $table) {
            $table->string('name')->after('user_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->after('notes');
        });

        // Step 5: Migrate existing user data to profiles
        DB::transaction(function () {
            // Migrate landlords
            $landlords = DB::table('users')->where('role', 'landlord')->get();
            foreach ($landlords as $user) {
                DB::table('landlord_profiles')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'business_info' => $user->business_info,
                        'status' => $user->status,
                        'approved_at' => $user->approved_at,
                        'approved_by' => $user->approved_by,
                        'rejection_reason' => $user->rejection_reason,
                        'created_at' => $user->created_at ?? now(),
                        'updated_at' => $user->updated_at ?? now(),
                    ]
                );
            }

            // Migrate tenants
            $tenants = DB::table('users')->where('role', 'tenant')->get();
            foreach ($tenants as $user) {
                DB::table('tenant_profiles')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'status' => $user->status ?? 'active',
                        'created_at' => $user->created_at ?? now(),
                        'updated_at' => $user->updated_at ?? now(),
                    ]
                );
            }

            // Migrate staff
            $staff = DB::table('users')->where('role', 'staff')->get();
            foreach ($staff as $user) {
                DB::table('staff_profiles')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'staff_type' => $user->staff_type,
                        'status' => $user->status ?? 'active',
                        'created_at' => $user->created_at ?? now(),
                        'updated_at' => $user->updated_at ?? now(),
                    ]
                );
            }

            // Migrate super admins
            $superAdmins = DB::table('users')->where('role', 'super_admin')->get();
            foreach ($superAdmins as $user) {
                DB::table('super_admin_profiles')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'status' => $user->status ?? 'active',
                        'created_at' => $user->created_at ?? now(),
                        'updated_at' => $user->updated_at ?? now(),
                    ]
                );
            }
        });

        // Step 6: Remove redundant fields from users table (keep only auth essentials)
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['approved_by']);
            
            // Drop columns
            $table->dropColumn([
                'name',  // Now in profiles
                'phone',  // Now in profiles
                'address',  // Now in profiles
                'business_info',  // Now in landlord_profiles
                'status',  // Now in profiles
                'approved_at',  // Now in landlord_profiles
                'approved_by',  // Now in landlord_profiles
                'rejection_reason',  // Now in landlord_profiles
                'staff_type',  // Now in staff_profiles
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore users table fields
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('phone')->nullable()->after('password');
            $table->text('address')->nullable()->after('phone');
            $table->text('business_info')->nullable()->after('address');
            $table->enum('status', ['pending', 'approved', 'rejected', 'active'])->default('active')->after('business_info');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('approved_by');
            $table->string('staff_type')->nullable()->after('role');
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // Migrate data back to users table
        DB::transaction(function () {
            // Restore from landlord_profiles
            $landlordProfiles = DB::table('landlord_profiles')->get();
            foreach ($landlordProfiles as $profile) {
                DB::table('users')->where('id', $profile->user_id)->update([
                    'name' => $profile->name,
                    'phone' => $profile->phone,
                    'address' => $profile->address,
                    'business_info' => $profile->business_info,
                    'status' => $profile->status,
                    'approved_at' => $profile->approved_at,
                    'approved_by' => $profile->approved_by,
                    'rejection_reason' => $profile->rejection_reason,
                ]);
            }

            // Restore from tenant_profiles
            $tenantProfiles = DB::table('tenant_profiles')->get();
            foreach ($tenantProfiles as $profile) {
                DB::table('users')->where('id', $profile->user_id)->update([
                    'name' => $profile->name,
                    'phone' => $profile->phone,
                    'address' => $profile->address,
                    'status' => $profile->status,
                ]);
            }

            // Restore from staff_profiles
            $staffProfiles = DB::table('staff_profiles')->get();
            foreach ($staffProfiles as $profile) {
                DB::table('users')->where('id', $profile->user_id)->update([
                    'name' => $profile->name,
                    'phone' => $profile->phone,
                    'address' => $profile->address,
                    'staff_type' => $profile->staff_type,
                    'status' => $profile->status,
                ]);
            }

            // Restore from super_admin_profiles
            $superAdminProfiles = DB::table('super_admin_profiles')->get();
            foreach ($superAdminProfiles as $profile) {
                DB::table('users')->where('id', $profile->user_id)->update([
                    'name' => $profile->name,
                    'phone' => $profile->phone,
                    'address' => $profile->address,
                    'status' => $profile->status,
                ]);
            }
        });

        // Remove added fields from profiles
        Schema::table('landlord_profiles', function (Blueprint $table) {
            $table->dropColumn(['name', 'status', 'approved_at', 'approved_by', 'rejection_reason']);
        });

        Schema::table('tenant_profiles', function (Blueprint $table) {
            $table->dropColumn(['name', 'status']);
        });

        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropColumn(['name', 'status']);
        });

        Schema::table('super_admin_profiles', function (Blueprint $table) {
            $table->dropColumn(['name', 'status']);
        });
    }
};
