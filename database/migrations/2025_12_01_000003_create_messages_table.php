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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->onDelete('set null');
            $table->json('metadata')->nullable(); // For additional data like file info, system message data
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for faster queries
            $table->index(['conversation_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};



