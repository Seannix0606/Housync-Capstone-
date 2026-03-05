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
        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_uid')->unique();
            $table->unsignedBigInteger('tenant_assignment_id')->nullable();
            $table->unsignedBigInteger('landlord_id');
            $table->unsignedBigInteger('apartment_id');
            $table->string('card_name')->nullable(); // Optional name for the card
            $table->enum('status', ['active', 'inactive', 'lost', 'stolen'])->default('active');
            $table->datetime('issued_at');
            $table->datetime('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_assignment_id')->references('id')->on('tenant_assignments')->onDelete('set null');
            $table->foreign('landlord_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
            
            $table->index(['card_uid', 'status']);
            $table->index(['tenant_assignment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_cards');
    }
};