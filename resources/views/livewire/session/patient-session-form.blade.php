<div class="p-4">
    <x-page-header :title="__('Schedule New Session')" :subtitle="__('Patient: ' . $patient->first_name . ' ' . $patient->last_name)" />

    @if (session()->has('success'))
        <x-alert color="success" title="Success" class="mb-4">
            {{ session('success') }}
        </x-alert>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">

        <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-accent-700">{{ __('1. Scheduling Details') }}</h3>

        {{-- Date, Time, and Duration --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Session Date --}}
            <div>
                <flux:input label="Date" type="date" wire:model="session_date" required />
                @error('session_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Start Time --}}
            <div>
                <flux:input label="Start Time" type="time" wire:model="start_time" required />
                @error('start_time')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Duration --}}
            <div>
                <flux:input label="Duration (minutes)" type="number" wire:model="duration_minutes" min="15"
                    max="180" required />
                @error('duration_minutes')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Therapist Selection --}}
        <div>
            <flux:select label="Therapist" wire:model="therapist_user_id" option-value="id" required>
                @foreach ($availableTherapists as $g)
                    <flux:select.option value="{{ $g->id }}">{{ $g->name }}</flux:select.option>
                @endforeach

            </flux:select>
            @error('therapist_user_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <h3 class="text-lg font-semibold border-b pb-2 mb-4 pt-4 text-accent-700">{{ __('2. Session Focus') }}</h3>

        {{-- Focus Area (Can be text or a select if you normalize it) --}}
        <div>
            <flux:input label="Focus Area (e.g., CBT, Anxiety Management)" wire:model="focus_area" required />
            @error('focus_area')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Clinical Notes --}}
        <div>
            <flux:textarea label="Clinical Notes / Pre-session thoughts (Optional)" wire:model="notes" rows="4" />
            @error('notes')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Homework Assigned --}}
        <div>
            <flux:textarea label="Initial Homework Assignment (Optional)" wire:model="homework_assigned"
                rows="2" />
            @error('homework_assigned')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Submit Button --}}
        <div class="pt-4">
            <x-mary-button type="submit" label="Schedule Session" icon="o-calendar" class="btn-primary w-full"
                spinner="save" />
        </div>
    </form>
</div>
