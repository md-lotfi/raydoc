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
use Mary\Traits\Toast;

class ShowInvoice extends Component
{
    use Toast;

    public Invoice $invoice;

    // Payment Modal State
    public $showPaymentModal = false;

    public $paymentAmount;

    public $paymentMethod;

    public $paymentDate;

    public $notes;

    // Options
    public $paymentMethods = [];

    protected $listeners = ['paymentRecorded' => '$refresh'];

    public function mount(Invoice $invoice)
    {
        // Eager load everything needed for the view, including Payments
        $this->invoice = $invoice->load(['patient', 'user', 'lineItems.billingCode', 'payments.user']);

        // Format payment methods for x-mary-select
        $this->paymentMethods = collect(config('constants.PAYMENT_METHODS', ['Cash', 'Credit Card', 'Bank Transfer', 'Insurance']))
            ->map(fn ($m) => ['id' => $m, 'name' => $m]);

        // Defaults
        $this->paymentAmount = $this->invoice->amount_due;
        $this->paymentDate = Carbon::now()->format('Y-m-d');
    }

    protected function rules()
    {
        return [
            'paymentAmount' => 'required|numeric|min:0.01|max:'.$this->invoice->amount_due,
            'paymentMethod' => 'required',
            'paymentDate' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ];
    }

    // --- Actions ---

    public function markAsSent()
    {
        if ($this->invoice->status === 'Draft') {
            $this->invoice->update(['status' => 'Sent']);
            $this->success(__('Updated'), __('Invoice marked as Sent.'));
        }
    }

    public function recordPayment()
    {
        Log::debug('validating record payment');
        $this->validate();

        try {
            DB::transaction(function () {
                $amountPaid = (float) $this->paymentAmount;
                $newAmountDue = $this->invoice->amount_due - $amountPaid;

                // 1. Create Payment
                Payment::create([
                    'invoice_id' => $this->invoice->id,
                    'patient_id' => $this->invoice->patient_id,
                    'amount' => $amountPaid,
                    'payment_date' => $this->paymentDate,
                    'payment_method' => $this->paymentMethod, // Ensure DB column matches
                    'notes' => $this->notes,
                    'user_id' => Auth::id(),
                ]);

                // 2. Update Invoice Status
                $newStatus = 'Partially Paid';
                if ($newAmountDue <= 0.005) { // Float tolerance
                    $newStatus = 'Paid';
                    $newAmountDue = 0;
                }

                $this->invoice->update([
                    'amount_due' => $newAmountDue,
                    'status' => $newStatus,
                ]);
            });

            $this->showPaymentModal = false;
            $this->invoice->refresh(); // Reload payments relation
            $this->success(__('Payment Recorded'), __('Payment of :amount received.', ['amount' => format_currency($this->paymentAmount)]));

        } catch (\Throwable $e) {
            $this->error(__('Error'), $e->getMessage());
        }
    }

    public function cancelInvoice()
    {
        if ($this->invoice->status === 'Canceled') {
            return;
        }

        // Prevent cancelling if partial payments exist
        if ($this->invoice->amount_due < $this->invoice->total_amount) {
            $this->error(__('Action Failed'), __('Cannot cancel an invoice that has payments. Refund payments first.'));

            return;
        }

        try {
            DB::transaction(function () {
                // 1. Reset Sessions to Pending
                $sessionIds = $this->invoice->lineItems->pluck('therapy_session_id')->filter()->toArray();
                if (! empty($sessionIds)) {
                    TherapySession::whereIn('id', $sessionIds)->update(['billing_status' => 'Pending']);
                }

                // 2. Cancel Invoice
                $this->invoice->update(['status' => 'Canceled']);
            });

            $this->warning(__('Canceled'), __('Invoice canceled and items reset.'));
            $this->invoice->refresh();

        } catch (\Exception $e) {
            $this->error(__('Error'), $e->getMessage());
        }
    }

    public function redirectToEdit()
    {
        return redirect()->route('invoice.edit', $this->invoice->id);
    }

    public function render()
    {
        return view('livewire.show-invoice');
    }
}
