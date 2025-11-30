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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Link to the user making/recording the payment
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // Link to the invoice this payment is applied against (Crucial change)
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            $table->decimal('amount', 10, 2); // Amount received
            $table->date('payment_date');
            $table->string('payment_method')->nullable(); // e.g., 'Credit Card', 'Cash', 'Insurance', 'Check'
            $table->string('transaction_id')->nullable(); // Stripe/PayPal ID or check number
            $table->text('notes')->nullable();
            $table->enum('status', ['Confirmed', 'Failed', 'Refunded'])->default('Confirmed');

            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
