<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'company_name' => config('app.name'),
            'company_email' => config('app.company_email'),
            'company_phone' => config('app.company_phone'),
            'notification_email' => 'notification@test.com',
            'default_currency_id' => 1,
            'default_currency_position' => 'suffix',
            'footer_text' => config('app.name').' © 2025',
            'company_address' => config('app.company_address'),
            'metadata' => ['default_language' => config('app.locale')],
        ]);
    }
}
