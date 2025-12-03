<?php

namespace App\Livewire\Invoices;

use App\Models\BillingCode;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Patient;
use App\Models\TherapySession;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GenerateInvoice extends Component
{
    // --- Invoice Properties ---
    public Patient $patient;

    public $invoice_number; // Will be generated on save

    public $issued_date;

    public $due_date;

    public $status = 'Draft';

    // --- Line Item Array (Dynamic Field Management) ---
    public $lineItems = [];

    // --- Calculated Totals ---
    public $total_amount = 0.00;

    public $amount_due = 0.00;

    // --- Reference Data ---
    public $availableBillingCodes;

    // --- Initial Sessions Data (Passed via Mount) ---
    protected $initialSessions;

    // Property to hold errors during automatic code matching
    public $billingErrors = []; // <-- ADDED

    protected function rules()
    {
        return [
            'issued_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issued_date',
            'status' => 'required|in:Draft,Sent,Paid,Partially Paid,Canceled',
            'lineItems.*.service_description' => 'required|string|max:255',
            'lineItems.*.billing_code_id' => 'required|exists:billing_codes,id',
            'lineItems.*.unit_price' => 'required|numeric|min:0.01|decimal:0,2',
            'lineItems.*.units' => 'required|integer|min:1',
            // 'lineItems.*.therapy_session_id' is optional for non-session charges
        ];
    }

    public function mount(Patient $patient, array $sessionIds = [])
    {
        // 1. Load patient and pending sessions
        $this->patient = $patient->load(['therapySessions' => function ($query) {
            $query->where('billing_status', 'Pending');
        }]);

        $this->issued_date = Carbon::now()->format('Y-m-d');
        $this->due_date = Carbon::now()->addDays(30)->format('Y-m-d'); // Default 30 days due

        // 2. Fetch reference data
        $this->availableBillingCodes = BillingCode::where('is_active', true)->get();

        // 3. Determine sessions to bill
        if (! empty($sessionIds)) {
            // Prioritize sessions passed via $sessionIds
            $this->initialSessions = TherapySession::whereIn('id', $sessionIds)->get();
        } elseif ($patient->therapySessions->count() > 0) {
            // Otherwise, use all pending sessions loaded on the patient
            $this->initialSessions = $patient->therapySessions;
        } else {
            // No sessions available, start with a manual line item
            $this->addLineItem();
            $this->calculateTotals();

            return;
        }

        // 4. Generate line items and calculate totals
        $this->generateLineItemsFromSessions();
        $this->calculateTotals();
    }

    // --- Dynamic Line Item Management ---

    private function generateLineItemsFromSessions()
    {
        foreach ($this->initialSessions as $session) {
            try {
                // CALL THE SERVICE: Determine the appropriate code based on duration
                $billingCode = BillingService::getBillingCodeForSession($session->id);

                // USE THE RESULT: Use the properties from the $billingCode model
                $this->lineItems[] = [
                    'therapy_session_id' => $session->id,
                    'billing_code_id' => $billingCode->id, // <--- NOW CORRECTLY POPULATED
                    'service_description' => $billingCode->name.
                    ' | '.__('Session').': '.$session->scheduled_at->translatedFormat('Y-m-d'),
                    'unit_price' => $billingCode->standard_rate,
                    'units' => 1,
                    'subtotal' => $billingCode->standard_rate,
                ];

            } catch (\Exception $e) {
                // Handle the exception by adding a placeholder for manual review
                $this->billingErrors[] = [
                    'sessionId' => $session->id,
                    'message' => $e->getMessage(),
                ];

                // Add a zero-cost line item requiring manual input
                $this->lineItems[] = [
                    'therapy_session_id' => $session->id,
                    'billing_code_id' => null, // Admin must select this
                    'service_description' => 'MANUAL REVIEW: Session on '.$session->scheduled_at->format('Y-m-d'),
                    'unit_price' => 0.00,
                    'units' => 1,
                    'subtotal' => 0.00,
                ];
            }
        }
    }

    public function addLineItem()
    {
        // ... (rest of addLineItem remains the same)
        $this->lineItems[] = [
            'therapy_session_id' => null,
            'billing_code_id' => null,
            'service_description' => '',
            'unit_price' => 0.00,
            'units' => 1,
            'subtotal' => 0.00,
        ];
    }

    public function removeLineItem($index)
    {
        // ... (rest of removeLineItem remains the same)
        unset($this->lineItems[$index]);
        $this->lineItems = array_values($this->lineItems); // Re-index array
        $this->calculateTotals();
    }

    // --- Calculation and Event Handling ---

    public function updatedLineItems($value, $key)
    {
        // ... (rest of updatedLineItems remains the same)
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        if (in_array($field, ['unit_price', 'units'])) {
            $this->lineItems[$index]['subtotal'] =
                (float) $this->lineItems[$index]['unit_price'] * (int) $this->lineItems[$index]['units'];
        }

        if ($field === 'billing_code_id' && $value) {
            // Automatically set the price and description when a code is selected
            $code = $this->availableBillingCodes->find($value);
            if ($code) {
                $this->lineItems[$index]['unit_price'] = $code->standard_rate;
                $this->lineItems[$index]['service_description'] = $code->name;
                $this->lineItems[$index]['subtotal'] = (float) $code->standard_rate * (int) $this->lineItems[$index]['units'];
            }
        }

        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        // ... (rest of calculateTotals remains the same)
        $this->total_amount = 0;
        foreach ($this->lineItems as $item) {
            $this->total_amount += $item['subtotal'];
        }
        // Assuming no payments/credits applied at creation, total due is total amount
        $this->amount_due = $this->total_amount;
    }

    // --- Save Logic (remains largely the same) ---

    public function saveInvoice()
    {
        // ... (saveInvoice logic remains the same, no changes needed here)
        $this->validate();

        // Generate a simple unique invoice number (e.g., INV-YYMM-0001)
        $this->invoice_number = 'INV-'.Carbon::now()->format('ym').'-'.str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
        try {
            DB::beginTransaction();

            // 1. Create the main Invoice record
            $invoice = Invoice::create([
                'invoice_number' => $this->invoice_number,
                'patient_id' => $this->patient->id,
                'user_id' => Auth::id(),
                'total_amount' => $this->total_amount,
                'amount_due' => $this->amount_due,
                'issued_date' => $this->issued_date,
                'due_date' => $this->due_date,
                'status' => $this->status,
            ]);
            // 2. Create the Line Items and update sessions
            $sessionIdsToUpdate = [];
            foreach ($this->lineItems as $itemData) {
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'therapy_session_id' => $itemData['therapy_session_id'] ?? null,
                    'billing_code_id' => $itemData['billing_code_id'],
                    'service_description' => $itemData['service_description'],
                    'unit_price' => $itemData['unit_price'],
                    'units' => $itemData['units'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                if (isset($itemData['therapy_session_id'])) {
                    $sessionIdsToUpdate[] = $itemData['therapy_session_id'];
                }
            }

            // 3. Update sessions' billing status to 'Billed'
            if (! empty($sessionIdsToUpdate)) {
                TherapySession::whereIn('id', $sessionIdsToUpdate)
                    ->update(['billing_status' => 'Billed']);
            }

            DB::commit();
            session()->flash('success', 'Invoice '.$this->invoice_number.' created and sessions marked as billed!');

            return $this->redirect(route('invoice.show', ['invoice' => $invoice->id]), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to create invoice: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.invoice.generate-invoice');
    }
}
