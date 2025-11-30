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
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('icd_code_id')->nullable()->constrained('icd_codes');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->date('start_date')->nullable(); // Approximate date the condition began
            $table->enum('type', config('constants.DIAGNOSIS_TYPE'))->nullable();
            // STATUS (Relating to the condition itself)
            $table->enum('condition_status', config('constants.DIAGNOSIS_CONDITION_STATUS'))->default('Active');
            $table->timestamps();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // referring_physician
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
