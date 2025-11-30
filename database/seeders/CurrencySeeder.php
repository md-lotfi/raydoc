<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::create([
            'currency_name' => __('Algerian Dinars'),
            'code' => Str::upper('DZD'),
            'symbol' => 'DA',
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'exchange_rate' => null,
        ]);
        Currency::create([
            'currency_name' => __('US Dollar'),
            'code' => Str::upper('USD'),
            'symbol' => '$',
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'exchange_rate' => null,
        ]);
        Currency::create([
            'currency_name' => __('Euro'),
            'code' => Str::upper('Euro'),
            'symbol' => '€',
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'exchange_rate' => null,
        ]);

    }
}
