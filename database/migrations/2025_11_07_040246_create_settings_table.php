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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('group')->default('general'); // general, email, security, features, payment, notifications, system
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            // General Settings
            ['key' => 'site_name', 'value' => 'Housync', 'type' => 'string', 'group' => 'general', 'description' => 'Application name', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_email', 'value' => 'admin@housync.com', 'type' => 'string', 'group' => 'general', 'description' => 'Default system email', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_phone', 'value' => '', 'type' => 'string', 'group' => 'general', 'description' => 'Contact phone number', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'timezone', 'value' => 'Asia/Manila', 'type' => 'string', 'group' => 'general', 'description' => 'Application timezone', 'created_at' => now(), 'updated_at' => now()],
            
            // Email Settings
            ['key' => 'mail_from_name', 'value' => 'Housync', 'type' => 'string', 'group' => 'email', 'description' => 'Email sender name', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'mail_from_address', 'value' => 'noreply@housync.com', 'type' => 'string', 'group' => 'email', 'description' => 'Email sender address', 'created_at' => now(), 'updated_at' => now()],
            
            // Security Settings
            ['key' => 'password_min_length', 'value' => '8', 'type' => 'integer', 'group' => 'security', 'description' => 'Minimum password length', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'session_lifetime', 'value' => '120', 'type' => 'integer', 'group' => 'security', 'description' => 'Session lifetime in minutes', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'require_email_verification', 'value' => 'false', 'type' => 'boolean', 'group' => 'security', 'description' => 'Require email verification for new users', 'created_at' => now(), 'updated_at' => now()],
            
            // Feature Settings
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable maintenance mode', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'allow_registration', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow new user registration', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'enable_rfid', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable RFID card functionality', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'dark_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable dark mode theme', 'created_at' => now(), 'updated_at' => now()],
            
            // Notification Settings
            ['key' => 'notify_new_landlord', 'value' => 'true', 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Notify admin when new landlord registers', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'notify_landlord_approval', 'value' => 'true', 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Notify landlord when approved', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
