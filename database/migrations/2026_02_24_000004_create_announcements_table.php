<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('type')->default('general'); // general, maintenance, emergency, event
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('audience')->default('all_tenants'); // all_tenants, property_tenants, all_staff, everyone
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'property_id']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
