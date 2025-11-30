@php
    $headers = [
        ['key' => 'id', 'label' => '#'],
        [
            'key' => 'scheduled_at',
            'label' => 'Scheduled Date',
            'sortable' => true,
            // Format to show only the date part of the timestamp
            'format' => fn($row, $field) => date('Y-m-d', strtotime($field)),
        ],
        [
            'key' => 'scheduled_at',
            'label' => 'Time',
            'sortable' => false,
            // Format to show only the time part of the timestamp
            'format' => fn($row, $field) => date('H:i', strtotime($field)),
        ],
        [
            'key' => 'duration_minutes',
            'label' => 'Duration',
            'sortable' => true,
            'format' => fn($row, $field) => "{$field} min",
        ],
        [
            'key' => 'user.name',
            'label' => 'Therapist',
            'sortable' => false, // Assuming searching/sorting by therapist name happens via Livewire query
        ],
        [
            'key' => 'focus_area',
            'label' => 'Focus Area',
            'sortable' => false, // Assuming this is descriptive text
        ],
        [
            'key' => 'status',
            'label' => 'Session Status',
        ],
        [
            'key' => 'billing_status',
            'label' => 'Billing Status',
        ],
    ];
@endphp
<div class="p-4">
    <x-page-header :title="__('Therapy Sessions')" :subtitle="$patient->first_name . ' ' . $patient->last_name" />

    <div>
        <x-custom-search model="search" placeholder="{{ __('Search sessions...') }}">
            <x-slot:actions>
                <a href="{{ route('patient.session.create', ['patient' => $patient->id]) }}">
                    <x-mary-button icon="o-calendar-days" label="Schedule Session" class="btn-primary" />
                </a>
                <a href="{{ route('invoice.generate', ['patient' => $patient->id]) }}">
                    <x-mary-button icon="s-banknotes" label="Generate invoice" class="btn-success" />
                </a>
                <x-mary-button icon="o-document-arrow-down" label="Export to PDF" wire:click="exportPdf"
                    class="btn-secondary" />
                <x-mary-button icon="o-funnel" label="Filter" wire:click="toggleFilter" class="btn-ghost" />
            </x-slot:actions>
        </x-custom-search>
    </div>

    @if ($sessions->isNotEmpty())

        <x-mary-table container-class="" :headers="$headers" :rows="$sessions" with-pagination>

            {{-- Custom scope for Session Status (Crucial for quick identification) --}}
            @scope('cell_status', $session)
                <x-mary-badge :value="$session->status"
                    class="{{ match ($session->status) {
                        'Completed' => 'bg-green-100 text-green-800',
                        'Scheduled' => 'bg-indigo-100 text-indigo-800',
                        'Cancelled', 'No Show' => 'bg-red-100 text-red-800',
                        'Rescheduled' => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-gray-100 text-gray-800',
                    } }}" />
            @endscope

            {{-- Custom scope for Billing Status (Important for Admin/Billing staff) --}}
            @scope('cell_billing_status', $session)
                <x-mary-badge :value="$session->billing_status"
                    class="{{ match ($session->billing_status) {
                        'Paid' => 'bg-green-200 text-green-900',
                        'Billed' => 'bg-blue-200 text-blue-900',
                        'Pending' => 'bg-orange-200 text-orange-900',
                        default => 'bg-gray-200 text-gray-900', // Not Applicable/Cancelled
                    } }}" />
            @endscope

            {{-- Actions column (for edit, complete, cancel actions) --}}
            @scope('actions', $session, $patient)
                <x-mary-dropdown>

                    @if ($session->status === 'Scheduled')
                        <x-mary-menu-item title="Mark as Completed" icon="o-check-circle"
                            wire:click="completeSession({{ $session->id }})" />
                    @endif
                    @if ($session->status !== 'Cancelled')
                        <x-mary-menu-item title="Edit" icon="s-pencil-square"
                            link="{{ route('patient.session.edit', ['patient' => $patient->id, 'session' => $session->id]) }}" />
                        <x-mary-menu-item title="Cancel Session" icon="o-x-circle"
                            wire:click="confirmCancel({{ $session->id }})" />
                    @endif
                    <x-mary-menu-item title="Not Show" icon="o-face-frown"
                        wire:click="confirmAbscent({{ $session->id }})" />
                    <x-mary-menu-item title="Duplicate" icon="o-document-duplicate"
                        wire:click="showDuplicateSessionModal({{ $session->id }})" />
                </x-mary-dropdown>
            @endscope
        </x-mary-table>

        {{-- You would need a separate modal for cancellation reasons --}}
        {{-- <x-mary-modal wire:model="showCancelModal" ...></x-mary-modal> --}}
        <x-mary-modal wire:model="showCancelModal" title="Cancel Therapy Session"
            subtitle="A reason is required for tracking and auditing purposes." separator>

            {{-- Input field for Cancellation Reason --}}
            <div class="space-y-4">
                <div class="text-lg text-red-700 font-semibold">
                    {{ __('Please confirm cancellation and provide a reason.') }}
                </div>

                <flux:textarea label="Cancellation Reason" wire:model="cancellationReason"
                    placeholder="eg., Patient flu, Provider emergency, Scheduling conflict." rows="4" required />
                @error('cancellationReason')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <x-slot:actions>
                <x-mary-button label="Close" @click="$wire.showCancelModal = false" />
                {{-- The wire:click method should be changed to a cancellation method, e.g., 'cancelSession' --}}
                <x-mary-button label="Confirm Cancellation" wire:click="cancelSession" class="btn-warning" spinner />
            </x-slot:actions>
        </x-mary-modal>

        <x-mary-modal wire:model="showAbscentModal" separator>

            {{-- Dynamic Title --}}
            <x-slot:title>
                {{ __('Mark Session as No Show (Absent)') }}
            </x-slot:title>

            {{-- Dynamic Subtitle --}}
            <x-slot:subtitle>
                {{ __('A reason is required for tracking and auditing purposes.') }}
            </x-slot:subtitle>

            {{-- Dynamic Content --}}
            <div class="space-y-4">

                {{-- Confirmation Message --}}
                <div class="text-lg font-semibold text-warning-700">
                    {{ __('Please confirm marking this session as "No Show" and provide notes.') }}
                </div>

                {{-- Input field for Reason/Notes --}}
                <flux:textarea label="{{ __('No Show Notes') }}" wire:model="abscentReason"
                    placeholder="{{ __('eg., Patient failed to arrive and did not call.') }}" rows="4"
                    required />
                @error('reason')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <x-slot:actions>
                <x-mary-button label="Close" @click="$wire.showAbscentModal = false" />

                {{-- Dynamic Action Button --}}
                <x-mary-button label="{{ __('Confirm No Show') }}" wire:click="markSessionAsNoShow" class="btn-warning"
                    spinner />
            </x-slot:actions>
        </x-mary-modal>

        <x-mary-modal wire:model="showDuplicateSchedule" title="Schedule session date" subtitle="You must enter a date."
            separator>

            {{-- Input field for Cancellation Reason --}}
            <div class="space-y-4">
                <div class="text-lg text-red-700 font-semibold">
                    {{ __('Please enter a valid date for the duplicated session.') }}
                </div>

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

            <x-slot:actions>
                <x-mary-button label="Close" @click="$wire.showDuplicateSchedule = false" />
                {{-- The wire:click method should be changed to a cancellation method, e.g., 'cancelSession' --}}
                <x-mary-button label="Confirm Duplicate" wire:click="duplicateSession" class="btn-warning" spinner />
            </x-slot:actions>
        </x-mary-modal>
    @else
        <x-alert color="info" title="No therapy sessions found">
            <a href="{{ route('patient.session.create', ['patient' => $patient->id]) }}">
                <flux:button variant="primary" color="indigo">{{ __('Schedule first session') }}</flux:button>
            </a>
        </x-alert>
    @endif
</div>
