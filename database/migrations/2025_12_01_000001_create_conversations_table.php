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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('direct'); // direct, group, maintenance_ticket
            $table->string('subject')->nullable(); // For tickets/group chats
            $table->foreignId('apartment_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_request_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'archived', 'resolved'])->default('active');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['type', 'status']);
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};



