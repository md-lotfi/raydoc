<?php

namespace App\Livewire\Settings;

use App\Models\Currency;
use App\Models\Setting;
use Livewire\Component;

class AppSettings extends Component
{
    // The single instance of the Setting model
    public Setting $settings;

    // Form properties bound to the model attributes
    public $company_name;

    public $company_email;

    public $company_phone;

    public $notification_email;

    public $default_currency_id;

    public $default_currency_position;

    public $footer_text;

    public $company_address;

    // Metadata properties
    public $default_language;

    // Data for select fields
    public $availableCurrencies;

    public $currencyPositions = ['prefix' => '$100', 'suffix' => '100$']; // Simple options

    protected function rules()
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'notification_email' => 'required|email|max:255',
            'default_currency_id' => 'required|exists:currencies,id',
            'default_currency_position' => 'required|in:prefix,suffix',
            'footer_text' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'default_language' => 'required|string|max:10',
        ];
    }

    public function mount()
    {
        // 1. Ensure the settings record exists (or create it if not)
        $this->settings = Setting::firstOrCreate(
            // Search criteria: If an ID 1 exists, use it. Otherwise, create.
            ['id' => 1],
            // Default values if the record needs to be created
            [
                'company_name' => config('app.name', 'Therapy Management App'),
                'company_email' => 'support@example.com', // Replace with config('app.company_email') if available
                'company_phone' => null, // Replace with config('app.company_phone') if available
                'notification_email' => 'notifications@example.com',
                'default_currency_id' => Currency::first()->id ?? 1, // Assume Currency 1 exists, otherwise fix logic
                'default_currency_position' => 'prefix',
                'footer_text' => config('app.name', 'Therapy Management App').' © '.date('Y'),
                'company_address' => null, // Replace with config('app.company_address') if available
                'metadata' => ['default_language' => config('app.locale', 'en')],
            ]
        );

        // 2. Hydrate form properties from the model
        $this->company_name = $this->settings->company_name;
        $this->company_email = $this->settings->company_email;
        $this->company_phone = $this->settings->company_phone;
        $this->notification_email = $this->settings->notification_email;
        $this->default_currency_id = $this->settings->default_currency_id;
        $this->default_currency_position = $this->settings->default_currency_position;
        $this->footer_text = $this->settings->footer_text;
        $this->company_address = $this->settings->company_address;

        // Hydrate metadata properties
        $this->default_language = $this->settings->metadata['default_language'] ?? config('app.locale', 'en');

        // 3. Fetch data for select fields
        $this->availableCurrencies = Currency::all();
    }

    public function saveSettings()
    {
        $this->validate();

        $this->settings->update([
            'company_name' => $this->company_name,
            'company_email' => $this->company_email,
            'company_phone' => $this->company_phone,
            'notification_email' => $this->notification_email,
            'default_currency_id' => $this->default_currency_id,
            'default_currency_position' => $this->default_currency_position,
            'footer_text' => $this->footer_text,
            'company_address' => $this->company_address,
            'metadata' => [
                'default_language' => $this->default_language,
            ],
        ]);

        session()->flash('success', 'Application settings updated successfully!');

        // This is important for Livewire to know the single record changed
        $this->settings = $this->settings->fresh();
    }

    public function render()
    {
        return view('livewire.app-settings');
    }
}
