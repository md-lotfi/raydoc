@php
    $headers = [
        ['key' => 'id', 'label' => '#'],
        [
            'key' => 'icdCode.code',
            'label' => 'ICD Code',
            'sortable' => true,
        ],
        [
            'key' => 'icdCode.description',
            'label' => 'Diagnosis',
            'sortable' => false, // Descriptions are often too long/complex to sort on easily
        ],
        [
            'key' => 'type',
            'label' => 'Type',
        ],
        [
            'key' => 'condition_status',
            'label' => 'Status',
        ],
        [
            'key' => 'start_date',
            'label' => 'Start Date',
            'format' => fn($row, $field) => $field ? date('Y-m-d', strtotime($field)) : 'N/A', // Handle null dates
        ],
        [
            'key' => 'user.name',
            'label' => 'Added By',
            'sortable' => false,
        ],
        [
            'key' => 'created_at',
            'label' => 'Recorded On',
            'format' => fn($row, $field) => date('Y-m-d', strtotime($field)),
        ],
    ];
@endphp
<div class="p-4">
    <x-page-header :title="__('Diagnosis')" :subtitle="$patient->first_name . ' ' . $patient->last_name" />
    <div>
        <x-custom-search model="search" placeholder="{{ __('Search by diagnosis...') }}">
            <x-slot:actions>

                {{-- 1. Add Diagnosis Button --}}
                <a href="{{ route('patient.diagnosis.create', ['patient' => $patient->id]) }}">
                    <x-mary-button icon="o-plus" label="Add Diagnosis" class="btn-primary" />
                </a>

                {{-- 2. Export Button --}}
                <x-mary-button icon="o-document-arrow-down" label="Export to PDF" wire:click="exportPdf"
                    class="btn-secondary" />

                {{-- 3. Another Action Button --}}
                <x-mary-button icon="o-funnel" label="Filter" wire:click="toggleFilter" class="btn-ghost" />

            </x-slot:actions>
        </x-custom-search>
    </div>

    @if ($diagnoses->isNotEmpty())*
        <x-mary-table container-class="" :headers="$headers" :rows="$diagnoses" with-pagination>

            {{-- Custom scope for diagnosis Type --}}
            @scope('cell_type', $diagnosis)
                <x-mary-badge :value="$diagnosis->type"
                    class="{{ match ($diagnosis->type) {
                        'Primary' => 'bg-indigo-100 text-indigo-800',
                        'Secondary' => 'bg-yellow-100 text-yellow-800',
                        'Historical' => 'bg-gray-100 text-gray-800',
                        default => 'bg-blue-100 text-blue-800',
                    } }}" />
            @endscope

            {{-- Custom scope for condition Status --}}
            @scope('cell_condition_status', $diagnosis)
                <x-mary-badge :value="$diagnosis->condition_status"
                    class="{{ $diagnosis->condition_status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}" />
            @endscope

            {{-- Actions column (assuming you want edit/delete) --}}
            @scope('actions', $diagnosis, $patient)
                <x-mary-dropdown>
                    <x-mary-menu-item title="Detail" icon="s-pencil-square"
                        link="{{ route('patient.diagnosis.detail', ['diagnosis' => $diagnosis->id]) }}" />
                    <x-mary-menu-item title="Remove" icon="o-trash" wire:click="confirmDelete({{ $diagnosis->id }})" />
                    <x-mary-menu-item title="Edit" icon="s-pencil-square"
                        link="{{ route('patient.diagnosis.edit', ['patient' => $patient->id, 'diagnosis' => $diagnosis->id]) }}" />
                </x-mary-dropdown>
            @endscope

        </x-mary-table>

        <x-mary-modal wire:model="showDeleteModal" title="Confirm Deletion" subtitle="This action cannot be undone."
            separator>
            <div class="py-5 text-lg">
                {{ __('Are you absolutely sure you want to permanently delete this diagnosis?') }}
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.showDeleteModal = false" />
                <x-mary-button label="Delete" wire:click="delete()" class="btn-error" spinner />
            </x-slot:actions>
        </x-mary-modal>
    @else
        <x-alert color="info" title="Patient diagnosis empty">

            <a href="{{ route('patient.diagnosis.create', ['patient' => $patient->id]) }}">
                <flux:button variant="primary" color="indigo">{{ __('Add diagnosis') }}</flux:button>
            </a>
        </x-alert>
    @endif
</div>
