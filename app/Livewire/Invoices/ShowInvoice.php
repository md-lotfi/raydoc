<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ShowInvoice extends Component
{
    public Invoice $invoice;

    public $showPaymentModal = false; // For future payment recording

    // Payment form properties (for future feature)
    public $paymentAmount = 'Cash';

    public $paymentMethod;

    public $paymentDate;

    public $notes;

    public $paymentMethods;

    protected $listeners = ['paymentRecorded' => 'handlePaymentRecorded'];

    public function handlePaymentRecorded()
    {
        $this->showPaymentModal = false; // Close the modal
        $this->invoice->refresh();      // Refresh the invoice model and its relationships
    }

    protected function rules()
    {
        return [
            'paymentAmount' => 'required|numeric|min:0.01|max:'.$this->invoice->amount_due,
            'paymentMethod' => 'required|in:'.implode(',', config('constants.PAYMENT_METHODS')),
            'paymentDate' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function mount(Invoice $invoice)
    {
        $this->paymentMethods = config('constants.PAYMENT_METHODS');
        $this->paymentMethods = config('constants.PAYMENT_METHODS');
        $this->paymentAmount = $invoice->amount_due; // Default to paying the full remaining amount
        $this->paymentDate = Carbon::now()->format('Y-m-d');
        // Load relationships needed for display
        $this->invoice = $invoice->load(['patient', 'user', 'lineItems.billingCode']);
    }

    // --- Action Methods ---

    public function markAsSent()
    {
        if ($this->invoice->status === 'Draft') {
            $this->invoice->update(['status' => 'Sent']);
            session()->flash('success', 'Invoice '.$this->invoice->invoice_number.' marked as Sent.');
        }
    }

    /**
     * Records the payment, updates the invoice status, and recalculates the amount due.
     */
    public function recordPayment()
    {
        Log::debug('Before validation');
        $this->validate();
        Log::debug('After validation');
        try {
            DB::beginTransaction();

            $amountPaid = (float) $this->paymentAmount;
            $newAmountDue = $this->invoice->amount_due - $amountPaid;

            // 1. Create the Payment Record
            Payment::create([
                'invoice_id' => $this->invoice->id,
                'patient_id' => $this->invoice->patient_id,
                'amount' => $amountPaid,
                'payment_date' => $this->paymentDate,
                'method' => $this->paymentMethod,
                'notes' => $this->notes,
                'user_id' => Auth::id(),
            ]);

            // 2. Update the Invoice Record
            $newStatus = 'Partially Paid';
            if ($newAmountDue <= 0.00) {
                $newStatus = 'Paid';
                $newAmountDue = 0.00; // Ensure no negative balance
            } elseif ($this->invoice->total_amount == $newAmountDue) {
                $newStatus = $this->invoice->status; // Should not happen if amountPaid > 0
            } elseif ($this->invoice->status === 'Draft' || $this->invoice->status === 'Sent') {
                $newStatus = 'Partially Paid';
            } else {
                $newStatus = $this->invoice->status; // Keep existing status if already Partially Paid
            }

            $this->invoice->update([
                'amount_due' => $newAmountDue,
                'status' => $newStatus,
            ]);
            DB::commit();
            // 3. Emit event to parent component (e.g., ShowInvoice) to refresh and close modal
            $this->dispatch('paymentRecorded');

            session()->flash('success', 'Payment of '.format_currency($this->paymentAmount).' recorded successfully for Invoice '.$this->invoice->invoice_number.'.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::debug('Error recording payment '.json_encode($e->getMessage()));
            session()->flash('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    // Placeholder for future cancellation logic
    /*public function cancelInvoice()
    {
        if ($this->invoice->status !== 'Canceled' && $this->invoice->amount_due == $this->invoice->total_amount) {
            $this->invoice->update(['status' => 'Canceled']);
            session()->flash('warning', 'Invoice '.$this->invoice->invoice_number.' has been canceled.');
        } else {
            session()->flash('error', 'Cannot cancel invoice with existing payments or non-draft status.');
        }
    }*/

    public function cancelInvoice()
    {
        // Rule: Cannot cancel if already canceled.
        if ($this->invoice->status === 'Canceled') {
            session()->flash('error', 'Invoice is already canceled.');

            return;
        }

        // Rule: Cannot cancel if any amount has been paid.
        // Assuming (total_amount - amount_due) represents total paid.
        if ($this->invoice->amount_due < $this->invoice->total_amount) {
            session()->flash('error', 'Cannot cancel invoice because payments have already been recorded.');

            return;
        }

        try {
            DB::transaction(function () {
                // 1. Reset associated Therapy Sessions
                // Find the IDs of the sessions linked to this invoice's line items
                $sessionIds = $this->invoice->lineItems
                    ->pluck('therapy_session_id')
                    ->filter() // Remove any null IDs from manual line items
                    ->toArray();

                if (! empty($sessionIds)) {
                    // Reset their billing status to 'Pending' so they can be re-invoiced
                    TherapySession::whereIn('id', $sessionIds)
                        ->update(['billing_status' => 'Pending']);
                }

                // 2. Cancel the Invoice
                $this->invoice->update(['status' => 'Canceled']);
            });

            session()->flash('warning', 'Invoice '.$this->invoice->invoice_number.' has been canceled and associated sessions reset to Pending.');
            $this->invoice->refresh(); // Refresh the model to reflect the new status

        } catch (\Exception $e) {
            session()->flash('error', 'Database error during cancellation: '.$e->getMessage());
            // Log the error here
        }
    }

    // Placeholder for Edit redirection
    public function redirectToEdit()
    {
        return $this->redirect(route('invoice.edit', $this->invoice->id), navigate: true);
    }

    public function render()
    {
        return view('livewire.show-invoice');
    }
}
