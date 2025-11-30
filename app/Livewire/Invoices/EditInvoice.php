<?php

namespace App\Livewire\Invoices;

use App\Models\BillingCode;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Patient;
use App\Models\TherapySession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EditInvoice extends Component
{
    // --- Invoice Properties ---
    public Invoice $invoice; // The main invoice model being edited

    public Patient $patient; // The patient associated with the invoice

    // Properties mapped from the Invoice model
    public $issued_date;

    public $due_date;

    public $status;

    // Original session IDs linked to this invoice (before any edits)
    public array $originalBilledSessionIds = [];

    // --- Line Item Array (Dynamic Field Management) ---
    public $lineItems = [];

    // --- Calculated Totals ---
    public $total_amount = 0.00;

    public $amount_due = 0.00; // Important: this should reflect the current total minus any payments.

    // --- Reference Data ---
    public $availableBillingCodes;

    // Billing errors are not relevant for editing, but kept for structure
    public $billingErrors = [];

    protected function rules()
    {
        // Adjust rules to ensure status is not changed away from 'Paid' if payments exist
        $totalPaid = $this->invoice->total_amount - $this->invoice->amount_due;
        $allowedStatuses = $totalPaid > 0 ? ['Partially Paid', 'Paid'] : ['Draft', 'Sent', 'Canceled'];

        return [
            'issued_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issued_date',
            'status' => 'required|in:'.implode(',', $allowedStatuses), // Restrict status based on payments
            'lineItems.*.service_description' => 'required|string|max:255',
            'lineItems.*.billing_code_id' => 'required|exists:billing_codes,id',
            'lineItems.*.unit_price' => 'required|numeric|min:0.01|decimal:0,2',
            'lineItems.*.units' => 'required|integer|min:1',
            // Note: 'lineItems.*.id' (for existing items) is NOT validated here, only the content.
        ];
    }

    public function mount(Invoice $invoice)
    {
        // 1. Load the existing invoice and relationships
        $this->invoice = $invoice->load(['patient', 'lineItems']);
        $this->patient = $invoice->patient;

        // 2. Populate component properties from the invoice
        $this->issued_date = $invoice->issued_date->format('Y-m-d');
        $this->due_date = $invoice->due_date->format('Y-m-d');
        $this->status = $invoice->status;

        // 3. Populate lineItems for editing
        $this->lineItems = $this->invoice->lineItems->map(function ($item) {
            return [
                'id' => $item->id, // Important: keep the line item ID for reference
                'therapy_session_id' => $item->therapy_session_id,
                'billing_code_id' => $item->billing_code_id,
                'service_description' => $item->service_description,
                'unit_price' => $item->unit_price,
                'units' => $item->units,
                'subtotal' => $item->subtotal,
            ];
        })->toArray();

        // Store original session IDs for cleanup later
        $this->originalBilledSessionIds = $this->invoice->lineItems
            ->pluck('therapy_session_id')
            ->filter() // Removes null entries
            ->toArray();

        Log::debug('originalBilledSessionIds '.json_encode($this->originalBilledSessionIds));

        // 4. Fetch reference data and calculate initial totals
        $this->availableBillingCodes = BillingCode::where('is_active', true)->get();
        $this->calculateTotals();
    }

    // --- Dynamic Line Item Management (Same as GenerateInvoice) ---

    public function addLineItem()
    {
        $this->lineItems[] = [
            // No ID means this is a new line item
            'therapy_session_id' => null,
            'billing_code_id' => null,
            'service_description' => '',
            'unit_price' => 0.00,
            'units' => 1,
            'subtotal' => 0.00,
        ];
        $this->calculateTotals();
    }

    public function removeLineItem($index)
    {
        unset($this->lineItems[$index]);
        $this->lineItems = array_values($this->lineItems); // Re-index array
        $this->calculateTotals();
    }

    public function updatedLineItems($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        if (in_array($field, ['unit_price', 'units'])) {
            $this->lineItems[$index]['subtotal'] =
                (float) $this->lineItems[$index]['unit_price'] * (int) $this->lineItems[$index]['units'];
        }

        if ($field === 'billing_code_id' && $value) {
            $code = $this->availableBillingCodes->find($value);
            if ($code) {
                $this->lineItems[$index]['unit_price'] = $code->standard_rate;
                // Preserve description if linked to a session, otherwise use code name
                if (! isset($this->lineItems[$index]['therapy_session_id'])) {
                    $this->lineItems[$index]['service_description'] = $code->name;
                }
                $this->lineItems[$index]['subtotal'] = (float) $code->standard_rate * (int) $this->lineItems[$index]['units'];
            }
        }

        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $newTotal = 0;
        foreach ($this->lineItems as $item) {
            $newTotal += $item['subtotal'];
        }
        $this->total_amount = $newTotal;

        // Calculate amount due based on the new total and the total amount already paid.
        // We assume total_amount initially stored on the invoice is correct.
        $totalPaid = $this->invoice->total_amount - $this->invoice->amount_due;
        $this->amount_due = max(0, $this->total_amount - $totalPaid);

        // Auto-update status if paid in full (only if it wasn't Canceled)
        if ($this->amount_due === 0.00 && $totalPaid > 0 && $this->status !== 'Canceled') {
            $this->status = 'Paid';
        } elseif ($totalPaid > 0 && $this->status === 'Draft') {
            $this->status = 'Partially Paid';
        }
    }

    // --- Update Logic ---
    public function updateInvoice()
    {
        $this->validate();

        try {
            DB::transaction(function () {

                // 1. Update the main Invoice record
                $this->invoice->update([
                    'total_amount' => $this->total_amount,
                    'amount_due' => $this->amount_due,
                    'issued_date' => $this->issued_date,
                    'due_date' => $this->due_date,
                    'status' => $this->status,
                ]);

                // 2. Prepare for Line Item Sync and Session Tracking
                $currentSessionIds = [];
                $lineItemIdsToKeep = [];

                // 3. Process/Upsert Line Items
                foreach ($this->lineItems as $itemData) {

                    // Identify existing vs. new item
                    $lineItemId = $itemData['id'] ?? null;

                    // Prepare data for upsert
                    $upsertData = [
                        'invoice_id' => $this->invoice->id,
                        'therapy_session_id' => $itemData['therapy_session_id'] ?? null,
                        'billing_code_id' => $itemData['billing_code_id'],
                        'service_description' => $itemData['service_description'],
                        'unit_price' => $itemData['unit_price'],
                        'units' => $itemData['units'],
                        'subtotal' => $itemData['subtotal'],
                    ];

                    if ($lineItemId) {
                        // Update existing item
                        InvoiceLineItem::find($lineItemId)->update($upsertData);
                        $lineItemIdsToKeep[] = $lineItemId;
                    } else {
                        // Create new item
                        $newItem = InvoiceLineItem::create($upsertData);
                        $lineItemIdsToKeep[] = $newItem->id;
                    }

                    if (isset($itemData['therapy_session_id'])) {
                        $currentSessionIds[] = $itemData['therapy_session_id'];
                    }
                }

                // 4. Delete removed Line Items
                $this->invoice->lineItems()
                    ->whereNotIn('id', $lineItemIdsToKeep)
                    ->delete();

                // 5. Manage Therapy Session Status Cleanup (CRITICAL LOGIC)

                // Sessions that were removed from the invoice (original billed - current items)
                $sessionsToReset = array_diff($this->originalBilledSessionIds, $currentSessionIds);

                Log::debug('$this->originalBilledSessionIds and sessionsToReset '.json_encode($this->originalBilledSessionIds).' '.json_encode($sessionsToReset));

                if (! empty($sessionsToReset)) {
                    // These sessions were on the invoice but are no longer. Reset to Pending.
                    TherapySession::whereIn('id', $sessionsToReset)
                        ->update(['billing_status' => 'Pending']);
                }

                // 5b. ENSURE BILLED: Sessions that are currently on the invoice
                if (! empty($currentSessionIds)) {
                    // These sessions are explicitly on the invoice. Ensure they are Billed.
                    TherapySession::whereIn('id', $currentSessionIds)
                        ->update(['billing_status' => 'Billed']);
                }
            });

            session()->flash('success', 'Invoice '.$this->invoice->invoice_number.' updated successfully!');

            return $this->redirect(route('invoice.show', $this->invoice->id), navigate: true);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update invoice: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.edit-invoice');
    }
}
