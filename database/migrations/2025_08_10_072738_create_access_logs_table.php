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
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('card_uid');
            $table->unsignedBigInteger('rfid_card_id')->nullable(); // null if card not recognized
            $table->unsignedBigInteger('tenant_assignment_id')->nullable();
            $table->unsignedBigInteger('apartment_id')->nullable();
            $table->enum('access_result', ['granted', 'denied'])->default('denied');
            $table->enum('denial_reason', [
                'card_not_found', 
                'card_inactive', 
                'card_expired', 
                'tenant_inactive',
                'outside_access_hours',
                'card_stolen',
                'card_lost'
            ])->nullable();
            $table->datetime('access_time');
            $table->string('reader_location')->default('main_entrance'); // Future expansion for multiple readers
            $table->json('raw_data')->nullable(); // Store the raw ESP32 data
            $table->timestamps();
            
            $table->foreign('rfid_card_id')->references('id')->on('rfid_cards')->onDelete('set null');
            $table->foreign('tenant_assignment_id')->references('id')->on('tenant_assignments')->onDelete('set null');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('set null');
            
            $table->index(['card_uid', 'access_time']);
            $table->index(['access_result', 'access_time']);
            $table->index(['apartment_id', 'access_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};