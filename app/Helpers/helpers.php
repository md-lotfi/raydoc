<?php

use App\Models\Setting;

if (! function_exists('settings')) {
    function settings()
    {
        $settings = cache()->remember('settings', 24 * 60, function () {
            return Setting::firstOrFail();
        });

        return $settings;
    }
}

if (! function_exists('format_currency')) {
    function format_currency($value, $format = true)
    {
        if (! $format) {
            return $value;
        }

        $settings = settings();
        $position = $settings->default_currency_position;
        $symbol = $settings->currency->symbol;
        $decimal_separator = $settings->currency->decimal_separator;
        $thousand_separator = $settings->currency->thousand_separator;

        if ($position == 'prefix') {
            $formatted_value = $symbol.number_format((float) $value, 2, $decimal_separator, $thousand_separator);
        } else {
            $formatted_value = number_format((float) $value, 2, $decimal_separator, $thousand_separator).$symbol;
        }

        return $formatted_value;
    }
}

if (! function_exists('toSelectOptions')) {
    function toSelectOptions(): array
    {
        return collect(config('constants.GENDERS'))
            ->map(fn ($item) => [
                'id' => $item,
                'name' => $item,
            ])
            ->toArray();
    }
}

if (! function_exists('getDoctorPermissions')) {
    function getDoctorPermissions(): array
    {
        return [
            'view patients',
            'create patients',
            'edit patients',
            'show patients health folder',
            'view sessions',
            'create sessions',
            'edit sessions',
            'manage diagnoses',
            'view reports',
        ];
    }
}

if (! function_exists('getAssistantPermissions')) {
    function getAssistantPermissions(): array
    {
        return [
            'view patients',
            'create patients',
            'edit patients',
            'show patients health folder',
            'view sessions',
            'create sessions',
            'edit sessions',
            'manage appointments',
            'manage invoices',
            'view payments',
        ];
    }
}

if (! function_exists('getAdminPermissions')) {
    function getAdminPermissions(): array
    {
        $p = [
            // Dashboard
            'view_dashboard',
            // Profile
            'view_profile',
            'edit_profile',
            'change_password',
            // Logout
            'logout',
        ];

        return array_merge($p, [getDoctorPermissions(), getAssistantPermissions()]);
    }
}
