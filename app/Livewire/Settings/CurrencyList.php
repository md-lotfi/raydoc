<?php

namespace App\Livewire\Settings;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class CurrencyList extends Component
{
    use Toast, WithPagination;

    // --- State ---
    public string $search = '';

    public array $sortBy = ['column' => 'code', 'direction' => 'asc'];

    // --- Delete Confirmation ---
    public $currencyToDeleteId;

    public $showDeleteModal = false;

    // --- Headers ---
    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => __('Code'), 'class' => 'font-bold w-20'],
            ['key' => 'currency_name', 'label' => __('Name'), 'class' => 'w-1/3'],
            ['key' => 'symbol', 'label' => __('Symbol'), 'class' => 'text-center'],
            ['key' => 'exchange_rate', 'label' => __('Exchange Rate'), 'class' => 'text-right font-mono'],
            ['key' => 'formatting', 'label' => __('Format'), 'sortable' => false, 'class' => 'hidden md:table-cell'],
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

    public function confirmDelete($id)
    {
        // Prevent deleting the default system currency
        if ($id == settings()->default_currency_id) {
            $this->error(__('Action Denied'), __('You cannot delete the default system currency. Change it in General Settings first.'));

            return;
        }

        $this->currencyToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->currencyToDeleteId) {
            // Double check before execution
            if ($this->currencyToDeleteId == settings()->default_currency_id) {
                $this->error(__('Action Denied'), __('Cannot delete default currency.'));
                $this->showDeleteModal = false;

                return;
            }

            $currency = Currency::find($this->currencyToDeleteId);
            $currency?->delete();
            $this->success(__('Deleted'), __('Currency removed successfully.'));
        }

        $this->showDeleteModal = false;
        $this->currencyToDeleteId = null;
    }

    public function render()
    {
        $currencies = Currency::query()
            ->when($this->search, function (Builder $q) {
                $q->where('code', 'like', "%$this->search%")
                    ->orWhere('currency_name', 'like', "%$this->search%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);

        return view('livewire.currency-list', [
            'currencies' => $currencies,
            'defaultCurrencyId' => settings()->default_currency_id,
        ]);
    }
}
