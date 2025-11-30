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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Sequential number for legal/auditing
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // User who created the invoice

            $table->decimal('total_amount', 10, 2)->default(0); // Sum of all line items
            $table->decimal('amount_due', 10, 2)->default(0); // Remaining balance

            $table->date('issued_date');
            $table->date('due_date');

            $table->enum('status', ['Draft', 'Sent', 'Paid', 'Partially Paid', 'Canceled'])->default('Draft');

            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
