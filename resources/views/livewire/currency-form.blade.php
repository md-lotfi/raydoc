<div>

    <x-page-header :title="__('Add New Currency')" :subtitle="__('Define currency codes and formatting rules')" />

    @if (session()->has('success'))
        <x-alert color="success" title="Success" class="mb-4">
            {{ session('success') }}
        </x-alert>
    @endif

    <form wire:submit.prevent="saveCurrency" class="space-y-6">

        <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-accent-700">{{ __('General Details') }}</h3>

        {{-- Name, Code, and Symbol --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Currency Name --}}
            <flux:field>
                <flux:label>{{ __('Currency Name') }}</flux:label>
                <flux:input wire:model="currency_name" required placeholder="{{ __('Eg., United States Dollar') }}" />
                <flux:description>{{ __('Currency name for searching purposes') }}</flux:description>
                <flux:error name="currency_name" />
            </flux:field>

            {{-- Code --}}
            <flux:field>
                <flux:label>{{ __('ISO Code') }}</flux:label>
                <flux:input wire:model="code" required placeholder="Eg., USD" />
                <flux:description>{{ __('3-letter code (must be unique).') }}</flux:description>
                <flux:error name="code" />
            </flux:field>

            {{-- Symbol --}}
            <flux:field>
                <flux:label>{{ __('Symbole') }}</flux:label>
                <flux:input wire:model="symbol" required placeholder="Eg., $" />
                <flux:description>{{ __('Currency symbole such as $, DA, ...') }}</flux:description>
                <flux:error name="symbole" />
            </flux:field>
        </div>

        <h3 class="text-lg font-semibold border-b pb-2 mb-4 pt-4 text-accent-700">{{ __('Formatting and Exchange') }}
        </h3>

        {{-- Separators --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Thousand Separator --}}
            <div>
                <flux:input label="{{ __('Thousand Separator') }}" wire:model="thousand_separator" required
                    placeholder="Eg., ,"
                    description="{{ __('The character used to separate thousands (e.g., 1**>**000**>**000).') }}" />
                @error('thousand_separator')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Decimal Separator --}}
            <div>
                <flux:input label="{{ __('Decimal Separator') }}" wire:model="decimal_separator" required
                    placeholder="Eg., ."
                    description="{{ __('The character used to separate the integer from the fractional part (e.g., 1.99).') }}" />
                @error('decimal_separator')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Exchange Rate --}}
        <div>
            <flux:input label="{{ __('Exchange Rate') }}" type="number" wire:model="exchange_rate" step="0.0001"
                min="0" placeholder="{{ __('Eg., 1.0 (Base) or 0.925 (for EUR vs USD)') }}"
                description="{{ __('Rate compared to the Base Currency of your system (set in settings). Set to 1 for the base currency.') }}" />
            @error('exchange_rate')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Submit Button --}}
        <div class="pt-6">
            <x-mary-button type="submit" label="{{ __('Save Currency') }}" icon="o-check-circle"
                class="btn-primary w-full" spinner="saveCurrency" />
        </div>
    </form>
</div>
