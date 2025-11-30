<?php

namespace App\Livewire\Settings;

use App\Models\Currency;
use Livewire\Component;
use Livewire\WithPagination;

class CurrencyList extends Component
{
    use WithPagination;

    public $search = '';

    public $currencyIdToDelete;

    public $showDeleteModal = false;

    public function confirmDelete($currencyId)
    {
        $this->currencyIdToDelete = $currencyId;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        // Add a check to prevent deleting the currency if it's set as the default
        // in the Settings table (if applicable).

        Currency::findOrFail($this->currencyIdToDelete)->delete();

        $this->showDeleteModal = false;
        session()->flash('success', 'Currency deleted successfully.');
        $this->resetPage(); // Reset pagination after deletion
    }

    public function render()
    {
        $query = Currency::query();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('currency_name', 'like', '%'.$this->search.'%');
            });
        }

        $currencies = $query->paginate(5);

        return view('livewire.currency-list', compact('currencies'));
    }
}
