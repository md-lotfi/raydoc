<?php

namespace App\Livewire\Billing;

use App\Models\BillingCode;
use Livewire\Component;
use Livewire\WithPagination;

class BillingCodesList extends Component
{
    use WithPagination;

    public $search = '';
    // ... other properties for delete modal

    // Computed property to fetch and filter the codes
    public function getBillingCodesProperty()
    {
        return BillingCode::query()
            ->when($this->search, function ($query) {
                $query->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->paginate(15);
    }

    // Method to toggle the active status directly from the badge
    public function toggleActive($codeId)
    {
        $code = BillingCode::findOrFail($codeId);
        $code->is_active = ! $code->is_active;
        $code->save();
        session()->flash('success', 'Billing code status updated successfully.');
    }

    public function render()
    {
        $query = BillingCode::query();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%');
            });
        }

        $billingCodes = $query->paginate(5);

        return view('livewire.billing.billing-codes-list', compact('billingCodes'));
    }
}
