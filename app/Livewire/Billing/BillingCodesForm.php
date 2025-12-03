<?php

namespace App\Livewire\Billing;

use App\Models\BillingCode;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Mary\Traits\Toast;

class BillingCodesForm extends Component
{
    use Toast;

    public ?BillingCode $billingCode = null;

    // Form Properties
    public string $code = '';
    public string $name = '';
    public ?string $description = null;
    public $standard_rate = 0.00;
    
    // Constraints
    public ?int $min_duration_minutes = null;
    public ?int $max_duration_minutes = null;
    
    // Status
    public bool $is_active = true;

    public function mount($id = null)
    {
        if ($id) {
            // 🟦 EDIT MODE
            $this->billingCode = BillingCode::findOrFail($id);
            $this->fill($this->billingCode->toArray());
        }
    }

    protected function rules()
    {
        return [
            'code' => [
                'required', 
                'string', 
                'max:20', 
                Rule::unique('billing_codes')->ignore($this->billingCode?->id)
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'standard_rate' => 'required|numeric|min:0',
            'min_duration_minutes' => 'nullable|integer|min:0',
            'max_duration_minutes' => 'nullable|integer|gte:min_duration_minutes',
            'is_active' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'standard_rate' => $this->standard_rate,
            'min_duration_minutes' => $this->min_duration_minutes,
            'max_duration_minutes' => $this->max_duration_minutes,
            'is_active' => $this->is_active,
        ];

        if ($this->billingCode) {
            $this->billingCode->update($data);
            $message = __('Service code updated successfully.');
        } else {
            BillingCode::create($data);
            $message = __('New service code created.');
        }

        $this->success(__('Saved'), $message);

        return redirect()->route('billing.codes.list');
    }

    public function render()
    {
        return view('livewire.billing.billing-codes-form');
    }
}