<div class="p-4">
    <x-page-header :title="__('Diagnosis')" :subtitle="$patient->first_name . ' ' . $patient->last_name" />
    <form wire:submit.prevent="save">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session()->get('error') }}
                    </div>
                @endif
                @if (session()->has('success'))
                    <div class="mt-4 p-4 bg-green-100 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <x-mary-choices label="{{ __('ICD Code') }}" wire:model="icd_searchable_id" :options="$icdCodes"
                    placeholder="Search ..." min-chars="2" search-function="searchIcd"
                    no-result-text="Ops! Nothing here ..." single clearable searchable option-label="code"
                    option-sub-label="description" />
                @error('icd_searchable_id')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
                <div class="mb-5"></div>

                <flux:textarea label="{{ __('Description') }}" wire:model="description"
                    placeholder="{{ __('Enter description') }}" />
                @error('description')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
                <div class="mb-5"></div>


                <flux:input label="{{ __('Start date') }}" type="date" wire:model="start_date"
                    placeholder="{{ __('Enter start date') }}" />
                @error('start_date')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
                <div class="mb-5"></div>

                <flux:label>{{ __('Diagnosis type') }}</flux:label>
                <flux:select wire:model="type" placeholder="Choose diagnosis type...">
                    @foreach ($diagnosisTypes as $dt)
                        <flux:select.option value="{{ $dt }}">{{ $dt }}</flux:select.option>
                    @endforeach

                </flux:select>
                @error('type')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
                <div class="mb-5"></div>

                <flux:label>{{ __('Condition Status') }}</flux:label>
                <flux:select wire:model="condition_status" placeholder="Choose condition status...">
                    @foreach ($conditionStatuses as $cs)
                        <flux:select.option value="{{ $cs }}">{{ $cs }}</flux:select.option>
                    @endforeach

                </flux:select>
                @error('condition_status')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
                <div class="mb-5"></div>

                <flux:button type="submit">
                    <span wire:loading.remove>{{ __('Add Diagnosis') }}</span>
                    <span wire:loading>{{ __('Saving ...') }}</span>

                </flux:button>


            </div>
            <div></div>
        </div>
    </form>
</div>
