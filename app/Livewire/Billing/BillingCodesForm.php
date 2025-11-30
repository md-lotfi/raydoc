<?php

namespace App\Livewire\Billing;

use App\Models\BillingCode;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BillingCodesForm extends Component
{
    public ?BillingCode $billingCode;

    // Form Properties
    public $code;

    public $name;

    public $standard_rate;

    public $min_duration_minutes = null;

    public $max_duration_minutes = null;

    public $is_active = true; // Default to active

    public $description;

    protected $validationAttributes = [
        'min_duration_minutes' => 'minimum duration',
        'max_duration_minutes' => 'maximum duration',
    ];

    protected function rules()
    {
        return [
            'code' => $this->billingCode ? 'required|string|max:20|exists:billing_codes,code' : 'required|string|max:20|unique:billing_codes,code',
            'name' => 'required|string|max:255',
            'standard_rate' => 'required|numeric|min:0.01|decimal:0,2',
            'min_duration_minutes' => 'nullable|integer|min:1',
            'max_duration_minutes' => 'nullable|integer|min:1|gte:min_duration_minutes', // Max must be >= Min
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ];
    }

    public function mount(BillingCode $billingCode)
    {
        if ($billingCode) {
            $this->billingCode = $billingCode;
            $this->code = $billingCode->code;
            $this->name = $billingCode->name;
            $this->standard_rate = $billingCode->standard_rate;
            $this->min_duration_minutes = $billingCode->min_duration_minutes;
            $this->max_duration_minutes = $billingCode->max_duration_minutes;
            $this->is_active = $billingCode->is_active;
            $this->description = $billingCode->description;
        }
    }

    protected function getDataForSave()
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'standard_rate' => $this->standard_rate,
            'min_duration_minutes' => $this->min_duration_minutes,
            'max_duration_minutes' => $this->max_duration_minutes,
            'is_active' => $this->is_active,
            'description' => $this->description,
        ];
    }

    public function saveCode()
    {
        $this->validate();

        try {
            $data = $this->getDataForSave();
            if ($this->billingCode) {
                $this->billingCode->update($data);
                $action = 'Updated';
            } else {
                BillingCode::create($data);
                $action = 'Added';
            }
            $this->dispatch('billingCode'.$action);
            session()->flash('success', 'Billing code '.$this->code.' created successfully.');

            return $this->redirect(route('billing.codes.list'), navigate: true);
        } catch (\Throwable $th) {
            Log::debug($th->getMessage());
            session()->flash('error', 'There was an error saving the billing code: '.$th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.billing.billing-codes-form');
    }
}
