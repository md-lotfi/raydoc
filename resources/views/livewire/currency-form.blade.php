<div class="max-w-5xl mx-auto space-y-8">

    {{-- 🟢 HEADER --}}
    <x-page-header title="{{ $currency ? __('Edit Currency') : __('Add Currency') }}"
        subtitle="{{ $currency ? __('Update settings for :code', ['code' => $code]) : __('Define a new supported currency.') }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" link="{{ route('settings.currency.list') }}" class="btn-ghost" />
            <x-mary-button label="{{ __('Save Currency') }}" icon="o-check" class="btn-primary" wire:click="save"
                spinner="save" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 📝 LEFT COLUMN: Identity --}}
        <div class="lg:col-span-2 space-y-6">
            <x-mary-card title="{{ __('Identification') }}" subtitle="{{ __('Basic details used for billing.') }}"
                shadow separator>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-mary-input label="{{ __('Currency Name') }}" wire:model="currency_name" icon="o-banknotes"
                        placeholder="{{ __('e.g. United States Dollar') }}" />

                    <x-mary-input label="{{ __('ISO Code') }}" wire:model="code" icon="o-tag"
                        placeholder="{{ __('e.g. USD') }}" class="uppercase" />
                </div>

                <div class="mt-4">
                    <x-mary-input label="{{ __('Symbol') }}" wire:model="symbol" class="w-1/2"
                        placeholder="{{ __('e.g. $') }}" hint="{{ __('Appears in invoices') }}" />
                </div>
            </x-mary-card>

            <x-mary-card title="{{ __('Exchange Logic') }}" separator shadow>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <x-mary-input label="{{ __('Exchange Rate') }}" wire:model="exchange_rate" type="number"
                        step="0.0001" icon="o-arrow-path" hint="{{ __('Relative to your base system currency.') }}" />

                    @if ((float) $exchange_rate === 1.0)
                        <div class="alert alert-info text-sm shadow-sm">
                            <x-mary-icon name="o-information-circle" />
                            <span>{{ __('A rate of 1.00 indicates this is your Base Currency.') }}</span>
                        </div>
                    @endif
                </div>
            </x-mary-card>
        </div>

        {{-- 🎨 RIGHT COLUMN: Formatting & Preview --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Formatting Controls --}}
            <x-mary-card title="{{ __('Number Formatting') }}" separator shadow>
                <div class="grid grid-cols-2 gap-4">
                    <x-mary-input label="{{ __('Thousand Sep.') }}" wire:model.live="thousand_separator"
                        class="text-center font-mono text-lg" maxlength="1" />
                    <x-mary-input label="{{ __('Decimal Sep.') }}" wire:model.live="decimal_separator"
                        class="text-center font-mono text-lg" maxlength="1" />
                </div>
            </x-mary-card>

            {{-- Live Preview Card --}}
            <x-mary-card title="{{ __('Live Preview') }}" class="bg-base-200/50 border border-base-200" shadow>
                <div class="text-center py-6">
                    <div class="text-xs uppercase text-gray-500 font-bold mb-2">{{ __('How it looks') }}</div>

                    <div class="text-3xl font-black text-primary">
                        {{ $symbol }} 1{{ $thousand_separator }}234{{ $decimal_separator }}56
                    </div>

                    <div class="mt-4 text-sm text-gray-400">
                        {{ __('(One thousand two hundred thirty-four)') }}
                    </div>
                </div>
            </x-mary-card>

            {{-- Save (Mobile) --}}
            <div class="lg:hidden">
                <x-mary-button label="{{ __('Save Changes') }}" icon="o-check" class="btn-primary w-full"
                    wire:click="save" />
            </div>
        </div>

    </div>
</div>
