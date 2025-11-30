<div>

    <x-page-header :title="__('Application Settings')" :subtitle="__('Configure company details, billing defaults, and system preferences')" />

    @if (session()->has('success'))
        <x-alert color="success" title="Success" class="mb-4">
            {{ session('success') }}
        </x-alert>
    @endif

    <form wire:submit.prevent="saveSettings" class="space-y-8">

        {{-- COMPANY & CONTACT DETAILS --}}
        <x-mary-card title="Company Identity" shadow separator>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Company Name --}}
                <x-mary-input label="Company Name" wire:model="company_name" required />

                {{-- Footer Text --}}
                <x-mary-input label="Footer Text" wire:model="footer_text" required
                    description="Appears at the bottom of pages and emails (e.g., Copyright notice)." />

                {{-- Company Email --}}
                <x-mary-input label="Contact Email" type="email" wire:model="company_email" required />

                {{-- Company Phone --}}
                <x-mary-input label="Contact Phone" type="tel" wire:model="company_phone" />

                {{-- Notification Email --}}
                <div class="md:col-span-2">
                    <x-mary-input label="System Notification Email" type="email" wire:model="notification_email"
                        required
                        description="Address used for automated system alerts (e.g., low balance, failed jobs)." />
                </div>

                {{-- Company Address --}}
                <div class="md:col-span-2">
                    <x-mary-textarea label="Company Address" wire:model="company_address" rows="3" />
                </div>
            </div>
        </x-mary-card>

        {{-- BILLING & LOCALIZATION DEFAULTS --}}
        <x-mary-card title="Financial and Localization Defaults" shadow separator>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Default Currency --}}
                <div>
                    <x-mary-select label="Default Currency" wire:model="default_currency_id" :options="$availableCurrencies"
                        option-label="code" option-value="id" required />
                    @error('default_currency_id')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Currency Position --}}
                <div>
                    <x-mary-select label="Currency Position" wire:model="default_currency_position" :options="$currencyPositions"
                        required />
                    @error('default_currency_position')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Default Language --}}
                <div>
                    {{-- Note: In a real app, you'd load available locales dynamically --}}
                    <x-mary-input label="Default Language Code" wire:model="default_language" required
                        placeholder="eg., en, es, fr" />
                    @error('default_language')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </x-mary-card>

        {{-- SAVE BUTTON --}}
        <div class="flex justify-end pt-4">
            <x-mary-button type="submit" label="Save All Settings" icon="o-cloud-arrow-up"
                class="btn-primary w-full md:w-auto" spinner="saveSettings" />
        </div>
    </form>
</div>
