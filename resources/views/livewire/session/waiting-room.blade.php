<div class="space-y-6">
    <x-page-header title="{{ __('Waiting Room') }}" subtitle="{{ __('Patient Flow & Status Management') }}" separator>
        <x-slot:actions>
            <x-mary-datepicker label="{{ __('Date') }}" wire:model.live="date" icon="o-calendar" class="w-48" />
            <x-mary-button label="{{ __('Refresh') }}" icon="o-arrow-path" class="btn-ghost" wire:click="$refresh" />
        </x-slot:actions>
    </x-page-header>

    <div class="flex flex-col lg:flex-row gap-6 overflow-x-auto pb-4 min-h-[calc(100vh-250px)]">

        @foreach ($board as $column)
            <div
                class="flex-1 min-w-[300px] flex flex-col bg-base-100 rounded-xl border border-base-200 shadow-sm h-full">

                {{-- Header --}}
                <div
                    class="p-4 border-b border-base-200 flex justify-between items-center {{ $column['bg'] }} rounded-t-xl">
                    <div class="font-bold flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full border-2 {{ $column['color'] }}"></div>
                        {{ $column['title'] }}
                    </div>
                    <span class="badge badge-sm font-mono">{{ $column['items']->count() }}</span>
                </div>

                {{-- Draggable Area --}}
                {{-- ✅ FIX: Added wire:key with date. This forces Livewire to replace the DIV when date changes. --}}
                <div class="flex-1 p-3 space-y-3 bg-base-50/50 sortable-list" data-status="{{ $column['id'] }}"
                    wire:ignore wire:key="col-{{ $column['id'] }}-{{ $date }}">
                    @foreach ($column['items'] as $session)
                        {{-- Added wire:key to items for performance --}}
                        <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow-sm border border-base-200 cursor-move hover:shadow-md hover:border-primary/50 transition-all group"
                            data-id="{{ $session->id }}" wire:key="session-{{ $session->id }}">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    <x-mary-avatar :image="$session->patient->avatar" class="!w-8 !h-8" />
                                    <div>
                                        <div class="font-bold text-sm leading-tight">
                                            {{ $session->patient->first_name }} {{ $session->patient->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-400">{{ $session->focus_area }}</div>
                                    </div>
                                </div>
                                <div class="text-xs font-mono font-bold text-gray-400">
                                    {{ $session->scheduled_at->translatedFormat('H:i') }}
                                </div>
                            </div>

                            <div
                                class="flex justify-between items-center mt-3 pt-3 border-t border-base-100 dark:border-zinc-700">
                                <div class="text-xs text-gray-500 flex items-center gap-1">
                                    <x-mary-icon name="o-user-circle" class="w-3 h-3" />
                                    {{ $session->user->name }}
                                </div>
                                <a href="{{ route('sessions.detail', $session->id) }}"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    <x-mary-icon name="o-arrow-right-circle"
                                        class="w-5 h-5 text-primary hover:scale-110 transition-transform" />
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <div
                        class="empty-placeholder hidden h-20 border-2 border-dashed border-base-200 rounded-lg flex items-center justify-center text-gray-400 text-sm">
                        {{ __('Drop here') }}
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    // Initialize on load
    initKanban();

    // ✅ FIX: Listen for date change event from PHP to re-init
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('kanban-refresh', () => {
            // Small timeout to allow DOM to settle
            setTimeout(() => {
                initKanban();
            }, 50);
        });
    });

    function initKanban() {
        const columns = document.querySelectorAll('.sortable-list');

        columns.forEach(column => {
            new Sortable(column, {
                group: 'sessions',
                animation: 150,
                ghostClass: 'opacity-50',
                dragClass: 'scale-105',
                delay: 100,
                delayOnTouchOnly: true,
                onEnd: function(evt) {
                    const itemEl = evt.item;
                    const newStatus = evt.to.getAttribute('data-status');
                    const sessionId = itemEl.getAttribute('data-id');

                    if (evt.from !== evt.to) {
                        Livewire.dispatch('status-changed', {
                            sessionId: sessionId,
                            newStatus: newStatus
                        });
                    }
                }
            });
        });
    }
</script>
