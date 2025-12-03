<div class="space-y-6">

    {{-- 🟢 HEADER --}}
    <x-page-header title="{{ __('Clinical Diagnoses') }}"
        subtitle="{{ __('Global registry of patient conditions and medical history.') }}" separator />

    {{-- 📊 STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-mary-stat title="{{ __('Total Recorded') }}" value="{{ $stats['total'] }}" icon="o-clipboard-document-list"
            class="bg-base-100 shadow-sm border border-base-200" />
        <x-mary-stat title="{{ __('Active Cases') }}" value="{{ $stats['active'] }}" icon="o-exclamation-circle"
            class="bg-base-100 shadow-sm border border-base-200" color="text-warning" />
        <x-mary-stat title="{{ __('Resolved') }}" value="{{ $stats['resolved'] }}" icon="o-check-badge"
            class="bg-base-100 shadow-sm border border-base-200" color="text-success" />
    </div>

    {{-- 🎛️ CONTROLS --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">

        {{-- Search --}}
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search patient, ICD code, or condition...') }}"
                wire:model.live.debounce.300ms="search" class="w-full" />
        </div>

        {{-- Filters --}}
        <div class="flex gap-2 w-full md:w-auto overflow-x-auto">
            <div class="join">
                <button class="join-item btn btn-sm {{ $statusFilter === '' ? 'btn-neutral' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', '')">
                    {{ __('All') }}
                </button>
                <button
                    class="join-item btn btn-sm {{ $statusFilter === 'Active' ? 'btn-active btn-warning' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', 'Active')">
                    {{ __('Active') }}
                </button>
                <button
                    class="join-item btn btn-sm {{ $statusFilter === 'Resolved' ? 'btn-active btn-success text-white' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', 'Resolved')">
                    {{ __('Resolved') }}
                </button>
            </div>
        </div>
    </div>

    {{-- 📋 DIAGNOSES TABLE --}}
    <x-mary-card shadow class="bg-base-100">
        <x-mary-table :headers="$this->headers()" :rows="$diagnoses" :sort-by="$sortBy" {{-- ✅ LINK FIX: No $event needed here. [id] is automatically replaced by MaryUI --}} :link="route('patient.diagnosis.detail', ['diagnosis' => '[id]'])"
            class="cursor-pointer hover:bg-base-50" with-pagination>

            {{-- 🏷️ ICD Code --}}
            @scope('cell_icdCode.code', $diagnosis)
                <div class="font-mono font-bold text-primary text-lg">{{ $diagnosis->icdCode->code }}</div>
            @endscope

            {{-- 👤 Patient --}}
            @scope('cell_patient.last_name', $diagnosis)
                <div class="flex items-center gap-3">
                    <x-mary-avatar :image="$diagnosis->patient->avatar" :title="$diagnosis->patient->first_name" class="!w-8 !h-8" />
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-800">{{ $diagnosis->patient->first_name }}
                            {{ $diagnosis->patient->last_name }}</span>
                        <span class="text-xs text-gray-400">{{ __('ID:') }} {{ $diagnosis->patient->id }}</span>
                    </div>
                </div>
            @endscope

            {{-- 🏥 Condition Description --}}
            @scope('cell_icdCode.description', $diagnosis)
                <div class="font-medium text-gray-700 truncate" title="{{ $diagnosis->icdCode->description }}">
                    {{ $diagnosis->icdCode->description }}
                </div>
                @if ($diagnosis->description)
                    <div class="text-xs text-gray-500 italic truncate max-w-xs mt-0.5">"{{ $diagnosis->description }}"
                    </div>
                @endif
            @endscope

            {{-- 🔖 Type Badge --}}
            @scope('cell_type', $diagnosis)
                <x-mary-badge :value="__($diagnosis->type)" {{-- ✅ Translate ONLY the display label --}}
                    class="text-xs font-bold
        @if ($diagnosis->type === 'Primary')
{{-- ⚠️ Keep checking the raw DB value (English) --}}
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

            {{-- 🗓️ Date --}}
            @scope('cell_start_date', $diagnosis)
                {{-- Use translated format for localized dates (e.g. '01 Jan 2025') --}}
                <span class="text-gray-500 text-sm">{{ $diagnosis->start_date->translatedFormat('M d, Y') }}</span>
            @endscope

            {{-- ⚙️ Actions --}}
            @scope('actions', $diagnosis)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-sm btn-ghost btn-circle" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('View Details') }}" icon="o-eye"
                            link="{{ route('patient.diagnosis.detail', $diagnosis->id) }}" />
                        <x-mary-menu-item title="{{ __('Edit') }}" icon="o-pencil"
                            link="{{ route('patient.diagnosis.edit', ['patient' => $diagnosis->patient_id, 'diagnosis' => $diagnosis->id]) }}" />
                        <x-mary-menu-item title="{{ __('Go to Patient') }}" icon="o-user"
                            link="{{ route('patient.health.folder', $diagnosis->patient_id) }}" />

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
            {{ __('Are you sure you want to remove this diagnosis record? This action cannot be undone.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Confirm Delete') }}" wire:click="delete" class="btn-error" spinner />
        </x-slot:actions>
    </x-mary-modal>

</div>
