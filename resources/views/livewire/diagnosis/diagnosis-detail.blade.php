<div>

    {{-- Use $diagnosis object for title --}}
    <x-page-header :title="__('Diagnosis Detail')" :subtitle="__('ICD Code: ' . $diagnosis->icdCode->code)" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- 📝 Left Column (Diagnosis Details) --}}
        <div class="lg:col-span-2 space-y-6">

            <x-mary-card title="{{ __('Diagnosis Information') }}" shadow separator>
                <div class="space-y-6">

                    {{-- 1. ICD Code Display --}}
                    <div class="bg-base-100 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-xs font-semibold uppercase text-gray-500">{{ __('ICD Code') }}</h4>
                        <p class="text-xl font-bold text-primary">{{ $diagnosis->icdCode->code }}</p>
                        <p class="text-sm text-gray-700">{{ $diagnosis->icdCode->description }}</p>
                    </div>

                    {{-- 2. Description / Clinical Notes --}}
                    <div>
                        <flux:label class="text-lg font-semibold">{{ __('Detailed Description / Clinical Notes') }}
                        </flux:label>
                        <p class="mt-2 p-3 bg-base-100 rounded-lg whitespace-pre-wrap">
                            {{ $diagnosis->description ?? 'N/A' }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">

                        {{-- 3. Start Date --}}
                        <div>
                            <flux:label>{{ __('Start Date (Date of Onset)') }}</flux:label>
                            <p class="font-semibold">{{ $diagnosis->start_date }}</p>
                        </div>

                        {{-- 4. Diagnosis Type --}}
                        <div>
                            <flux:label>{{ __('Diagnosis Type') }}</flux:label>
                            <p class="font-semibold">{{ $diagnosis->type }}</p>
                        </div>

                        {{-- 5. Condition Status --}}
                        <div>
                            <flux:label>{{ __('Condition Status') }}</flux:label>
                            <p class="font-semibold">{{ $diagnosis->condition_status }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t">
                        <flux:label>{{ __('Diagnosed By') }}</flux:label>
                        <p class="font-semibold text-info">{{ $diagnosis->user->name ?? 'N/A' }}</p>
                    </div>

                </div>
            </x-mary-card>

            {{-- Optional: Action to edit the diagnosis --}}
            <div class="flex justify-start mt-4">
                <x-mary-button label="{{ __('Edit Diagnosis') }}" icon="o-pencil" class="btn-warning"
                    link="{{ route('patient.diagnosis.edit', ['patient' => $diagnosis->patient->id, 'diagnosis' => $diagnosis->id]) }}" />
            </div>

        </div>

        {{-- 🖼️ Right Column: Patient Summary --}}
        <div class="lg:col-span-1 space-y-6">
            <x-mary-card title="{{ __('Patient Summary') }}" shadow separator>

                <h3 class="text-lg font-semibold mb-2">
                    {{ $diagnosis->patient->first_name }} {{ $diagnosis->patient->last_name }}
                </h3>

                {{-- REPLACED x-mary-list with standard HTML definition list (dl) --}}
                <dl class="divide-y divide-base-200">

                    {{-- Date of Birth --}}
                    <div class="py-2 flex items-start space-x-3">
                        <x-mary-icon name="o-calendar" class="w-5 h-5 flex-shrink-0 text-primary mt-1" />
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Date of Birth') }}</dt>
                            <dd class="mt-0.5 text-base font-semibold">
                                {{ $diagnosis->patient->date_of_birth ?? 'N/A' }}</dd>
                        </div>
                    </div>

                    {{-- Gender --}}
                    <div class="py-2 flex items-start space-x-3">
                        <x-mary-icon name="o-user" class="w-5 h-5 flex-shrink-0 text-primary mt-1" />
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Gender') }}</dt>
                            <dd class="mt-0.5 text-base font-semibold">{{ $diagnosis->patient->gender ?? 'N/A' }}</dd>
                        </div>
                    </div>

                    {{-- Created At --}}
                    <div class="py-2 flex items-start space-x-3">
                        <x-mary-icon name="o-clock" class="w-5 h-5 flex-shrink-0 text-primary mt-1" />
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Recorded On') }}</dt>
                            <dd class="mt-0.5 text-base font-semibold">{{ $diagnosis->created_at->format('Y-m-d') }}
                            </dd>
                        </div>
                    </div>

                </dl>
            </x-mary-card>
        </div>
    </div>
</div>
