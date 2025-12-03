<div class="max-w-5xl mx-auto space-y-8">

    <x-page-header title="{{ $session ? __('Edit Session') : __('Schedule Session') }}"
        subtitle="{{ __('Patient: ') . $patient->first_name . ' ' . $patient->last_name }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" link="{{ route('patient.session.list', $patient->id) }}"
                class="btn-ghost" />
            <x-mary-button label="{{ $session ? __('Update Session') : __('Confirm Booking') }}" icon="o-calendar"
                class="btn-primary" wire:click="save" spinner="save" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 🗓️ LEFT COLUMN: Scheduling --}}
        <div class="lg:col-span-1 space-y-6">
            <x-mary-card title="{{ __('Appointment Details') }}" shadow separator class="relative overflow-hidden">

                {{-- Decorative bg --}}
                <div class="absolute top-0 right-0 w-20 h-20 bg-primary/10 rounded-bl-full -mr-4 -mt-4"></div>

                <div class="space-y-4">

                    {{-- Date & Time --}}
                    <x-mary-datepicker label="{{ __('Date') }}" wire:model="session_date" icon="o-calendar" />
                    <x-mary-input label="{{ __('Time') }}" type="time" wire:model="start_time" icon="o-clock" />

                    {{-- Duration Presets --}}
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">{{ __('Duration') }}</span>
                        </label>
                        <div class="join w-full mb-2">
                            <button type="button"
                                class="join-item btn btn-sm flex-1 {{ $duration_minutes == 30 ? 'btn-primary' : 'btn-ghost bg-base-200' }}"
                                wire:click="setDuration(30)">30{{ __('m') }}</button>
                            <button type="button"
                                class="join-item btn btn-sm flex-1 {{ $duration_minutes == 45 ? 'btn-primary' : 'btn-ghost bg-base-200' }}"
                                wire:click="setDuration(45)">45{{ __('m') }}</button>
                            <button type="button"
                                class="join-item btn btn-sm flex-1 {{ $duration_minutes == 60 ? 'btn-primary' : 'btn-ghost bg-base-200' }}"
                                wire:click="setDuration(60)">60{{ __('m') }}</button>
                        </div>
                        <x-mary-input wire:model="duration_minutes" type="number" suffix="{{ __('min') }}"
                            min="15" max="180" class="text-center font-mono" />
                    </div>

                    {{-- Therapist --}}
                    <x-mary-select label="{{ __('Therapist') }}" :options="$availableTherapists" wire:model="therapist_user_id"
                        icon="o-user-circle" />

                    {{-- Recurring Toggle (Visual Only for now) --}}
                    <x-mary-checkbox label="{{ __('Repeat Weekly') }}" wire:model="is_recurring"
                        hint="{{ __('Create this appointment for the next 4 weeks') }}" />

                </div>
            </x-mary-card>
        </div>

        {{-- 📝 RIGHT COLUMN: Clinical Plan --}}
        <div class="lg:col-span-2 space-y-6">
            <x-mary-card title="{{ __('Clinical Focus') }}"
                subtitle="{{ __('Plan the session content and objectives.') }}" shadow separator>

                <div class="space-y-4">
                    <x-mary-input label="{{ __('Primary Focus Area') }}" wire:model="focus_area" icon="o-tag"
                        placeholder="{{ __('e.g. Cognitive Restructuring, Anxiety Trigger Analysis') }}" />

                    <div class="grid grid-cols-1 gap-4">
                        <x-mary-textarea label="{{ __('Pre-Session Notes') }}" wire:model="notes" rows="5"
                            placeholder="{{ __('Internal notes for the therapist...') }}" />
                        <x-mary-textarea label="{{ __('Homework / Preparation') }}" wire:model="homework_assigned"
                            rows="3" icon="o-clipboard-document-check"
                            placeholder="{{ __('Tasks for patient to complete before this session...') }}" />
                    </div>
                </div>

            </x-mary-card>

            {{-- Save Button (Mobile) --}}
            <div class="lg:hidden">
                <x-mary-button label="{{ __('Confirm Booking') }}" icon="o-check" class="btn-primary w-full"
                    wire:click="save" />
            </div>
        </div>

    </div>
</div>
