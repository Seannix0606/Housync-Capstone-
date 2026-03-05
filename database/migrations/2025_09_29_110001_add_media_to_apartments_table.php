<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            if (!Schema::hasColumn('apartments', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('status');
            }
            if (!Schema::hasColumn('apartments', 'gallery')) {
                $table->json('gallery')->nullable()->after('cover_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            if (Schema::hasColumn('apartments', 'gallery')) {
                $table->dropColumn('gallery');
            }
            if (Schema::hasColumn('apartments', 'cover_image')) {
                $table->dropColumn('cover_image');
            }
        });
    }
};


