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
        Schema::table('therapy_sessions', function (Blueprint $table) {
            // Distinguish between a fixed 'Appointment' and a flexible 'Queue' visit
            $table->enum('session_type', config('constants.SESSION_TYPE'))->default('appointment')->after('status');
            // Exact timestamp for FIFO (First-In-First-Out) sorting in the waiting room
            $table->timestamp('checked_in_at')->nullable()->after('actual_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapy_sessions', function (Blueprint $table) {
            $table->dropColumn(['type', 'checked_in_at']);
        });
    }
};
