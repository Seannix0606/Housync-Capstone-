<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_requests', 'ticket_number')) {
                $table->string('ticket_number')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('maintenance_requests', 'rating')) {
                $table->tinyInteger('rating')->nullable()->after('completed_date');
            }
            if (!Schema::hasColumn('maintenance_requests', 'rating_feedback')) {
                $table->text('rating_feedback')->nullable()->after('rating');
            }
            if (!Schema::hasColumn('maintenance_requests', 'property_id')) {
                $table->foreignId('property_id')->nullable()->after('unit_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'proof_image')) {
                $table->string('proof_image')->nullable()->after('proof_path');
            }
            if (!Schema::hasColumn('payments', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['ticket_number', 'rating', 'rating_feedback', 'property_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['proof_image', 'verified_by', 'verified_at']);
        });
    }
};
