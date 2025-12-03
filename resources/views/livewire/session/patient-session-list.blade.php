<div class="space-y-6">

    {{-- 🟢 HEADER --}}
    <x-page-header title="{{ __('Therapy Sessions') }}"
        subtitle="{{ __('History for') }} {{ $patient->first_name }} {{ $patient->last_name }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('New Session') }}" icon="o-calendar" class="btn-primary"
                link="{{ route('patient.session.create', $patient->id) }}" />
        </x-slot:actions>
    </x-page-header>

    {{-- 🎛️ CONTROLS --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">

        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search focus area...') }}"
                wire:model.live.debounce.300ms="search" />
        </div>

        <div class="flex gap-2 w-full md:w-auto overflow-x-auto">
            <div class="join">
                <button class="join-item btn btn-sm {{ $statusFilter === '' ? 'btn-neutral' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', '')">{{ __('All') }}</button>
                <button
                    class="join-item btn btn-sm {{ $statusFilter === 'Scheduled' ? 'btn-active btn-info text-white' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', 'Scheduled')">{{ __('Scheduled') }}</button>
                <button
                    class="join-item btn btn-sm {{ $statusFilter === 'Completed' ? 'btn-active btn-success text-white' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', 'Completed')">{{ __('Completed') }}</button>
            </div>

            <x-mary-button icon="o-arrow-down-tray" class="btn-ghost btn-sm" tooltip="{{ __('Export History') }}" />
        </div>
    </div>

    {{-- 📋 SESSIONS LIST --}}
    <div class="space-y-4">
        @forelse($sessions as $session)
            <div
                class="bg-base-100 rounded-xl border border-base-200 p-4 shadow-sm hover:border-primary/30 transition-all flex flex-col md:flex-row gap-4 items-start md:items-center">

                {{-- Date Badge --}}
                <div
                    class="flex flex-col items-center justify-center bg-base-200/50 rounded-lg p-3 min-w-[80px] border border-base-200">
                    <span
                        class="text-xs font-bold text-gray-500 uppercase">{{ $session->scheduled_at->translatedFormat('M') }}</span>
                    <span
                        class="text-2xl font-black text-gray-800">{{ $session->scheduled_at->translatedFormat('d') }}</span>
                    <span class="text-xs text-gray-400">{{ $session->scheduled_at->translatedFormat('Y') }}</span>
                </div>

                {{-- Session Info --}}
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-bold text-lg">{{ $session->focus_area }}</h3>
                        <x-mary-badge :value="__($session->status)"
                            class="text-xs font-bold
                            @if ($session->status === 'Completed')
badge-success/10 text-success
@elseif($session->status === 'Scheduled')
badge-info/10 text-info
@elseif($session->status === 'Cancelled')
badge-error/10 text-error
@else
badge-warning/10 text-warning
@endif" />
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                        <div class="flex items-center gap-1">
                            <x-mary-icon name="o-clock" class="w-4 h-4" />
                            {{ $session->scheduled_at->translatedFormat('H:i') }}
                            ({{ $session->duration_minutes }}{{ __('m') }})
                        </div>
                        <div class="flex items-center gap-1">
                            <x-mary-icon name="o-user-circle" class="w-4 h-4" />
                            {{ $session->user->name }}
                        </div>
                        @if ($session->billing_status !== 'Not Applicable')
                            <div class="flex items-center gap-1">
                                <x-mary-icon name="o-currency-dollar" class="w-4 h-4" />
                                <span
                                    class="{{ $session->billing_status === 'Paid' ? 'text-success' : 'text-warning' }}">
                                    {{ __($session->billing_status) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 self-end md:self-center">
                    @if ($session->status === 'Scheduled')
                        <x-mary-button icon="o-check" class="btn-circle btn-sm btn-success text-white"
                            tooltip="{{ __('Complete') }}" wire:click="markCompleted({{ $session->id }})" />
                    @endif

                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-circle btn-sm btn-ghost" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('View Details') }}" icon="o-eye"
                            link="{{ route('sessions.detail', $session->id) }}" />

                        @if ($session->status !== 'Cancelled')
                            <x-mary-menu-item title="{{ __('Edit') }}" icon="o-pencil"
                                link="{{ route('patient.session.edit', ['patient' => $patient->id, 'session' => $session->id]) }}" />
                            <x-mary-menu-item title="{{ __('Duplicate') }}" icon="o-document-duplicate"
                                wire:click="confirmAction({{ $session->id }}, 'duplicate')" />
                            <x-mary-menu-item title="{{ __('Cancel') }}" icon="o-x-mark" class="text-error"
                                wire:click="confirmAction({{ $session->id }}, 'cancel')" />
                            <x-mary-menu-item title="{{ __('No Show') }}" icon="o-user-minus" class="text-warning"
                                wire:click="confirmAction({{ $session->id }}, 'noshow')" />
                        @endif
                    </x-mary-dropdown>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-base-100 rounded-xl border border-dashed border-base-300">
                <x-mary-icon name="o-calendar" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                <h3 class="text-lg font-bold text-gray-500">{{ __('No sessions found') }}</h3>
                <p class="text-sm text-gray-400">{{ __('Try adjusting your filters or schedule a new session.') }}</p>
            </div>
        @endforelse

        {{ $sessions->links() }}
    </div>

    {{-- 🚫 CANCEL MODAL --}}
    <x-mary-modal wire:model="showCancelModal" title="{{ __('Cancel Session') }}" class="backdrop-blur">
        <div class="mb-4 text-gray-600">{{ __('Please provide a reason for cancellation. This will be recorded.') }}
        </div>
        <x-mary-textarea wire:model="reasonText" placeholder="{{ __('Reason...') }}" rows="3" />
        <x-slot:actions>
            <x-mary-button label="{{ __('Back') }}" @click="$wire.showCancelModal = false" />
            <x-mary-button label="{{ __('Confirm Cancel') }}" class="btn-error"
                wire:click="updateStatus('Cancelled')" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- 👻 NO SHOW MODAL --}}
    <x-mary-modal wire:model="showNoShowModal" title="{{ __('Mark as No Show') }}" class="backdrop-blur">
        <div class="mb-4 text-gray-600">{{ __('Did the patient fail to arrive? Add any relevant notes.') }}</div>
        <x-mary-textarea wire:model="reasonText" placeholder="{{ __('Notes...') }}" rows="3" />
        <x-slot:actions>
            <x-mary-button label="{{ __('Back') }}" @click="$wire.showNoShowModal = false" />
            <x-mary-button label="{{ __('Confirm No Show') }}" class="btn-warning"
                wire:click="updateStatus('No Show')" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- 🔁 DUPLICATE MODAL --}}
    <x-mary-modal wire:model="showDuplicateModal" title="{{ __('Duplicate Session') }}" class="backdrop-blur">
        <div class="grid grid-cols-1 gap-4">
            <x-mary-datepicker label="{{ __('New Date') }}" wire:model="dupDate" />
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="{{ __('Time') }}" type="time" wire:model="dupTime" />
                <x-mary-input label="{{ __('Duration (min)') }}" type="number" wire:model="dupDuration" />
            </div>
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDuplicateModal = false" />
            <x-mary-button label="{{ __('Schedule Duplicate') }}" class="btn-primary"
                wire:click="duplicateSession" />
        </x-slot:actions>
    </x-mary-modal>

</div>
