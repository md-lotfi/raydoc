<div class="max-w-5xl mx-auto space-y-6">

    <x-page-header title="{{ __('System Configuration') }}"
        subtitle="{{ __('Manage global settings, branding, and automation rules.') }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Save Changes') }}" icon="o-check" class="btn-primary" wire:click="saveSettings"
                spinner="saveSettings" />
        </x-slot:actions>
    </x-page-header>

    <x-mary-tabs wire:model="selectedTab" active-class="bg-primary text-white rounded-t-lg" label-class="font-semibold">

        {{-- 🏢 TAB 1: IDENTITY --}}
        <x-mary-tab name="identity" label="{{ __('Branding') }}" icon="o-building-office">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                {{-- Logo Section --}}
                <div class="md:col-span-1">
                    <x-mary-card title="{!! __('Company Logo') !!}" class="text-center" shadow>
                        <div class="flex justify-center mb-4">
                            @if ($logo_upload)
                                <img src="{{ $logo_upload->temporaryUrl() }}"
                                    class="h-32 object-contain border rounded-lg p-2" />
                            @elseif ($site_logo)
                                <img src="{{ asset($site_logo) }}" class="h-32 object-contain border rounded-lg p-2" />
                            @else
                                <div
                                    class="h-32 w-32 bg-base-200 rounded-lg flex items-center justify-center text-gray-400">
                                    <x-mary-icon name="o-photo" class="w-12 h-12" />
                                </div>
                            @endif
                        </div>
                        <x-mary-file wire:model="logo_upload" accept="image/png, image/jpeg" crop-after-change>
                            <x-slot:label>
                                <span class="btn btn-sm btn-outline w-full">{{ __('Change Logo') }}</span>
                            </x-slot:label>
                        </x-mary-file>
                    </x-mary-card>
                </div>

                {{-- Contact Details --}}
                <div class="md:col-span-2 space-y-4">
                    <x-mary-card title="{!! __('Company Information') !!}" shadow>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-mary-input label="{{ __('Company Name') }}" wire:model="company_name"
                                icon="o-building-storefront" />
                            <x-mary-input label="{{ __('Official Email') }}" wire:model="company_email"
                                icon="o-envelope" />
                            <x-mary-input label="{{ __('Phone Number') }}" wire:model="company_phone" icon="o-phone" />
                            <x-mary-input label="{{ __('System Alerts Email') }}" wire:model="notification_email"
                                icon="o-bell" />
                            <div class="md:col-span-2">
                                <x-mary-textarea label="{{ __('Physical Address') }}" wire:model="company_address"
                                    rows="2" icon="o-map-pin" />
                            </div>
                        </div>
                    </x-mary-card>
                </div>
            </div>
        </x-mary-tab>

        {{-- ⚖️ TAB 2: LEGAL & FINANCE --}}
        <x-mary-tab name="finance" label="{{ __('Legal & Finance') }}" icon="o-banknotes">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <x-mary-card title="{{ __('Legal Information') }}" shadow>
                    <div class="space-y-4">
                        <x-mary-input label="{{ __('Tax / VAT Number') }}" wire:model="tax_vat_number"
                            icon="o-receipt-percent" />
                        <x-mary-input label="{!! __('Company Registration #') !!}" wire:model="company_reg_number"
                            icon="o-identification" />
                        <x-mary-textarea label="{{ __('Invoice Disclaimer') }}" wire:model="legal_disclaimer"
                            rows="3" />
                    </div>
                </x-mary-card>

                <x-mary-card title="{{ __('Currency Defaults') }}" shadow>
                    <div class="space-y-4">
                        <x-mary-select label="{{ __('Default Currency') }}" :options="$availableCurrencies" option-value="id"
                            option-label="code" wire:model="default_currency_id" icon="o-currency-dollar" />
                        <x-mary-select label="{{ __('Format Position') }}" :options="$currencyPositions"
                            wire:model="default_currency_position" />
                        <x-mary-input label="{{ __('Footer Text') }}" wire:model="invoice_footer_text" />
                    </div>
                </x-mary-card>
            </div>
        </x-mary-tab>

        {{-- ✉️ TAB 3: EMAIL SETTINGS --}}
        <x-mary-tab name="email" label="{{ __('Email') }}" icon="o-envelope">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

                {{-- SMTP Configuration --}}
                <x-mary-card title="{{ __('SMTP Configuration') }}"
                    subtitle="{{ __('Configure your outgoing mail server.') }}" shadow>
                    <div class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <x-mary-input label="{{ __('Host') }}" wire:model="mail_host"
                                    placeholder="smtp.example.com" icon="o-server" />
                            </div>
                            <div>
                                <x-mary-input label="{{ __('Port') }}" wire:model="mail_port" placeholder="587" />
                            </div>
                        </div>

                        <x-mary-select label="{{ __('Encryption') }}" wire:model="mail_encryption" :options="$encryptionOptions" />

                        <x-mary-input label="{{ __('Username') }}" wire:model="mail_username" icon="o-user" />
                        <x-mary-input label="{{ __('Password') }}" wire:model="mail_password" type="password"
                            icon="o-key" />
                    </div>
                </x-mary-card>

                {{-- Sender Identity --}}
                <x-mary-card title="{!! __('Sender Identity') !!}" subtitle="{{ __('How emails appear to recipients.') }}"
                    shadow>
                    <div class="space-y-4">
                        <x-mary-input label="{{ __('From Name') }}" wire:model="mail_from_name" icon="o-user-circle"
                            hint="{{ __('e.g. Raydoc Support') }}" />
                        <x-mary-input label="{!! __('From Address') !!}" wire:model="mail_from_address"
                            icon="o-at-symbol" hint="{{ __('e.g. no-reply@raydoc.com') }}" />

                        <div class="alert alert-info text-sm mt-4">
                            <x-mary-icon name="o-information-circle" />
                            <span>{{ __('Note: Updates here override the .env file settings.') }}</span>
                        </div>
                    </div>
                </x-mary-card>
            </div>
        </x-mary-tab>

        {{-- 🔔 TAB 4: NOTIFICATIONS --}}
        <x-mary-tab name="notifications" label="{{ __('Notifications') }}" icon="o-bell-alert">
            <div class="mt-6">
                <x-mary-card title="{!! __('Email Automation Rules') !!}" shadow>
                    <div class="space-y-4">
                        <x-mary-toggle label="{{ __('Notify Patient: Booking Confirmation') }}"
                            wire:model="notify_patient_on_booking" class="toggle-primary" right />
                        <x-mary-toggle label="{{ __('Notify Patient: Invoice Generated') }}"
                            wire:model="notify_patient_on_invoice" class="toggle-primary" right />
                        <div class="divider"></div>
                        <x-mary-toggle label="{{ __('Notify Doctor: New Appointment Assigned') }}"
                            wire:model="notify_doctor_on_assignment" class="toggle-secondary" right />
                        <div class="divider"></div>
                        <x-mary-toggle label="{!! __('Notify Admin: Payment Received') !!}" wire:model="notify_admin_on_payment"
                            class="toggle-warning" right />
                    </div>
                </x-mary-card>
            </div>
        </x-mary-tab>

        {{-- ⚙️ TAB 5: SYSTEM --}}
        <x-mary-tab name="system" label="{{ __('System') }}" icon="o-cog-6-tooth">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">


                {{-- Scheduling --}}
                <x-mary-card title="{{ __('Scheduling Logic') }}" shadow>

                    {{-- ✅ NEW: Default Operation Mode --}}
                    <div class="mb-6 border-b border-base-200 pb-6">
                        <x-mary-select label="{{ __('Default Booking Mode') }}" wire:model="default_session_type"
                            :options="[
                                ['id' => 'appointment', 'name' => __('Scheduled Appointments')],
                                ['id' => 'queue', 'name' => __('Walk-in / Queue')],
                            ]" icon="o-queue-list"
                            hint="{{ __('Which mode should be selected by default when creating a new session?') }}" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <x-mary-input label="{{ __('Day Start') }}" type="time"
                            wire:model="working_hours_start" />
                        <x-mary-input label="{{ __('Day End') }}" type="time" wire:model="working_hours_end" />
                    </div>

                    <div class="mt-4">
                        <x-mary-range label="{{ __('Appointment Buffer (min)') }}"
                            wire:model="appointment_buffer_minutes" min="0" max="60" step="5"
                            hint="{{ $appointment_buffer_minutes . ' minutes between sessions' }}" />
                    </div>

                </x-mary-card>

                <x-mary-card title="{{ __('Regional') }}" shadow>
                    <x-mary-select label="{{ __('Default System Language') }}" wire:model="default_language"
                        :options="[
                            ['id' => 'en', 'name' => 'English'],
                            ['id' => 'fr', 'name' => 'French'],
                            ['id' => 'ar', 'name' => 'Arabic'],
                        ]" />
                </x-mary-card>
            </div>
        </x-mary-tab>

    </x-mary-tabs>
</div>
