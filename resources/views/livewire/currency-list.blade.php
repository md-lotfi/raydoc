@php
    $headers = [
        ['key' => 'id', 'label' => '#'],
        [
            'key' => 'currency_name',
            'label' => 'Name',
            'sortable' => true,
        ],
        [
            'key' => 'code',
            'label' => 'Code',
            'sortable' => true,
        ],
        [
            'key' => 'symbol',
            'label' => 'Symbol',
            'sortable' => false,
        ],
        [
            'key' => 'thousand_separator',
            'label' => 'Thousand Sep.',
            'sortable' => false,
        ],
        [
            'key' => 'decimal_separator',
            'label' => 'Decimal Sep.',
            'sortable' => false,
        ],
        [
            'key' => 'exchange_rate',
            'label' => 'Exchange Rate (vs Base)',
            'sortable' => true,
        ],
    ];
@endphp

<div>
    <x-page-header :title="__('Currencies')" :subtitle="__('Manage currency codes, symbols, and exchange rates')" />

    <div>
        <x-custom-search model="search" placeholder="{{ __('Search by code or name...') }}">
            <x-slot:actions>
                {{-- Button to create a new currency --}}
                <a href="{{ route('settings.currency.create') }}">
                    <x-mary-button icon="o-plus" label="Add New Currency" class="btn-primary" />
                </a>
            </x-slot:actions>
        </x-custom-search>
    </div>

    <x-mary-table :headers="$headers" :rows="$currencies" with-pagination>

        {{-- Custom scope for Exchange Rate --}}
        @scope('cell_exchange_rate', $currency)
            @if ($currency->exchange_rate)
                <x-mary-badge :value="number_format($currency->exchange_rate, 4)" class="bg-blue-100 text-blue-800" />
            @else
                <span class="text-gray-500">N/A (Base)</span>
            @endif
        @endscope

        {{-- Actions column for editing/deleting --}}
        @scope('actions', $currency)
            <div class="flex space-x-2">
                <a href="{{ route('settings.currency.edit', ['currency' => $currency->id]) }}">
                    <x-mary-button icon="s-pencil-square" class="btn-sm" />
                </a>
                <x-mary-button icon="o-trash" wire:click="confirmDelete({{ $currency->id }})" spinner
                    class="btn-sm btn-error" />
            </div>
        @endscope

    </x-mary-table>

    {{-- Delete Confirmation Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirm Deletion" separator>
        <div class="py-5 text-lg">
            {{ __('Are you sure you want to delete this currency? This action cannot be undone and may affect billing history.') }}
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="Delete" wire:click="delete()" class="btn-error" spinner />
        </x-slot:actions>
    </x-mary-modal>

</div>
