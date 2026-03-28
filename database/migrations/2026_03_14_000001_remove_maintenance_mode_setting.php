<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->where('key', 'maintenance_mode')->delete();
        Setting::clearCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->insert([
            'key' => 'maintenance_mode',
            'value' => 'false',
            'type' => 'boolean',
            'group' => 'features',
            'description' => 'Enable maintenance mode',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
