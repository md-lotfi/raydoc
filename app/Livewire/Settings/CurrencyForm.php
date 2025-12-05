<?php

namespace App\Livewire\Settings;

use App\Models\Currency;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Mary\Traits\Toast;

class CurrencyForm extends Component
{
    use Toast;

    public ?Currency $currency = null;

    // Form Properties
    public string $currency_name = '';

    public string $code = '';

    public string $symbol = '';

    public string $thousand_separator = ',';

    public string $decimal_separator = '.';

    public $exchange_rate = 1.0000;

    public function mount(?Currency $currency = null)
    {
        if ($currency) {
            // 🟦 EDIT MODE
            $this->currency = $currency;

            $this->currency_name = $this->currency->currency_name;
            $this->code = $this->currency->code;
            $this->symbol = $this->currency->symbol;
            $this->thousand_separator = $this->currency->thousand_separator;
            $this->decimal_separator = $this->currency->decimal_separator;
            $this->exchange_rate = $this->currency->exchange_rate;
        }
    }

    protected function rules()
    {
        return [
            'currency_name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:10', 'uppercase', Rule::unique('currencies')->ignore($this->currency?->id)],
            'symbol' => 'required|string|max:10',
            'thousand_separator' => 'required|string|max:1',
            'decimal_separator' => 'required|string|max:1',
            'exchange_rate' => 'required|numeric|min:0.000001',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'currency_name' => $this->currency_name,
            'code' => strtoupper($this->code),
            'symbol' => $this->symbol,
            'thousand_separator' => $this->thousand_separator,
            'decimal_separator' => $this->decimal_separator,
            'exchange_rate' => $this->exchange_rate,
        ];

        if ($this->currency) {
            $this->currency->update($data);
            $message = __('Currency updated successfully.');
        } else {
            Currency::create($data);
            $message = __('New currency added successfully.');
        }

        $this->success(__('Saved'), $message);

        return redirect()->route('settings.currency.list');
    }

    public function render()
    {
        return view('livewire.currency-form');
    }
}
