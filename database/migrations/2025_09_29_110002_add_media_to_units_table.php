<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('units', 'gallery')) {
                $table->json('gallery')->nullable()->after('cover_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'gallery')) {
                $table->dropColumn('gallery');
            }
            if (Schema::hasColumn('units', 'cover_image')) {
                $table->dropColumn('cover_image');
            }
        });
    }
};


