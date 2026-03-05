<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->string('type')->default('comment'); // comment, status_change, staff_assigned
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('maintenance_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_comments');
    }
};
