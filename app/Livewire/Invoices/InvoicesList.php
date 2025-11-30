<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;

class InvoicesList extends Component
{
    use WithPagination;

    // --- Component State ---
    public $patientId = null;

    public $search = '';

    public $statusFilter = 'All';

    // --- Reference Data ---
    protected $allStatuses = ['All', 'Draft', 'Sent', 'Paid', 'Partially Paid', 'Canceled'];

    /**
     * Optional patient model for contextual display (e.g., "Invoices for John Doe")
     */
    public ?Patient $patient = null;

    /**
     * Mount method to accept an optional patient ID.
     *
     * * @param int|null $patientId
     */
    public function mount($patientId = null)
    {
        $this->patientId = $patientId;
        if ($patientId) {
            $this->patient = Patient::findOrFail($patientId);
        }
    }

    // --- Computed Property to Fetch Filtered Invoices ---

    public function getInvoicesProperty()
    {
        $query = Invoice::query()
            ->with('patient') // Eager load patient for listing
            ->latest();

        // 1. Filter by Patient (if patientId is set)
        if ($this->patientId) {
            $query->where('patient_id', $this->patientId);
        }

        // 2. Filter by Status
        if ($this->statusFilter !== 'All') {
            $query->where('status', $this->statusFilter);
        }

        // 3. Filter by Search (Invoice Number or Patient Name)
        if ($this->search) {
            $query->where('invoice_number', 'like', '%'.$this->search.'%')
                ->orWhereHas('patient', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                });
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.invoices-list', [
            'invoices' => $this->invoices,
            'statuses' => $this->allStatuses,
        ]);
    }
}
