@php
    $headers = [
        ['key' => 'scheduled_at', 'label' => __('Date & Time'), 'class' => 'w-48'],
        ['key' => 'patient.last_name', 'label' => __('Patient')],
        ['key' => 'user.name', 'label' => __('Therapist'), 'class' => 'hidden md:table-cell'],
        ['key' => 'status', 'label' => __('Status'), 'class' => 'text-center'],
        ['key' => 'billing_status', 'label' => __('Billing'), 'class' => 'text-center hidden lg:table-cell'],
    ];
@endphp

<div class="space-y-6">

    {{-- 🟢 PAGE HEADER --}}
    <x-page-header title="{{ __('Session History') }}" subtitle="{{ __('Manage appointments and clinical records.') }}"
        separator />

    {{-- 📊 STATS OVERVIEW --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-mary-stat title="{{ __('Scheduled') }}" value="{{ $stats['scheduled'] }}" icon="o-calendar"
            class="bg-base-100 shadow-sm border border-base-200" color="text-info" />
        <x-mary-stat title="{{ __('Completed') }}" value="{{ $stats['completed'] }}" icon="o-check-circle"
            class="bg-base-100 shadow-sm border border-base-200" color="text-success" />
        <x-mary-stat title="{{ __('Unbilled') }}" value="{{ $stats['pending_billing'] }}" icon="o-currency-dollar"
            class="bg-base-100 shadow-sm border border-base-200" color="text-warning" />
        <x-mary-stat title="{{ __('Total') }}" value="{{ $stats['total'] }}" icon="o-archive-box"
            class="bg-base-100 shadow-sm border border-base-200" />
    </div>

    {{-- 🎛️ CONTROLS BAR --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search patient or therapist...') }}"
                wire:model.live.debounce.300ms="search" class="w-full" />
        </div>

        <div class="flex gap-2 w-full md:w-auto justify-end overflow-x-auto">
            <div class="join">
                <button class="join-item btn btn-sm {{ $statusFilter === '' ? 'btn-neutral' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', '')">{{ __('All') }}</button>
                @foreach ($statuses as $status)
                    <button
                        class="join-item btn btn-sm {{ $statusFilter === $status ? 'btn-active btn-primary' : 'btn-ghost' }}"
                        wire:click="$set('statusFilter', '{{ $status }}')">
                        {{-- ✅ FIX: Translate the label, keep the value raw --}}
                        {{ __($status) }}
                    </button>
                @endforeach
            </div>
            <a href="{{ route('sessions.schedule') }}" class="hidden md:block">
                <x-mary-button label="{{ __('Calendar View') }}" icon="o-calendar-days" class="btn-outline" />
            </a>
        </div>
    </div>

    {{-- 📋 SESSIONS TABLE --}}
    <x-mary-card shadow class="bg-base-100">
        {{-- ✅ FIXED: Using Laravel Route with [id] placeholder --}}
        <x-mary-table container-class="" :headers="$headers" :rows="$sessions" :sort-by="$sortBy" :link="route('sessions.detail', ['session' => '[id]'])"
            class="cursor-pointer hover:bg-base-50" with-pagination>

            {{-- 🗓️ Date & Time --}}
            @scope('cell_scheduled_at', $session)
                <div class="flex flex-col">
                    <span class="font-bold text-gray-800">{{ $session->scheduled_at->translatedFormat('M d, Y') }}</span>
                    <span class="text-xs text-gray-500 flex items-center gap-1">
                        <x-mary-icon name="o-clock" class="w-3 h-3" />
                        {{ $session->scheduled_at->translatedFormat('H:i') }}
                        ({{ $session->duration_minutes }}{{ __('m') }})
                    </span>
                </div>
            @endscope

            {{-- 👤 Patient Info --}}
            @scope('cell_patient.last_name', $session)
                <div class="flex items-center gap-3">
                    <x-mary-avatar :image="$session->patient->avatar" :title="$session->patient->first_name" class="!w-8 !h-8" />
                    <div>
                        <a href="{{ route('patient.health.folder', $session->patient_id) }}"
                            class="font-bold hover:underline hover:text-primary">
                            {{ $session->patient->first_name }} {{ $session->patient->last_name }}
                        </a>
                        <div class="text-xs text-gray-400">{{ $session->focus_area ?? __('General Checkup') }}</div>
                    </div>
                </div>
            @endscope

            {{-- 👨‍⚕️ Therapist Info --}}
            @scope('cell_user.name', $session)
                <div class="text-sm text-gray-600">{{ $session->user->name }}</div>
            @endscope

            {{-- 🏷️ Status Badge --}}
            @scope('cell_status', $session)
                <x-mary-badge :value="__($session->status)"
                    class="font-bold
                    @if ($session->status === 'Completed')
badge-success/10 text-success
@elseif($session->status === 'Scheduled')
badge-info/10 text-info
@elseif($session->status === 'Cancelled')
badge-error/10 text-error
@else
badge-warning/10 text-warning
@endif" />
            @endscope

            {{-- 💲 Billing Badge --}}
            @scope('cell_billing_status', $session)
                <x-mary-badge :value="__($session->billing_status)"
                    class="text-xs
                    @if ($session->billing_status === 'Pending')
badge-warning badge-outline
@elseif($session->billing_status === 'Billed')
badge-success badge-outline
@else
badge-ghost
@endif" />
            @endscope

            {{-- ⚙️ ACTIONS DROPDOWN --}}
            @scope('actions', $session)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-sm btn-ghost btn-circle" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('View Details') }}" icon="o-eye"
                            link="{{ route('sessions.detail', $session->id) }}" />

                        @if ($session->status === 'Scheduled')
                            <x-mary-menu-item title="{{ __('Mark Completed') }}" icon="o-check" class="text-success"
                                wire:click="updateStatus({{ $session->id }}, 'Completed')" />
                            <x-mary-menu-item title="{{ __('Cancel Session') }}" icon="o-x-mark" class="text-warning"
                                wire:click="updateStatus({{ $session->id }}, 'Cancelled')" />
                        @endif

                        @if ($session->billing_status === 'Pending' && $session->status === 'Completed')
                            <x-mary-menu-item title="{{ __('Generate Invoice') }}" icon="o-banknotes"
                                link="{{ route('invoice.generate', ['patient' => $session->patient_id, 'sessionIds' => [$session->id]]) }}" />
                        @endif

                        <x-mary-menu-item title="{{ __('Delete') }}" icon="o-trash" class="text-error"
                            wire:click="confirmDelete({{ $session->id }})" />
                    </x-mary-dropdown>
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- 🗑️ Delete Confirmation Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="{{ __('Delete Session') }}" class="backdrop-blur">
        <div class="mb-5 text-gray-500">
            {{ __('Are you sure you want to delete this session record? This action cannot be undone.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Delete') }}" wire:click="delete" class="btn-error" />
        </x-slot:actions>
    </x-mary-modal>

</div>
