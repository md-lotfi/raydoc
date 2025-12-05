<?php

namespace App\Livewire\Settings;

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class AppSettings extends Component
{
    use Toast, WithFileUploads;

    public Setting $settings;

    public string $selectedTab = 'identity';

    // --- Tab 1: Identity & Branding ---
    public $company_name;

    public $company_email;

    public $company_phone;

    public $company_address;

    public $site_logo;

    public $logo_upload;

    // --- Tab 2: Financial & Legal ---
    public $default_currency_id;

    public $default_currency_position;

    public $tax_vat_number;

    public $company_reg_number;

    public $invoice_footer_text;

    public $legal_disclaimer;

    // --- Tab 3: Notifications & Automation ---
    public $notification_email;

    public $notify_patient_on_booking = false;

    public $notify_patient_on_invoice = false;

    public $notify_doctor_on_assignment = false;

    public $notify_admin_on_payment = false;

    // --- Tab 4: Email (SMTP) Settings ---
    public $mail_mailer = 'smtp';

    public $mail_host;

    public $mail_port;

    public $mail_username;

    public $mail_password;

    public $mail_encryption;

    public $mail_from_address;

    public $mail_from_name;

    // --- Tab 5: System Fine-Tuning ---
    public $default_language;

    public $working_hours_start;

    public $working_hours_end;

    public $appointment_buffer_minutes;

    public $default_session_type;

    // Options
    public $availableCurrencies;

    public $currencyPositions = [['id' => 'prefix', 'name' => 'Prefix ($100)'], ['id' => 'suffix', 'name' => 'Suffix (100$)']];

    public $encryptionOptions = [['id' => 'tls', 'name' => 'TLS'], ['id' => 'ssl', 'name' => 'SSL'], ['id' => 'null', 'name' => 'None']];

    protected function rules()
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'notification_email' => 'required|email|max:255',
            'logo_upload' => 'nullable|image|max:1024',
            'default_currency_id' => 'required',
            'working_hours_start' => 'required',
            'working_hours_end' => 'required|after:working_hours_start',

            // SMTP Rules
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|numeric',
            'mail_username' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'nullable|email',
        ];
    }

    public function mount()
    {
        $this->settings = Setting::firstOrCreate(['id' => 1], [
            'company_name' => 'Raydoc Clinic',
            'default_currency_id' => 1,
            'default_currency_position' => 'prefix',
            'notification_email' => 'admin@raydoc.com',
            'company_email' => 'contact@raydoc.com',
            'footer_text' => 'Raydoc © '.date('Y'),
            'company_address' => '',
            'metadata' => [],
        ]);

        // Hydrate Standard Fields
        $this->company_name = $this->settings->company_name;
        $this->company_email = $this->settings->company_email;
        $this->company_phone = $this->settings->company_phone;
        $this->company_address = $this->settings->company_address;
        $this->notification_email = $this->settings->notification_email;
        $this->site_logo = $this->settings->site_logo;
        $this->default_currency_id = $this->settings->default_currency_id;
        $this->default_currency_position = $this->settings->default_currency_position;
        $this->invoice_footer_text = $this->settings->footer_text;

        $meta = $this->settings->metadata ?? [];

        // Legal
        $this->tax_vat_number = $meta['tax_vat_number'] ?? '';
        $this->company_reg_number = $meta['company_reg_number'] ?? '';
        $this->legal_disclaimer = $meta['legal_disclaimer'] ?? '';

        // Notifications
        $this->notify_patient_on_booking = $meta['notifications']['patient_booking'] ?? true;
        $this->notify_patient_on_invoice = $meta['notifications']['patient_invoice'] ?? true;
        $this->notify_doctor_on_assignment = $meta['notifications']['doctor_assignment'] ?? true;
        $this->notify_admin_on_payment = $meta['notifications']['admin_payment'] ?? false;

        // SMTP / Email
        $mail = $meta['mail'] ?? [];
        $this->mail_host = $mail['host'] ?? config('mail.mailers.smtp.host');
        $this->mail_port = $mail['port'] ?? config('mail.mailers.smtp.port');
        $this->mail_username = $mail['username'] ?? config('mail.mailers.smtp.username');
        $this->mail_password = $mail['password'] ?? config('mail.mailers.smtp.password'); // Caution with displaying passwords
        $this->mail_encryption = $mail['encryption'] ?? config('mail.mailers.smtp.encryption');
        $this->mail_from_address = $mail['from_address'] ?? config('mail.from.address');
        $this->mail_from_name = $mail['from_name'] ?? config('mail.from.name');

        // System
        $this->default_language = $meta['system']['default_language'] ?? config('app.locale');
        $this->working_hours_start = $meta['system']['working_hours_start'] ?? '09:00';
        $this->working_hours_end = $meta['system']['working_hours_end'] ?? '17:00';
        $this->appointment_buffer_minutes = $meta['system']['appointment_buffer'] ?? 15;

        $this->default_session_type = $meta['system']['default_session_type'] ?? config('constants.SESSION_TYPE')[0];

        $this->availableCurrencies = Currency::all();
    }

    public function saveSettings()
    {
        $this->validate();

        if ($this->logo_upload) {
            if ($this->site_logo && Storage::disk(config('app.default_disk'))->exists($this->site_logo)) {
                Storage::disk(config('app.default_disk'))->delete($this->site_logo);
            }
            $path = $this->logo_upload->store('logos', config('app.default_disk'));
            $this->site_logo = 'storage/'.$path;
        }

        $metadata = [
            'tax_vat_number' => $this->tax_vat_number,
            'company_reg_number' => $this->company_reg_number,
            'legal_disclaimer' => $this->legal_disclaimer,
            'notifications' => [
                'patient_booking' => $this->notify_patient_on_booking,
                'patient_invoice' => $this->notify_patient_on_invoice,
                'doctor_assignment' => $this->notify_doctor_on_assignment,
                'admin_payment' => $this->notify_admin_on_payment,
            ],
            'mail' => [
                'host' => $this->mail_host,
                'port' => $this->mail_port,
                'username' => $this->mail_username,
                'password' => $this->mail_password,
                'encryption' => $this->mail_encryption,
                'from_address' => $this->mail_from_address,
                'from_name' => $this->mail_from_name,
            ],
            'system' => [
                'default_language' => $this->default_language,
                'working_hours_start' => $this->working_hours_start,
                'working_hours_end' => $this->working_hours_end,
                'appointment_buffer' => $this->appointment_buffer_minutes,
                'default_session_type' => $this->default_session_type,
            ],
        ];

        $this->settings->update([
            'company_name' => $this->company_name,
            'company_email' => $this->company_email,
            'company_phone' => $this->company_phone,
            'company_address' => $this->company_address,
            'notification_email' => $this->notification_email,
            'site_logo' => $this->site_logo,
            'default_currency_id' => $this->default_currency_id,
            'default_currency_position' => $this->default_currency_position,
            'footer_text' => $this->invoice_footer_text,
            'metadata' => $metadata,
        ]);

        cache()->forget('settings');

        $this->success(__('Settings Saved'), __('System configuration updated successfully.'));
        $this->logo_upload = null;
    }

    public function render()
    {
        return view('livewire.app-settings');
    }
}
