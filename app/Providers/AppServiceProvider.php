<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '100M');
        ini_set('memory_limit', '256M');
        if ($this->app->environment('production') || config('app.url') !== 'http://localhost') {
            URL::forceScheme('https');
        }
        Gate::before(function ($user, $ability) {
            return $user->hasRole(config('constants.ROLES.ADMIN')) ? true : null;
        });

        $this->configureMailSettings();
    }

    /**
     * Load mail settings from database and override config
     */
    protected function configureMailSettings(): void
    {
        // Prevent crashes during migration when table doesn't exist yet
        if (! Schema::hasTable('settings')) {
            return;
        }

        try {
            $settings = $settings = settings();

            if ($settings && isset($settings->metadata['mail'])) {
                $mailConfig = $settings->metadata['mail'];

                if (! empty($mailConfig['host'])) {
                    Config::set('mail.mailers.smtp.host', $mailConfig['host']);
                }
                if (! empty($mailConfig['port'])) {
                    Config::set('mail.mailers.smtp.port', $mailConfig['port']);
                }
                if (! empty($mailConfig['encryption'])) {
                    Config::set('mail.mailers.smtp.encryption', $mailConfig['encryption']);
                }
                if (! empty($mailConfig['username'])) {
                    Config::set('mail.mailers.smtp.username', $mailConfig['username']);
                }
                if (! empty($mailConfig['password'])) {
                    Config::set('mail.mailers.smtp.password', $mailConfig['password']);
                }

                // Override From Address
                if (! empty($mailConfig['from_address'])) {
                    Config::set('mail.from.address', $mailConfig['from_address']);
                }
                if (! empty($mailConfig['from_name'])) {
                    Config::set('mail.from.name', $mailConfig['from_name']);
                }
            }
        } catch (\Exception $e) {
            Log::debug('AppServiceProvider, configureMailSettings '.$e->getMessage());
            // Log error or ignore if DB connection fails (e.g. during deployment)
        }
    }
}
