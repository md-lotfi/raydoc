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
        Schema::create('therapy_sessions', function (Blueprint $table) {
            $table->id();

            // RELATIONSHIPS
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Therapist/Doctor
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            // SCHEDULING (Use TIMESTAMP for combined date and time)
            $table->timestamp('scheduled_at'); // When the session is supposed to start
            $table->unsignedSmallInteger('duration_minutes'); // Scheduled length

            // ACTUAL SESSION TIME (For precise logging and billing)
            $table->timestamp('actual_start_at')->nullable(); // When the session actually started
            $table->timestamp('actual_end_at')->nullable(); // When the session actually ended

            // CLINICAL DETAILS
            $table->string('focus_area'); // Or foreignId('focus_area_id')
            $table->text('notes')->nullable();
            $table->text('homework_assigned')->nullable();

            // STATUS AND CANCELLATION
            $table->enum('status', config('constants.THERAPY_SESSION_STATUS'));
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable(); // Why it was cancelled

            // BILLING
            $table->enum('billing_status', ['Billed', 'Pending', 'Paid', 'Not Applicable']);

            // METADATA AND TIMESTAMPS
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
