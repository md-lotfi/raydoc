<div class="space-y-6">

    {{-- 🟢 HEADER --}}
    <x-page-header title="{{ __('Medical Diagnoses') }}"
        subtitle="{{ __('Clinical history for ') . $patient->first_name . ' ' . $patient->last_name }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Add Diagnosis') }}" icon="o-plus" class="btn-primary"
                link="{{ route('patient.diagnosis.create', $patient->id) }}" />
        </x-slot:actions>
    </x-page-header>

    {{-- 📊 PATIENT STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-mary-stat title="{{ __('Total Conditions') }}" value="{{ $this->stats['total'] }}"
            icon="o-clipboard-document-list" class="bg-base-100 shadow-sm border border-base-200" />
        <x-mary-stat title="{{ __('Active Issues') }}" value="{{ $this->stats['active'] }}"
            icon="o-exclamation-triangle" class="bg-base-100 shadow-sm border border-base-200" color="text-warning" />
        <x-mary-stat title="{{ __('Primary Diagnoses') }}" value="{{ $this->stats['primary'] }}" icon="o-star"
            class="bg-base-100 shadow-sm border border-base-200" color="text-primary" />
    </div>

    {{-- 🎛️ CONTROLS --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">

        {{-- Search --}}
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search ICD code or description...') }}"
                wire:model.live.debounce.300ms="search" class="w-full" />
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            {{-- Status Filter Tabs --}}
            <div class="join">
                <button class="join-item btn btn-sm {{ $statusFilter === '' ? 'btn-neutral' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', '')">{{ __('All') }}</button>
                <button
                    class="join-item btn btn-sm {{ $statusFilter === 'Active' ? 'btn-active btn-warning' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', 'Active')">{{ __('Active') }}</button>
                <button
                    class="join-item btn btn-sm {{ $statusFilter === 'Resolved' ? 'btn-active btn-success text-white' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', 'Resolved')">{{ __('Resolved') }}</button>
            </div>

            {{-- Type Filter Dropdown --}}
            <x-mary-dropdown label="{{ $typeFilter ?: __('Type') }}" class="btn-sm btn-outline">
                <x-mary-menu-item title="{{ __('All Types') }}" wire:click="$set('typeFilter', '')" />
                @foreach ($diagnosisTypes as $type)
                    <x-mary-menu-item title="{{ __($type) }}"
                        wire:click="$set('typeFilter', '{{ $type }}')" />
                @endforeach
            </x-mary-dropdown>
        </div>
    </div>

    {{-- 📋 DIAGNOSIS TABLE --}}
    <x-mary-card shadow class="bg-base-100">
        <x-mary-table container-class="" :headers="$this->headers()" :rows="$this->diagnoses" :sort-by="$sortBy" :link="route('patient.diagnosis.detail', ['diagnosis' => '[id]'])"
            class="cursor-pointer hover:bg-base-50" with-pagination>

            {{-- 🏷️ ICD Code --}}
            @scope('cell_icdCode.code', $diagnosis)
                <span class="font-mono font-bold text-primary text-lg">{{ $diagnosis->icdCode->code }}</span>
            @endscope

            {{-- 🏥 Description --}}
            @scope('cell_icdCode.description', $diagnosis)
                <div class="font-medium text-gray-800">{{ $diagnosis->icdCode->description }}</div>
                @if ($diagnosis->description)
                    <div class="text-xs text-gray-500 italic truncate max-w-xs mt-0.5">"{{ $diagnosis->description }}"
                    </div>
                @endif
            @endscope

            {{-- 🔖 Type Badge --}}
            @scope('cell_type', $diagnosis)
                <x-mary-badge :value="__($diagnosis->type)"
                    class="text-xs font-bold
                    @if ($diagnosis->type === 'Primary')
badge-primary/10 text-primary
@elseif($diagnosis->type === 'Secondary')
badge-secondary/10 text-secondary
@else
badge-ghost
@endif" />
            @endscope

            {{-- 🚦 Status Badge --}}
            @scope('cell_condition_status', $diagnosis)
                <x-mary-badge :value="__($diagnosis->condition_status)"
                    class="text-xs font-bold
                    @if ($diagnosis->condition_status === 'Active')
badge-warning/10 text-warning
@elseif($diagnosis->condition_status === 'Resolved')
badge-success/10 text-success
@elseif($diagnosis->condition_status === 'Chronic')
badge-error/10 text-error
@else
badge-ghost
@endif" />
            @endscope

            {{-- 🗓️ Start Date --}}
            @scope('cell_start_date', $diagnosis)
                <span
                    class="text-gray-600">{{ $diagnosis->start_date ? $diagnosis->start_date->translatedFormat('M d, Y') : '-' }}</span>
            @endscope

            {{-- ⚙️ Actions --}}
            @scope('actions', $diagnosis, $patient)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-sm btn-ghost btn-circle" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('View Details') }}" icon="o-eye"
                            link="{{ route('patient.diagnosis.detail', $diagnosis->id) }}" />
                        <x-mary-menu-item title="{{ __('Edit') }}" icon="o-pencil"
                            link="{{ route('patient.diagnosis.edit', ['patient' => $patient->id, 'diagnosis' => $diagnosis->id]) }}" />

                        <x-mary-menu-separator />

                        <x-mary-menu-item title="{{ __('Delete') }}" icon="o-trash" class="text-error"
                            wire:click="confirmDelete({{ $diagnosis->id }})" />
                    </x-mary-dropdown>
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- 🗑️ Delete Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="{{ __('Delete Diagnosis') }}" class="backdrop-blur">
        <div class="mb-5 text-gray-600">
            {{ __('Are you sure you want to delete this diagnosis? This action cannot be undone.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Confirm Delete') }}" wire:click="delete" class="btn-error" spinner />
        </x-slot:actions>
    </x-mary-modal>

</div>
