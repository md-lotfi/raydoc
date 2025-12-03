<?php

namespace App\Livewire\Billing;

use App\Models\BillingCode;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class BillingCodesList extends Component
{
    use Toast, WithPagination;

    // --- State ---
    public string $search = '';

    public string $statusFilter = ''; // '' = All, '1' = Active, '0' = Inactive

    public array $sortBy = ['column' => 'code', 'direction' => 'asc'];

    // Delete Confirmation
    public $codeToDeleteId = null;

    public $showDeleteModal = false;

    // --- Data Definition ---
    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => __('Code'), 'class' => 'font-mono font-bold w-32'],
            ['key' => 'name', 'label' => __('Service Name'), 'class' => 'w-1/3'],
            ['key' => 'standard_rate', 'label' => __('Rate'), 'class' => 'text-right font-bold'],
            ['key' => 'duration', 'label' => __('Duration'), 'sortable' => false], // Virtual column
            ['key' => 'is_active', 'label' => __('Status'), 'class' => 'text-center'],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ];
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

    public function toggleStatus($id)
    {
        $code = BillingCode::find($id);
        if ($code) {
            $code->is_active = ! $code->is_active;
            $code->save();

            $status = $code->is_active ? __('Active') : __('Inactive');
            $this->success(__('Updated'), __("Service code is now $status."));
        }
    }

    public function confirmDelete($id)
    {
        $this->codeToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->codeToDeleteId) {
            $code = BillingCode::find($this->codeToDeleteId);
            $code?->delete();
            $this->success(__('Deleted'), __('Service code removed.'));
        }
        $this->showDeleteModal = false;
        $this->codeToDeleteId = null;
    }

    public function render()
    {
        $query = BillingCode::query();

        // Search
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        // Filter
        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        // Sort
        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        // Statistics
        $stats = [
            'total' => BillingCode::count(),
            'active' => BillingCode::where('is_active', 1)->count(),
            'inactive' => BillingCode::where('is_active', 0)->count(),
        ];

        return view('livewire.billing.billing-codes-list', [
            'billingCodes' => $query->paginate(10),
            'stats' => $stats,
        ]);
    }
}
