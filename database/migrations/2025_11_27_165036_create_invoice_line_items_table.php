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
        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            // Link the service (therapy session) that created this charge
            $table->foreignId('therapy_session_id')->nullable()->constrained('therapy_sessions')->nullOnDelete();

            // Link the code used for billing
            $table->foreignId('billing_code_id')->constrained('billing_codes')->restrictOnDelete();

            $table->string('service_description'); // e.g., "Therapy Session 90837"
            $table->decimal('unit_price', 10, 2); // Rate applied (may differ from standard_rate due to contracts)
            $table->unsignedSmallInteger('units')->default(1); // Usually 1 session
            $table->decimal('subtotal', 10, 2); // unit_price * units

            $table->json('metadata')->nullable(); // Useful for storing insurance claim ID associated with this line
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_line_items');
    }
};
