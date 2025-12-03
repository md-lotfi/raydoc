@php
    // Translatable Headers
    $headers = [
        ['key' => 'id', 'label' => __('#'), 'class' => 'w-1'],
        ['key' => 'avatar', 'label' => __('Patient'), 'sortable' => false],
        ['key' => 'email', 'label' => __('Contact'), 'sortable' => false],
        ['key' => 'gender', 'label' => __('Gender')],
        ['key' => 'is_active', 'label' => __('Status'), 'class' => 'text-center'],
        ['key' => 'created_at', 'label' => __('Joined'), 'class' => 'hidden lg:table-cell'],
    ];

    // Translatable Filter Options
    $statusOptions = [['id' => '1', 'name' => __('Active Only')], ['id' => '0', 'name' => __('Inactive Only')]];
    $genderOptions = [['id' => 'Male', 'name' => __('Male')], ['id' => 'Female', 'name' => __('Female')]];
@endphp

<div class="space-y-6">

    {{-- 🟢 PAGE HEADER --}}
    <x-page-header title="{{ __('Patients') }}" subtitle="{{ __('Manage your patient records and history.') }}"
        separator />

    {{-- 📊 Header with Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-mary-stat title="{{ __('Total Patients') }}" value="{{ $stats['total'] }}" icon="o-users"
            class="bg-base-100 shadow-sm border border-base-200" />
        <x-mary-stat title="{{ __('Active') }}" value="{{ $stats['active'] }}" icon="o-check-circle"
            class="bg-base-100 shadow-sm border border-base-200" color="text-success" />
        <x-mary-stat title="{{ __('Inactive') }}" value="{{ $stats['inactive'] }}" icon="o-archive-box"
            class="bg-base-100 shadow-sm border border-base-200" color="text-gray-400" />
    </div>

    {{-- 🎛️ Controls Bar --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">

        {{-- Search --}}
        <div class="w-full md:w-1/2">
            <x-mary-input wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
                placeholder="{{ __('Search name, email, phone...') }}" class="w-full" />
        </div>

        {{-- Actions --}}
        <div class="flex gap-2 w-full md:w-auto justify-end">
            <x-mary-button icon="o-funnel" label="{{ __('Filters') }}"
                class="btn-ghost {{ $statusFilter !== null || $genderFilter ? 'text-primary' : '' }}"
                wire:click="$toggle('showFilterDrawer')"
                badge="{{ ($statusFilter !== null ? 1 : 0) + ($genderFilter ? 1 : 0) ?: null }}" />

            <x-mary-button icon="o-arrow-down-tray" wire:click="exportCsv" class="btn-ghost"
                tooltip="{{ __('Export CSV') }}" />

            <a href="{{ route('patient.create') }}">
                <x-mary-button icon="o-plus" label="{{ __('Add Patient') }}" class="btn-primary" />
            </a>
        </div>
    </div>

    {{-- 📋 Main Table --}}
    <x-mary-card shadow class="bg-base-100">
        <x-mary-table container-class="" :headers="$headers" :rows="$patients" :sort-by="$sortBy"
            @row-click="$wire.showPatientDetails($event.detail.id)" class="cursor-pointer hover:bg-base-50"
            with-pagination>

            {{-- Custom Avatar + Name Cell --}}
            @scope('cell_avatar', $patient)
                <div class="flex items-center gap-3">
                    <x-mary-avatar :image="$patient->avatar" :title="$patient->first_name" class="!w-10 !h-10" />
                    <div>
                        <div class="font-bold">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                        <div class="text-xs text-gray-500">{{ $patient->phone_number ?? __('No phone') }}</div>
                    </div>
                </div>
            @endscope

            {{-- Custom Contact Cell --}}
            @scope('cell_email', $patient)
                <div class="text-sm">{{ $patient->email }}</div>
                <div class="text-xs text-gray-400">{{ $patient->city ?? '-' }}</div>
            @endscope

            {{-- Custom Gender Cell --}}
            @scope('cell_gender', $patient)
                {{ __($patient->gender) }}
            @endscope

            {{-- Custom Status Badge --}}
            @scope('cell_is_active', $patient)
                <div @click.stop>
                    <x-mary-badge value="{{ $patient->is_active ? __('Active') : __('Inactive') }}"
                        class="cursor-pointer {{ $patient->is_active ? 'badge-success/20 text-success' : 'badge-ghost text-gray-400' }}"
                        wire:click="togglePatientStatus({{ $patient->id }})" />
                </div>
            @endscope

            {{-- Actions Menu --}}
            @scope('actions', $patient)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-mary-menu-item title="{{ __('Health Folder') }}" icon="o-folder-open"
                            link="{{ route('patient.health.folder', $patient->id) }}" />

                        {{-- ✅ RESTORED: Link to Diagnosis List --}}
                        <x-mary-menu-item title="{{ __('Diagnoses') }}" icon="o-clipboard-document-list"
                            link="{{ route('patient.diagnosis.list', $patient->id) }}" />

                        <x-mary-menu-item title="{{ __('Edit Details') }}" icon="o-pencil"
                            link="{{ route('patient.edit', $patient->id) }}" />
                        <x-mary-menu-item title="{{ __('Delete') }}" icon="o-trash" class="text-error"
                            wire:click="confirmDelete({{ $patient->id }})" />
                    </x-mary-dropdown>
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- 🕵️‍♂️ Filter Drawer --}}
    <x-mary-drawer wire:model="showFilterDrawer" title="{{ __('Filter Patients') }}" right separator with-close-button
        class="w-11/12 lg:w-1/3">
        <div class="space-y-4 p-4">
            <x-mary-select label="{{ __('Status') }}" :options="$statusOptions" wire:model.live="statusFilter"
                placeholder="{{ __('All Statuses') }}" />
            <x-mary-select label="{{ __('Gender') }}" :options="$genderOptions" wire:model.live="genderFilter"
                placeholder="{{ __('All Genders') }}" />

            <div class="pt-4">
                <x-mary-button label="{{ __('Reset Filters') }}" icon="o-x-mark" wire:click="clearFilters"
                    class="btn-outline w-full" />
            </div>
        </div>
    </x-mary-drawer>

    {{-- 👁️ Patient Quick View Drawer --}}
    <x-mary-drawer wire:model="showDetailsDrawer" title="{{ __('Patient Overview') }}" right separator
        with-close-button class="w-11/12 lg:w-1/3">
        @if ($selectedPatient)
            <div class="text-center py-6">
                <x-mary-avatar :image="$selectedPatient->avatar" :title="$selectedPatient->first_name" class="!w-24 !h-24 mx-auto mb-4" />
                <h3 class="text-xl font-bold">{{ $selectedPatient->first_name }} {{ $selectedPatient->last_name }}
                </h3>
                <p class="text-gray-500">{{ $selectedPatient->email }}</p>
                <div class="mt-2">
                    <span class="badge {{ $selectedPatient->is_active ? 'badge-success' : 'badge-ghost' }}">
                        {{ $selectedPatient->is_active ? __('Active Patient') : __('Archived') }}
                    </span>
                </div>
            </div>

            <div class="space-y-2">
                <x-mary-list-item :item="$selectedPatient" title="{{ __('Phone') }}" sub-value="phone_number"
                    icon="o-phone" />
                <x-mary-list-item :item="$selectedPatient" title="{{ __('Location') }}" sub-value="city" icon="o-map-pin" />
                <x-mary-list-item :item="$selectedPatient" title="{{ __('Date of Birth') }}" icon="o-cake">
                    <x-slot:sub-value>
                        {{ $selectedPatient->date_of_birth ? $selectedPatient->date_of_birth->translatedFormat('M d, Y') : __('N/A') }}
                    </x-slot:sub-value>
                </x-mary-list-item>
            </div>

            <div class="p-4 gap-2 flex flex-col">
                <x-mary-button label="{{ __('Open Health Folder') }}" icon="o-folder-open" class="btn-primary"
                    link="{{ route('patient.health.folder', $selectedPatient->id) }}" />
                <x-mary-button label="{{ __('Schedule Session') }}" icon="o-calendar" class="btn-outline"
                    link="{{ route('patient.session.create', $selectedPatient->id) }}" />
            </div>
        @endif
    </x-mary-drawer>

    {{-- 🗑️ Delete Confirmation Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="{{ __('Delete Patient') }}" class="backdrop-blur">
        <div class="mb-5 text-gray-500">
            {{ __('Are you sure you want to delete this patient? All associated data (sessions, diagnoses) will also be removed. This action cannot be undone.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Delete Permanently') }}" wire:click="delete" class="btn-error" />
        </x-slot:actions>
    </x-mary-modal>

</div>
