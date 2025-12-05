<div class="space-y-6">

    {{-- 🟢 PAGE HEADER --}}
    <x-page-header title="{{ __('Currencies') }}" subtitle="{{ __('Manage supported currencies and exchange rates.') }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Add Currency') }}" icon="o-plus" class="btn-primary"
                link="{{ route('settings.currency.create') }}" />
        </x-slot:actions>
    </x-page-header>

    {{-- 🎛️ CONTROLS --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search code or name...') }}"
                wire:model.live.debounce.300ms="search" class="w-full" />
        </div>

        <div class="flex items-center gap-2 text-sm text-gray-500">
            <x-mary-icon name="o-banknotes" class="w-4 h-4" />
            <span>{{ $currencies->total() }} {{ __('Currencies Configured') }}</span>
        </div>
    </div>

    {{-- 📋 TABLE --}}
    <x-mary-card shadow class="bg-base-100">
        <x-mary-table container-class="" :headers="$this->headers()" :rows="$currencies" :sort-by="$sortBy" :link="route('settings.currency.edit', ['currency' => '[id]'])"
            class="cursor-pointer hover:bg-base-50" with-pagination>

            {{-- 🏷️ Code --}}
            {{-- ✅ FIX: Pass $defaultCurrencyId as the 3rd argument here --}}
            @scope('cell_code', $currency, $defaultCurrencyId)
                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-lg">{{ $currency->code }}</span>
                    @if ($currency->id === $defaultCurrencyId)
                        <span class="badge badge-xs badge-primary">{{ __('Default') }}</span>
                    @endif
                </div>
            @endscope

            {{-- 💱 Exchange Rate --}}
            @scope('cell_exchange_rate', $currency)
                <div class="font-mono">
                    {{ number_format($currency->exchange_rate, 4) }}
                </div>
            @endscope

            {{-- 🔢 Formatting Preview --}}
            @scope('cell_formatting', $currency)
                <span class="text-gray-400 text-xs">
                    1{{ $currency->thousand_separator }}000{{ $currency->decimal_separator }}00
                </span>
            @endscope

            {{-- ⚙️ Actions --}}
            @scope('actions', $currency, $defaultCurrencyId)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-sm btn-ghost btn-circle" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('Edit') }}" icon="o-pencil"
                            link="{{ route('settings.currency.edit', $currency->id) }}" />

                        @if ($currency->id !== $defaultCurrencyId)
                            <x-mary-menu-item title="{{ __('Delete') }}" icon="o-trash" class="text-error"
                                wire:click="confirmDelete({{ $currency->id }})" />
                        @else
                            <x-mary-menu-item title="{{ __('Default (Cannot Delete)') }}" icon="o-lock-closed"
                                class="text-gray-400 cursor-not-allowed opacity-50" />
                        @endif
                    </x-mary-dropdown>
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- 🗑️ Delete Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="{{ __('Delete Currency') }}" class="backdrop-blur">
        <div class="mb-5 text-gray-600">
            {{ __('Are you sure you want to delete this currency? Historical records using this currency may display incorrectly.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Confirm Delete') }}" wire:click="delete" class="btn-error" spinner />
        </x-slot:actions>
    </x-mary-modal>

</div>
