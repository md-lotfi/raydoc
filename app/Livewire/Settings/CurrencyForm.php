<?php

namespace App\Livewire\Settings;

use App\Models\Currency;
use Livewire\Component;

class CurrencyForm extends Component
{
    // Form Properties
    public $currency_name;

    public $code;

    public $symbol;

    public $thousand_separator = ','; // Default to comma

    public $decimal_separator = '.';   // Default to dot

    public $exchange_rate = 1;         // Default to 1 (for the base currency)

    protected $rules = [
        'currency_name' => 'required|string|max:255',
        'code' => 'required|string|max:10|unique:currencies,code', // e.g., USD, EUR
        'symbol' => 'required|string|max:5', // e.g., $, €, £
        'thousand_separator' => 'required|string|max:1',
        'decimal_separator' => 'required|string|max:1',
        'exchange_rate' => 'nullable|numeric|min:0.0001',
    ];

    public function saveCurrency()
    {
        $this->validate();

        // The exchange rate is crucial for multi-currency operations.
        // We set it to null if the user leaves it blank, as defined in the migration.
        $rate = empty($this->exchange_rate) ? null : $this->exchange_rate;

        Currency::create([
            'currency_name' => $this->currency_name,
            'code' => strtoupper($this->code),
            'symbol' => $this->symbol,
            'thousand_separator' => $this->thousand_separator,
            'decimal_separator' => $this->decimal_separator,
            'exchange_rate' => $rate,
        ]);

        session()->flash('success', 'Currency '.strtoupper($this->code).' added successfully.');

        // Redirect back to the currency list
        return $this->redirect(route('admin.settings.currencies.list'), navigate: true);
    }

    public function render()
    {
        return view('livewire.currency-form');
    }
}
