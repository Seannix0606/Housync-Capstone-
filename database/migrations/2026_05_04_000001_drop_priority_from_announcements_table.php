<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('announcements', 'priority')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('announcements', 'priority')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('priority')->default('normal')->after('type');
            });
        }
    }
};
