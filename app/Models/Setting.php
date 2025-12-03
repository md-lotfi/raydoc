<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'json',
    ];

    protected $with = ['currency'];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors for Metadata
    |--------------------------------------------------------------------------
    | These allow you to access metadata fields directly as if they were columns.
    | Example: $setting->tax_vat_number
    */

    public function getTaxVatNumberAttribute()
    {
        return $this->metadata['tax_vat_number'] ?? null;
    }

    public function getCompanyRegNumberAttribute()
    {
        return $this->metadata['company_reg_number'] ?? null;
    }

    public function getLegalDisclaimerAttribute()
    {
        return $this->metadata['legal_disclaimer'] ?? null;
    }

    /**
     * Get notification preferences with default fallbacks.
     * Usage: $setting->notifications['patient_booking']
     */
    public function getNotificationsAttribute()
    {
        $defaults = [
            'patient_booking' => true,
            'patient_invoice' => true,
            'doctor_assignment' => true,
            'admin_payment' => false,
        ];

        return array_merge($defaults, $this->metadata['notifications'] ?? []);
    }

    /**
     * Get system configuration with default fallbacks.
     * Usage: $setting->system['working_hours_start']
     */
    public function getSystemAttribute()
    {
        $defaults = [
            'default_language' => config('app.locale'),
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'appointment_buffer' => 15,
        ];

        return array_merge($defaults, $this->metadata['system'] ?? []);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if a specific notification type is enabled.
     * Example: $setting->shouldNotify('patient_booking')
     */
    public function shouldNotify(string $type): bool
    {
        return $this->notifications[$type] ?? false;
    }

    /**
     * Get the working hours as a formatted string (e.g., "09:00 - 17:00")
     */
    public function getWorkingHoursString(): string
    {
        return ($this->system['working_hours_start'] ?? '09:00').' - '.($this->system['working_hours_end'] ?? '17:00');
    }
}
