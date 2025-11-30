@php
    $headers = [
        ['key' => 'scheduled_at', 'label' => __('Date/Time')],
        ['key' => 'patient.full_name', 'label' => __('Patient')],
        ['key' => 'user.name', 'label' => __('Therapist')],
        ['key' => 'duration_minutes', 'label' => __('Duration')],
        ['key' => 'status', 'label' => __('Status')],
        ['key' => 'billing_status', 'label' => __('Billing')],
    ];
@endphp

<div>

    <x-page-header :title="__('Therapy Session List')" :subtitle="__('View and manage all scheduled and completed sessions.')" />

    {{-- Filters and Search --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">

        {{-- Search Input --}}
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search patient/therapist name...') }}"
                wire:model.live.debounce.300ms="search" />
        </div>

        {{-- Status Filter Button Group --}}
        <div class="w-full md:w-auto">
            {{-- Status Filter Button Group --}}
            <div class="w-full md:w-auto">
                <div class="btn-group"> {{-- ⬅️ FIX: Use the standard HTML div with DaisyUI's btn-group class --}}
                    @foreach ($statuses as $statusOption)
                        <x-mary-button :label="$statusOption" wire:click="$set('statusFilter', '{{ $statusOption }}')"
                            :class="$statusOption === $statusFilter ? 'btn-primary' : 'btn-ghost'" />
                    @endforeach
                </div> {{-- ⬅️ END of btn-group div --}}
            </div>
        </div>

        {{-- Add Session Button (Optional) --}}
        <a href="{{ route('patient.list') }}">
            <x-mary-button label="{{ __('Schedule New Session') }}" icon="o-calendar" class="btn-primary" />
        </a>
    </div>

    {{-- Session Table --}}
    <x-mary-table :headers="$headers" :rows="$sessions" with-pagination>

        {{-- Custom scope for Date/Time --}}
        @scope('cell_scheduled_at', $session)
            <div class="font-bold">{{ $session->scheduled_at->format('Y-m-d') }}</div>
            <div class="text-sm text-gray-500">{{ $session->scheduled_at->format('H:i') }}</div>
        @endscope

        {{-- Custom scope for Patient Name (Link to patient profile) --}}
        @scope('cell_patient.full_name', $session)
            <a href="{{ route('patient.health.folder', $session->patient_id) }}" class="link link-secondary font-semibold">
                {{ $session->patient->first_name }} {{ $session->patient->last_name }}
            </a>
        @endscope

        {{-- Custom scope for Status --}}
        @scope('cell_status', $session)
            <x-mary-badge :value="$session->status" :class="[
                'Scheduled' => 'badge-info',
                'Completed' => 'badge-success',
                'Cancelled' => 'badge-error',
                'No Show' => 'badge-warning',
            ][$session->status] ?? 'badge-neutral'" class="font-semibold" />
        @endscope

        {{-- Custom scope for Billing Status --}}
        @scope('cell_billing_status', $session)
            <x-mary-badge :value="$session->billing_status" :class="[
                'Billed' => 'badge-primary',
                'Pending' => 'badge-neutral',
                'Paid' => 'badge-success',
                'Not Applicable' => 'badge-ghost',
            ][$session->billing_status] ?? 'badge-neutral'" class="font-semibold" />
        @endscope

        {{-- Actions column (Optional: Link to Session Details) --}}
        @scope('actions', $session)
            <a href="{{ route('sessions.detail', $session->id) }}">
                <x-mary-button icon="o-eye" class="btn-sm btn-ghost" />
            </a>
        @endscope
    </x-mary-table>
</div>
