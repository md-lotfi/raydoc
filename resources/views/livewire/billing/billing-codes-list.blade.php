@php
    $headers = [
        ['key' => 'id', 'label' => '#'],
        [
            'key' => 'code',
            'label' => 'Code',
            'sortable' => true,
        ],
        [
            'key' => 'name',
            'label' => 'Service Name',
            'sortable' => true,
        ],
        [
            'key' => 'standard_rate',
            'label' => 'Standard Rate',
            'sortable' => true,
        ],
        [
            'key' => 'min_duration_minutes',
            'label' => 'Min Duration',
            'sortable' => true,
        ],
        [
            'key' => 'max_duration_minutes',
            'label' => 'Max Duration',
            'sortable' => true,
        ],
        [
            'key' => 'is_active',
            'label' => 'Status',
        ],
        [
            'key' => 'description',
            'label' => 'Description',
            'sortable' => false,
        ],
    ];
@endphp

<div>
    <x-page-header :title="__('Billing Codes')" :subtitle="__('Manage CPT and service rates')" />

    <div>
        <x-custom-search model="search" placeholder="{{ __('Search code or name...') }}">
            <x-slot:actions>
                {{-- Button to create a new billing code --}}
                <a href="{{ route('billing.codes.create') }}">
                    <x-mary-button icon="o-plus" label="Add New Code" class="btn-primary" />
                </a>

                <x-mary-button icon="o-document-arrow-down" label="Export Rates" wire:click="exportRates"
                    class="btn-secondary" />
            </x-slot:actions>
        </x-custom-search>
    </div>

    <x-mary-table :headers="$headers" :rows="$billingCodes" with-pagination>

        {{-- Custom scope for Standard Rate (formatting the decimal) --}}
        @scope('cell_standard_rate', $code)
            <x-mary-badge :value="format_currency($code->standard_rate)" class="bg-green-100 text-green-800 font-bold" />
        @endscope

        {{-- Custom scope for Min/Max Duration (cleaner display) --}}
        @scope('cell_min_duration_minutes', $code)
            {{ $code->min_duration_minutes ? $code->min_duration_minutes . ' min' : 'N/A' }}
        @endscope

        @scope('cell_max_duration_minutes', $code)
            {{ $code->max_duration_minutes ? $code->max_duration_minutes . ' min' : 'N/A' }}
        @endscope

        {{-- Custom scope for Active Status (Crucial for service eligibility) --}}
        @scope('cell_is_active', $code)
            <x-mary-badge wire:click="toggleActive({{ $code->id }})" :value="$code->is_active ? 'Active' : 'Inactive'"
                class="{{ $code->is_active ? 'bg-indigo-100 text-indigo-800 cursor-pointer' : 'bg-red-100 text-red-800 cursor-pointer' }}"
                tooltip="{{ $code->is_active ? 'Click to deactivate' : 'Click to activate' }}" />
        @endscope

        {{-- Actions column for editing/deleting codes --}}
        @scope('actions', $code)
            <div class="flex space-x-2">
                <a href="{{ route('billing.codes.edit', ['billingCode' => $code->id]) }}">
                    <x-mary-button icon="s-pencil-square" class="btn-sm" />
                </a>
                <x-mary-button icon="o-trash" wire:click="confirmDelete({{ $code->id }})" spinner class="btn-sm" />
            </div>
        @endscope

    </x-mary-table>

    {{-- Delete Modal Placeholder --}}
    {{-- You will need a simple delete confirmation modal here --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirm Deletion" separator>
        {{-- Modal content and actions here --}}
    </x-mary-modal>

</div>
