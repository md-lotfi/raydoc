<div class="space-y-6">
    {{-- 🟢 Header with Actions --}}
    {{-- 🟢 Header with Interactive Date Navigation --}}
    <x-page-header title="{{ __('Schedule') }}" subtitle="{{ __('Manage your weekly appointments.') }}" separator>
        <x-slot:actions>

            {{-- Interactive Date Navigator --}}
            <div class="flex items-center gap-2 bg-base-100 p-1 rounded-lg border border-base-300 shadow-sm">

                {{-- ⬅️ Previous Week --}}
                <x-mary-button icon="o-chevron-left" wire:click="previousWeek"
                    class="btn-sm btn-ghost text-base-content/70 hover:text-primary"
                    tooltip="{{ __('Previous Week') }}" />

                {{-- 📅 Interactive Date Picker (Overlay) --}}
                <div class="relative group">
                    {{-- Hidden Native Date Input (Triggers Picker on Click) --}}
                    <input type="date" wire:model.live="date"
                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10"
                        title="{{ __('Pick a specific date') }}" />

                    {{-- Visible Label --}}
                    <div
                        class="btn btn-sm btn-ghost font-mono font-bold min-w-[160px] flex items-center gap-2 group-hover:bg-base-200 transition-colors">
                        <x-mary-icon name="o-calendar"
                            class="w-4 h-4 text-primary opacity-70 group-hover:opacity-100" />
                        <span>{{ $startOfWeek->translatedFormat('M d') }}</span>
                        <span class="opacity-40">-</span>
                        <span>{{ $endOfWeek->translatedFormat('M d, Y') }}</span>
                    </div>
                </div>

                {{-- ➡️ Next Week --}}
                <x-mary-button icon="o-chevron-right" wire:click="nextWeek"
                    class="btn-sm btn-ghost text-base-content/70 hover:text-primary" tooltip="{{ __('Next Week') }}" />
            </div>

            {{-- 🔄 Reset to Today (Separate Quick Action) --}}
            <x-mary-button label="{{ __('Today') }}" wire:click="today" class="btn-sm btn-outline ml-2"
                icon="o-calendar-days" />

            {{-- New Session Button --}}
            <x-mary-button label="{{ __('New Session') }}" icon="o-plus" class="btn-primary btn-sm ml-2"
                wire:click="$set('showCreateModal', true)" />
        </x-slot:actions>
    </x-page-header>

    {{-- 🗓️ Weekly Grid (Unchanged) --}}
    <div class="grid grid-cols-1 lg:grid-cols-7 gap-4 lg:gap-2">
        @foreach ($weekGrid as $date => $dayData)
            <div
                class="flex flex-col min-h-[400px] rounded-xl bg-base-100 border {{ $dayData['is_today'] ? 'border-primary/50 shadow-md ring-1 ring-primary/20' : 'border-base-200' }}">
                <div class="p-3 text-center border-b border-base-200 bg-base-50/50 rounded-t-xl">
                    <div class="text-xs uppercase font-bold tracking-wider opacity-60">{{ $dayData['day_name'] }}
                    </div>
                    <div class="text-2xl font-black {{ $dayData['is_today'] ? 'text-primary' : '' }}">
                        {{ $dayData['day_number'] }}</div>
                </div>

                <div class="flex-1 p-2 space-y-2">
                    @forelse($dayData['sessions'] as $session)
                        <div wire:click="selectSession({{ $session->id }})"
                            class="cursor-pointer p-2 rounded-lg border text-xs hover:shadow-md transition-all bg-base-100 border-base-300">
                            <div class="flex justify-between mb-1">
                                <span
                                    class="font-bold">{{ \Carbon\Carbon::parse($session->scheduled_at)->translatedFormat('H:i') }}</span>
                            </div>
                            <div class="font-semibold truncate">{{ $session->patient->first_name }}
                                {{ $session->patient->last_name }}</div>
                        </div>
                    @empty
                        {{-- Ghost Slot --}}
                        <div class="h-full flex items-center justify-center opacity-10">
                            <span class="text-xs">{{ __('No events') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- 🔎 Session Detail Drawer (Same as before) --}}
    <x-mary-drawer wire:model="showDrawer" class="w-11/12 lg:w-1/3" title="{{ __('Session Details') }}" right>
        @if ($selectedSession)
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <x-mary-avatar :image="$selectedSession->patient->avatar ?? null" :title="$selectedSession->patient->first_name" />
                    <div class="font-bold text-lg">{{ $selectedSession->patient->first_name }}
                        {{ $selectedSession->patient->last_name }}</div>
                </div>
                <hr />
                <div class="grid grid-cols-2 gap-4">
                    <x-mary-stat title="Date"
                        value="{{ \Carbon\Carbon::parse($selectedSession->scheduled_at)->translatedFormat('M d') }}" />
                    <x-mary-stat title="Time"
                        value="{{ \Carbon\Carbon::parse($selectedSession->scheduled_at)->translatedFormat('H:i') }}" />
                </div>
                <x-mary-button label="Edit Session"
                    link="{{ route('patient.session.edit', ['patient' => $selectedSession->patient_id, 'session' => $selectedSession->id]) }}"
                    class="btn-primary w-full" />
            </div>
        @endif
    </x-mary-drawer>

    {{-- 🆕 PATIENT SELECTION MODAL --}}
    <x-mary-modal wire:model="showCreateModal" title="{{ __('Select Patient') }}" class="backdrop-blur">
        <div class="mb-4 text-sm text-gray-500">
            {{ __('Who is this session for? Search for a registered patient to continue.') }}
        </div>

        <x-mary-input label="{{ __('Search Patient') }}" icon="o-magnifying-glass"
            placeholder="{{ __('Type name or email...') }}" wire:model.live.debounce.300ms="patientSearch" autofocus />

        <div class="mt-4 space-y-2">
            @forelse($foundPatients as $patient)
                <div wire:click="createSessionForPatient({{ $patient->id }})"
                    class="flex items-center justify-between p-3 rounded-lg border border-base-200 hover:bg-base-200 cursor-pointer transition-colors">
                    <div class="flex items-center gap-3">
                        <x-mary-avatar :image="$patient->avatar_url ?? null" :title="$patient->first_name" class="!w-10 !h-10" />
                        <div>
                            <div class="font-bold">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                            <div class="text-xs text-gray-400">{{ $patient->email }}</div>
                        </div>
                    </div>
                    <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-gray-400" />
                </div>
            @empty
                @if (strlen($patientSearch) > 1)
                    <div class="text-center py-4 text-gray-500">
                        {{ __('No patients found.') }}
                        <a href="{{ route('patient.create') }}"
                            class="text-primary hover:underline">{{ __('Create new?') }}</a>
                    </div>
                @endif
            @endforelse
        </div>
    </x-mary-modal>
</div>
