<div>
    <x-mary-card title="{{ __('Weekly Timeline Schedule') }}" shadow separator>

        <x-slot:menu>
            <x-mary-button label="{{ __('New Session') }}" icon="o-plus" class="btn-primary"
                link="{{ route('patient.list') }}" />
        </x-slot:menu>

        {{-- Controls and Navigation (Unchanged) --}}
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-2">
                <x-mary-button icon="o-arrow-small-left" wire:click="navigate('prev')" class="btn-ghost" />

                {{-- Display current period --}}
                <h3 class="text-xl font-semibold" x-data="{ title: '{{ $scheduleTitle }}' }"
                    x-on:schedule-updated.window="title = $event.detail.title">
                    <span x-text="title"></span>
                </h3>

                <x-mary-button icon="o-arrow-small-right" wire:click="navigate('next')" class="btn-ghost" />

                <x-mary-button label="{{ __('Current Week') }}"
                    wire:click="$set('currentDate', '{{ \Carbon\Carbon::now()->toDateString() }}')"
                    class="btn-sm btn-outline" />
            </div>
        </div>

        <hr class="mb-6" />

        {{-- 📅 TIMELINE CONTAINER --}}
        {{-- Alpine data and logic (Unchanged) --}}
        <div x-data="{
            // Function to calculate session position (0-1440 minutes in a day)
            sessionStyle(scheduledAt, duration) {
                    const date = new Date(scheduledAt);
                    const hours = date.getHours();
                    const minutes = date.getMinutes();
                    const startMinutes = (hours * 60) + minutes; // Total minutes from midnight
        
                    // Timeline width is 100%. A day is 1440 minutes (24 * 60).
                    const leftPercentage = (startMinutes / 1440) * 100;
                    const widthPercentage = (duration / 1440) * 100;
        
                    return `left: ${leftPercentage}%; width: ${widthPercentage}%;`;
                },
        
                // Helper to determine status color
                getStatusClass(status) {
                    switch (status) {
                        case 'Completed':
                            return 'bg-success hover:bg-success-focus';
                        case 'Scheduled':
                            return 'bg-info hover:bg-info-focus';
                        case 'Cancelled':
                            return 'bg-error/50 hover:bg-error/70';
                        case 'No Show':
                            return 'bg-warning hover:bg-warning-focus';
                        default:
                            return 'bg-gray-500';
                    }
                }
        }" class="flex overflow-hidden border rounded-lg"> {{-- 👈 NEW: Outer Flex Container --}}

            {{-- 1. 📅 STATIC DAY COLUMN --}}
            <div class="w-[100px] flex-shrink-0 bg-base-200 border-r border-gray-300">

                {{-- TOP CORNER (for Hour Header alignment) --}}
                <div class="h-[36px] border-b border-gray-300"></div>

                {{-- Day Labels --}}
                @php
                    $dayLabels = $weekStart->copy();
                @endphp
                @for ($i = 0; $i < 7; $i++)
                    <div
                        class="h-16 border-b border-gray-300 text-sm font-semibold flex items-center justify-center p-2">
                        {{ $dayLabels->format('D') }} <br /> ({{ $dayLabels->format('M j') }})
                    </div>
                    @php $dayLabels->addDay(); @endphp
                @endfor
            </div>

            {{-- 2. 🕒 SCROLLING TIMELINE CONTENT --}}
            <div class="overflow-x-auto grow">
                <div class="min-w-[1200px]"> {{-- This minimum width ensures horizontal scrolling --}}

                    {{-- TIMELINE HEADER (Hours) --}}
                    {{-- Adjusted class for height and border to align with the Day Labels' top corner --}}
                    <div class="grid grid-cols-24 border-b border-gray-300 bg-base-200 h-[36px] sticky top-0 z-10">
                        @foreach (range(0, 23) as $hour)
                            <div class="text-center text-xs p-1 border-r border-gray-300 last:border-r-0">
                                {{ $hour }}:00
                            </div>
                        @endforeach
                    </div>

                    {{-- TIMELINE ROWS (Days) --}}
                    @php
                        $currentDay = $weekStart->copy();
                    @endphp

                    @for ($i = 0; $i < 7; $i++)
                        @php
                            $dayKey = $currentDay->format('Y-m-d');
                            $sessions = $sessionsByDay[$dayKey] ?? collect();
                        @endphp

                        <div class="relative h-16 border-b border-gray-300 hover:bg-base-100">

                            {{-- Day Grid Lines (Visual helper) --}}
                            <div class="grid grid-cols-24 h-full">
                                @foreach (range(0, 23) as $hour)
                                    <div class="border-r border-gray-200"></div>
                                @endforeach
                            </div>

                            {{-- Session Rectangles --}}
                            @foreach ($sessions as $session)
                                @php
                                    $scheduledAt = $session->scheduled_at->toDateTimeString();
                                    $duration = $session->duration_minutes;
                                @endphp

                                <a href="{{ route('sessions.detail', $session) }}" style=""
                                    x-bind:style="sessionStyle('{{ $scheduledAt }}', {{ $duration }})"
                                    :class="[getStatusClass('{{ $session->status }}')]"
                                    class="absolute top-1 bottom-1 text-white text-xs p-1 rounded-md shadow-md cursor-pointer transition-all duration-150 ease-in-out z-30 overflow-hidden whitespace-nowrap"
                                    title="{{ $session->patient->first_name }} - {{ $session->scheduled_at->format('H:i') }}">
                                    <span class="font-semibold">{{ $session->scheduled_at->format('H:i') }}</span>
                                    <span class="hidden sm:inline"> - {{ $session->patient->first_name }}</span>
                                </a>
                            @endforeach
                        </div>

                        @php
                            $currentDay->addDay();
                        @endphp
                    @endfor

                </div>
            </div> {{-- End SCROLLING TIMELINE CONTENT --}}

        </div> {{-- End OUTER FLEX CONTAINER --}}
    </x-mary-card>
</div>
