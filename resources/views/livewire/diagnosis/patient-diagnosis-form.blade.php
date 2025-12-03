<div class="max-w-5xl mx-auto space-y-8">

    <x-page-header title="{{ $diagnosisId ? __('Edit Diagnosis') : __('New Diagnosis') }}"
        subtitle="{{ __('Patient: ') . $patient->first_name . ' ' . $patient->last_name }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" link="{{ route('patient.diagnosis.list', $patient->id) }}"
                class="btn-ghost" />
            <x-mary-button label="{{ $diagnosisId ? __('Update Record') : __('Save Diagnosis') }}" icon="o-check"
                class="btn-primary" wire:click="save" spinner="save" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 🏥 LEFT COLUMN: Core Diagnosis --}}
        <div class="lg:col-span-2 space-y-6">
            <x-mary-card title="{{ __('Clinical Condition') }}" shadow separator>

                <div class="space-y-6">
                    {{-- ICD Search --}}
                    <x-mary-choices label="{{ __('ICD-10 Code') }}" wire:model="icd_searchable_id" :options="$icdCodes"
                        placeholder="{{ __('Search code or description...') }}" min-chars="2"
                        search-function="searchIcd" single searchable icon="o-magnifying-glass" option-label="code"
                        option-sub-label="description" class="text-lg">
                        {{-- ✅ FIX: Use @scope instead of <x-slot:item> --}}
                        @scope('item', $code)
                            <div class="flex flex-col text-left">
                                <span class="font-bold">{{ $code->code }}</span>
                                <span class="text-xs text-gray-500 truncate">{{ $code->description }}</span>
                            </div>
                        @endscope

                        {{-- Optional: Customize the selected value display as well --}}
                        @scope('selection', $code)
                            <div class="flex flex-col text-left leading-tight">
                                <span class="font-bold">{{ $code->code }}</span>
                                <span class="text-xs text-gray-500">{{ Str::limit($code->description, 40) }}</span>
                            </div>
                        @endscope
                    </x-mary-choices>

                    {{-- Description --}}
                    <x-mary-textarea label="{{ __('Clinical Notes') }}" wire:model="description"
                        placeholder="{{ __('Enter specific details regarding this diagnosis...') }}" rows="5"
                        hint="{{ __('Visible in patient medical history') }}" />
                </div>

            </x-mary-card>
        </div>

        {{-- ⚙️ RIGHT COLUMN: Metadata --}}
        <div class="lg:col-span-1 space-y-6">
            <x-mary-card title="{!! __('Status & Classification') !!}" shadow separator>

                <div class="space-y-4">
                    <x-mary-datepicker label="{{ __('Date of Onset') }}" wire:model="start_date" icon="o-calendar" />

                    <x-mary-select label="{{ __('Type') }}" wire:model="type" :options="$diagnosisTypes" icon="o-tag"
                        placeholder="{{ __('Select type...') }}" />

                    <x-mary-select label="{{ __('Status') }}" wire:model="condition_status" :options="$conditionStatuses"
                        icon="o-check-circle" placeholder="{{ __('Select status...') }}" />
                </div>

            </x-mary-card>

            {{-- Save Button (Mobile) --}}
            <div class="lg:hidden">
                <x-mary-button label="{{ __('Save Diagnosis') }}" icon="o-check" class="btn-primary w-full"
                    wire:click="save" />
            </div>
        </div>

    </div>
</div>
