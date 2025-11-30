@php
    $headers = [
        ['key' => 'id', 'label' => '#'],
        ['key' => 'avatar', 'label' => 'Avatar'],
        ['key' => 'email', 'label' => 'E-mail', 'sortable' => false], // <--- Won't be sortable
    [
        'key' => 'is_active',
        'label' => 'Status',
    ],
    [
        'key' => 'user.name',
        'label' => 'Added by',
        'sortable' => false,
    ],
    [
        'key' => 'created_at',
        'label' => 'Created',
        'sortable' => false,
        'format' => fn($row, $field) => date('Y-m-d', strtotime($field)),
        ],
    ];
@endphp
<div class="p-4">
    <x-page-header :title="__('Patient list')" :subtitle="__('Display and manage patients')" />
    <div>
        <x-custom-search model="search" placeholder="{{ __('Search by name, email, or phone...') }}">
            <x-slot:actions>

                {{-- 1. Add Diagnosis Button --}}
                <a href="{{ route('patient.create') }}">
                    <x-mary-button icon="o-plus" label="Add Patient" class="btn-primary" />
                </a>

                {{-- 2. Export Button --}}
                <x-mary-button icon="o-document-arrow-down" label="Export to PDF" wire:click="exportPdf"
                    class="btn-secondary" />

                {{-- 3. Another Action Button --}}
                <x-mary-button icon="o-funnel" label="Filter" wire:click="toggleFilter" class="btn-ghost" />



            </x-slot:actions>
        </x-custom-search>
    </div>
    <x-mary-table container-class="" :headers="$headers" :rows="$patients" with-pagination>
        @scope('cell_avatar', $patient)
            <x-mary-avatar :image="$patient->avatar" :title="$patient->first_name . ' ' . $patient->last_name" :subtitle="$patient->address . ', ' . $patient->city" class="!w-10" />
        @endscope

        @scope('cell_is_active', $patient)
            <x-mary-badge wire:click="togglePatientStatus({{ $patient->id }})" :value="$patient->is_active ? 'Active' : 'Inactive'"
                class="
            {{ $patient->is_active
                ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'
                : 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' }}" />
        @endscope

        @scope('actions', $patient)
            <x-mary-dropdown>
                <x-mary-menu-item title="Health Folder" icon="s-folder"
                    link="{{ route('patient.health.folder', ['patient' => $patient->id]) }}" />
                <x-mary-menu-item title="Remove" icon="o-trash" wire:click="confirmDelete({{ $patient->id }})" />
                <x-mary-menu-item title="Edit" icon="s-pencil-square"
                    link="{{ route('patient.edit', ['id' => $patient->id]) }}" />
                <x-mary-menu-item title="Diagnoses" icon="fas.heartbeat"
                    link="{{ route('patient.diagnosis.list', ['patient' => $patient->id]) }}" />
                <x-mary-menu-item title="Therapy sessions" icon="fas.thermometer"
                    link="{{ route('patient.session.list', ['patient' => $patient->id]) }}" />
                <x-mary-menu-item title="Generate Invoice" icon="s-banknotes"
                    link="{{ route('invoice.generate', ['patient' => $patient->id]) }}" />
            </x-mary-dropdown>
        @endscope
    </x-mary-table>
    <x-mary-modal wire:model="showDeleteModal" title="Confirm Deletion" subtitle="This action cannot be undone."
        separator>
        <div class="py-5 text-lg">
            {{ __('Are you absolutely sure you want to permanently delete this patient?') }}
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="Delete" wire:click="delete()" class="btn-error" spinner />
        </x-slot:actions>
    </x-mary-modal>
</div>
