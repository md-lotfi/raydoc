<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;

class InvoicesList extends Component
{
    use WithPagination, Toast;

    // --- Component State ---
    public $patientId = null;
    public $search = '';
    public $statusFilter = ''; // Empty = All
    
    // Sorting
    public array $sortBy = ['column' => 'issued_date', 'direction' => 'desc'];

    // Delete Confirmation
    public $showDeleteModal = false;
    public $invoiceToDeleteId = null;

    public ?Patient $patient = null;

    public function mount($patientId = null)
    {
        $this->patientId = $patientId;
        if ($patientId) {
            $this->patient = Patient::findOrFail($patientId);
        }
    }

    // --- Actions ---

    public function sort($column)
    {
        if ($this->sortBy['column'] === $column) {
            $this->sortBy['direction'] = $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy['column'] = $column;
            $this->sortBy['direction'] = 'asc';
        }
    }

    public function confirmDelete($id)
    {
        $this->invoiceToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->invoiceToDeleteId) {
            $invoice = Invoice::find($this->invoiceToDeleteId);
            
            // Optional: Prevent deleting paid invoices for integrity
            if ($invoice && $invoice->status === 'Paid') {
                $this->error(__('Cannot delete'), __('Paid invoices cannot be deleted for audit purposes.'));
                $this->showDeleteModal = false;
                return;
            }

            $invoice?->delete();
            $this->success(__('Deleted'), __('Invoice record removed successfully.'));
        }
        $this->showDeleteModal = false;
        $this->invoiceToDeleteId = null;
    }

    // --- Render ---

    public function render()
    {
        // 1. Base Query
        $query = Invoice::query()->with('patient');

        // Context: Specific Patient
        if ($this->patientId) {
            $query->where('patient_id', $this->patientId);
        }

        // 2. Search
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('invoice_number', 'like', "%$this->search%")
                  ->orWhereHas('patient', function ($p) {
                      $p->where('first_name', 'like', "%$this->search%")
                        ->orWhere('last_name', 'like', "%$this->search%");
                  });
            });
        }

        // 3. Filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // 4. Sort
        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        // 5. Statistics (Scoped to current patient if applicable)
        $statsQuery = Invoice::query();
        if ($this->patientId) {
            $statsQuery->where('patient_id', $this->patientId);
        }

        $stats = [
            'total_volume' => $statsQuery->sum('total_amount'),
            'outstanding' => $statsQuery->sum('amount_due'),
            'paid_count' => (clone $statsQuery)->where('status', 'Paid')->count(),
            'overdue_count' => (clone $statsQuery)->where('due_date', '<', now())->where('status', '!=', 'Paid')->count(),
        ];

        return view('livewire.invoices-list', [
            'invoices' => $query->paginate(10),
            'stats' => $stats,
            'statuses' => ['Draft', 'Sent', 'Paid', 'Partially Paid', 'Canceled']
        ]);
    }
}