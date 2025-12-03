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
            // 'appointment' = Time specific (e.g., 10:30 AM)
            // 'queue' = Date specific (e.g., Today), order by arrival
            $table->enum('type', config('constants.SESSION_TYPE'))->default('appointment')->after('status');
            // Exact timestamp when they arrived at the clinic
            $table->timestamp('checked_in_at')->nullable()->after('actual_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapy_sessions', function (Blueprint $table) {
            //
        });
    }
};
