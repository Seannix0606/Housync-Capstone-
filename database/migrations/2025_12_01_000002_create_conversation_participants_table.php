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
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'participant', 'staff'])->default('participant');
            $table->timestamp('last_read_at')->nullable();
            $table->integer('unread_count')->default(0);
            $table->boolean('is_muted')->default(false);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate participants
            $table->unique(['conversation_id', 'user_id']);
            $table->index(['user_id', 'unread_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};



