<div class="space-y-6">
    <x-page-header title="{!! __('Waiting Room') !!}" subtitle="{{ __('Patient Flow & Status Management') }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Call Next Patient') }}" icon="o-megaphone" class="btn-primary"
                wire:click="callNextPatient" spinner="callNextPatient" />
            <x-mary-datepicker label="{{ __('Date') }}" wire:model.live="date" icon="o-calendar" class="w-48" />
            <x-mary-button label="{{ __('Refresh') }}" icon="o-arrow-path" class="btn-ghost" wire:click="$refresh" />
        </x-slot:actions>
    </x-page-header>

    {{-- Scrollable Container --}}
    <div class="flex flex-col lg:flex-row gap-6 overflow-x-auto pb-4 min-h-[calc(100vh-250px)]">

        @foreach ($board as $column)
            <div
                class="flex-1 min-w-[300px] flex flex-col bg-base-100 rounded-xl border border-base-200 shadow-sm h-full">

                {{-- Column Header --}}
                <div
                    class="p-4 border-b border-base-200 flex justify-between items-center {{ $column['bg'] }} rounded-t-xl">
                    <div class="font-bold flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full border-2 {{ $column['color'] }}"></div>
                        {{ $column['title'] }}
                    </div>
                    <span class="badge badge-sm font-mono">{{ $column['items']->count() }}</span>
                </div>

                {{-- Draggable Zone --}}
                <div class="flex-1 p-3 space-y-3 bg-base-50/50 sortable-list" data-status="{{ $column['id'] }}"
                    wire:ignore wire:key="col-{{ $column['id'] }}-{{ $date }}-{{ $refreshKey }}">
                    @foreach ($column['items'] as $session)
                        {{-- Draggable Card --}}
                        <div class="draggable-item bg-white dark:bg-zinc-800 p-4 rounded-lg shadow-sm border border-base-200 cursor-move hover:shadow-md hover:border-primary/50 transition-all group relative"
                            data-id="{{ $session->id }}" wire:key="session-{{ $session->id }}">
                            {{-- Card Content (Unchanged) --}}
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    <x-mary-avatar :image="$session->patient->avatar" class="!w-8 !h-8" />
                                    <div>
                                        <div class="font-bold text-sm leading-tight">
                                            {{ $session->patient->first_name }} {{ $session->patient->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-400">{{ $session->focus_area }}</div>

                                        <span
                                            class="badge badge-xs font-semibold border-0
                                            {{ $session->session_type === 'appointment' ? 'bg-info/10 text-info' : 'bg-purple-100 text-purple-700' }}">

                                            {{-- Icon --}}
                                            <x-mary-icon
                                                name="{{ $session->session_type === 'appointment' ? 'o-calendar' : 'o-user' }}"
                                                class="w-3 h-3 me-1" />

                                            {{-- Label --}}
                                            {{ $session->session_type === 'appointment' ? __('Appointment') : __('Walk-in') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-xs font-mono font-bold text-gray-400">
                                    {{ $session->scheduled_at->translatedFormat('H:i') }}
                                </div>
                            </div>

                            {{-- Footer Actions --}}
                            <div
                                class="flex justify-between items-center mt-3 pt-3 border-t border-base-100 dark:border-zinc-700">
                                <div class="text-xs text-gray-500 flex items-center gap-1">
                                    <x-mary-icon name="o-user-circle" class="w-3 h-3" />
                                    {{ $session->user->name }}
                                </div>

                                <div class="flex items-center gap-1">
                                    {{-- Quick Link --}}
                                    <a href="{{ route('sessions.detail', $session->id) }}"
                                        class="btn btn-ghost btn-circle btn-xs">
                                        <x-mary-icon name="o-eye" class="w-4 h-4" />
                                    </a>

                                    {{-- ⚙️ Dropdown for Quick Status Changes --}}
                                    <x-mary-dropdown right>
                                        <x-slot:trigger>
                                            <x-mary-icon name="o-ellipsis-vertical"
                                                class="w-4 h-4 cursor-pointer text-gray-400 hover:text-primary" />
                                        </x-slot:trigger>

                                        @if ($session->status !== 'No Show')
                                            <x-mary-menu-item title="{{ __('Mark Absent') }}" icon="o-user-minus"
                                                class="text-error"
                                                wire:click="updateStatus({{ $session->id }}, 'No Show')" />
                                        @endif

                                        @if ($session->status === 'No Show')
                                            <x-mary-menu-item title="{{ __('Re-queue (Check In)') }}"
                                                icon="o-arrow-path" class="text-warning"
                                                wire:click="updateStatus({{ $session->id }}, 'Checked In')" />
                                        @endif
                                    </x-mary-dropdown>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Empty placeholder (visible only if list is empty via CSS or JS if needed) --}}
                    <div
                        class="empty-placeholder hidden h-20 border-2 border-dashed border-base-200 rounded-lg flex items-center justify-center text-gray-400 text-sm">
                        {{ __('Drop here') }}
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>

{{-- Load SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

{{-- Livewire 3 Script Logic --}}
@script
    <script>
        let sortables = [];

        function initKanban() {
            // Cleanup old instances to prevent memory leaks/duplicates
            sortables.forEach(instance => instance.destroy());
            sortables = [];

            const columns = document.querySelectorAll('.sortable-list');

            columns.forEach(column => {
                let sortable = new Sortable(column, {
                    group: 'sessions', // Allow dragging between lists
                    animation: 150,
                    delay: 100, // Prevent accidental drags on touch
                    delayOnTouchOnly: true,
                    draggable: '.draggable-item', // Only drag the cards, ignore other elements
                    ghostClass: 'opacity-50',

                    onEnd: function(evt) {
                        // If dropped in the same list, do nothing
                        if (evt.from === evt.to) return;

                        const itemEl = evt.item;
                        const newStatus = evt.to.getAttribute('data-status');
                        const sessionId = itemEl.getAttribute('data-id');

                        // Call the Livewire method directly
                        $wire.updateStatus(sessionId, newStatus);
                    }
                });

                sortables.push(sortable);
            });
        }

        // Initialize on first load
        initKanban();

        // Re-initialize when the date changes (triggered from PHP)
        $wire.on('kanban-refresh', () => {
            setTimeout(() => {
                initKanban();
            }, 50); // Small delay to ensure DOM is updated
        });
    </script>
@endscript
