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
        Schema::create('billing_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g., CPT Code 90837 for a 60-min session
            $table->string('name');
            $table->decimal('standard_rate', 10, 2); // The default price for this service
            $table->unsignedSmallInteger('min_duration_minutes')->nullable(); // Min time for code eligibility
            $table->unsignedSmallInteger('max_duration_minutes')->nullable(); // Max time for code eligibility
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_codes');
    }
};
