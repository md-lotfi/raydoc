<div class="space-y-8">

    {{-- HEADER (Unchanged) --}}
    <x-page-header title="{{ __('Patient Record') }}"
        subtitle="{{ __('Comprehensive dashboard for clinical and financial history.') }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Print Report') }}" icon="o-printer" class="btn-ghost" onclick="window.print()" />
            <x-mary-button label="{{ __('Edit Profile') }}" icon="o-pencil" class="btn-ghost"
                link="{{ route('patient.edit', $patient->id) }}" />
        </x-slot:actions>
    </x-page-header>

    {{-- PATIENT HEADER CARD (Unchanged) --}}
    <div
        class="flex flex-col lg:flex-row gap-6 items-start justify-between bg-base-100 p-6 rounded-xl shadow-sm border border-base-200">
        <div class="flex items-center gap-5">
            <x-mary-avatar :image="$patient->avatar" :title="$patient->first_name . ' ' . $patient->last_name"
                class="!w-24 !h-24 text-3xl border-4 border-base-100 shadow-lg" />
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $patient->first_name }} {{ $patient->last_name }}
                    </h1>
                    <x-mary-badge :value="$patient->is_active ? __('Active') : __('Archived')"
                        class="{{ $patient->is_active ? 'badge-success' : 'badge-ghost' }}" />
                </div>
                <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-500">
                    <div class="flex items-center gap-1.5 bg-base-200/50 px-2 py-1 rounded">
                        <x-mary-icon name="o-identification" class="w-4 h-4" />
                        <span class="font-mono font-semibold">#{{ $patient->id }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-base-200/50 px-2 py-1 rounded">
                        <x-mary-icon name="o-cake" class="w-4 h-4" />
                        <span>{{ $patient->date_of_birth?->age ?? '?' }} {{ __('Years Old') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2 w-full lg:w-auto">
            <x-mary-button label="{{ __('Schedule') }}" icon="o-calendar" class="btn-primary flex-1 lg:flex-none"
                link="{{ route('patient.session.create', $patient->id) }}" />
        </div>
    </div>

    {{-- TABS --}}
    <x-mary-tabs wire:model="currentTab" active-class="bg-primary/5 text-primary border-primary font-bold">

        {{-- TAB 1: OVERVIEW (Unchanged) --}}
        <x-mary-tab name="overview" label="{{ __('Overview') }}" icon="o-squares-2x2">
            {{-- ... (Keep existing overview content) ... --}}
            <div class="grid lg:grid-cols-3 gap-6 mt-6">
                {{-- Profile --}}
                <div class="lg:col-span-1 space-y-6">
                    <x-mary-card title="{{ __('Contact Information') }}" separator shadow class="h-full">
                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between py-2 border-b border-base-200">
                                <span class="text-gray-500">{{ __('Email') }}</span>
                                <span class="font-medium">{{ $patient->email }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-200">
                                <span class="text-gray-500">{{ __('Phone') }}</span>
                                <span class="font-medium">{{ $patient->phone_number ?? '-' }}</span>
                            </div>
                            @if ($patient->address)
                                <div class="pt-2">
                                    <span class="text-gray-500 block mb-2">{{ __('Physical Address') }}</span>
                                    <div class="p-3 bg-base-200 rounded-lg text-gray-700">{{ $patient->address }}</div>
                                </div>
                            @endif
                        </div>
                    </x-mary-card>
                </div>

                {{-- Charts --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="grid lg:grid-cols-2 gap-6">
                        <x-mary-card title="{{ __('Session Performance') }}" separator shadow>
                            <div class="h-64 relative flex items-center justify-center">
                                <x-mary-chart wire:model="sessionChart" class="w-full h-full" />
                            </div>
                        </x-mary-card>

                        <x-mary-card title="{{ __('Recent Activity') }}" separator shadow>
                            <div class="space-y-6">
                                @foreach (collect($this->clinicalTimeline)->take(5) as $event)
                                    <div class="flex gap-4 items-start">
                                        <div class="mt-1">
                                            <div
                                                class="w-2.5 h-2.5 rounded-full {{ $event['type'] == 'session' ? 'bg-primary' : 'bg-warning' }} ring-4 ring-base-100 shadow-sm">
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-gray-400 mb-0.5">
                                                {{ $event['date']->translatedFormat('M d, Y') }}</div>
                                            <div class="font-semibold text-sm text-gray-800">{{ $event['title'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-mary-card>
                    </div>
                </div>
            </div>
        </x-mary-tab>

        {{-- 🩺 TAB 2: CLINICAL HISTORY (Timeline) --}}
        <x-mary-tab name="clinical" label="{{ __('Clinical History') }}" icon="o-heart">

            <div class="flex justify-end mb-6">
                <x-mary-button label="{{ __('Add Diagnosis') }}" icon="o-plus" class="btn-outline btn-sm"
                    link="{{ route('patient.diagnosis.create', $patient->id) }}" />
            </div>

            <div class="grid lg:grid-cols-12 gap-8">

                {{-- 🕒 Timeline (8 Cols) --}}
                <div class="lg:col-span-8">
                    <x-mary-card title="{{ __('Health Timeline') }}"
                        subtitle="{{ __('Chronological history of diagnoses, sessions, and events.') }}" separator
                        shadow>

                        <div class="px-2 py-6">
                            <div class="space-y-0">
                                @forelse($this->clinicalTimeline as $event)
                                    {{-- ✅ FIX: Use GRID Layout instead of absolute positioning --}}
                                    <div class="grid grid-cols-[80px_auto_1fr] gap-4 group min-h-[100px]">

                                        {{-- 1. Date Column (Inside the frame) --}}
                                        <div class="text-end pt-2">
                                            <div class="font-bold text-gray-600 text-sm leading-tight">
                                                {{ $event['date']->translatedFormat('M d') }}</div>
                                            <div class="text-xs text-gray-400 font-mono">
                                                {{ $event['date']->translatedFormat('Y') }}</div>
                                        </div>

                                        {{-- 2. Axis Column (Line + Icon) --}}
                                        <div class="relative flex flex-col items-center">
                                            {{-- The Vertical Line --}}
                                            <div
                                                class="absolute top-0 bottom-0 w-px bg-base-300 group-last:bottom-auto group-last:h-full">
                                            </div>

                                            {{-- The Icon --}}
                                            <div
                                                class="z-10 flex items-center justify-center w-8 h-8 rounded-full border-2 border-base-100 dark:border-zinc-800 {{ $event['color'] }} shadow-sm">
                                                <x-mary-icon name="{{ $event['icon'] }}" class="w-4 h-4" />
                                            </div>
                                        </div>

                                        {{-- 3. Content Column --}}
                                        <div class="pb-8">
                                            <div class="bg-base-100 rounded-xl border {{ $event['border'] }} p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer relative overflow-hidden"
                                                @if ($event['type'] === 'session') wire:click="previewSession({{ $event['id'] }})"
                                                @elseif($event['type'] === 'diagnosis')
                                                    onclick="window.location.href='{{ route('patient.diagnosis.detail', $event['id']) }}'" @endif>
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h3 class="font-bold text-base text-gray-900 dark:text-white">
                                                            {{ $event['title'] }}</h3>
                                                        <p class="text-sm text-gray-500 mt-0.5">
                                                            {{ $event['subtitle'] }}</p>
                                                    </div>
                                                    <div
                                                        class="badge badge-ghost font-mono text-[10px] bg-base-200 border-base-300">
                                                        {{ $event['details'] }}
                                                    </div>
                                                </div>

                                                {{-- Hover Action Hint --}}
                                                <div
                                                    class="mt-3 pt-2 border-t border-base-200/60 flex items-center gap-2 text-xs font-medium text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <x-mary-icon name="o-eye" class="w-3 h-3" />
                                                    <span>{{ __('View Details') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div
                                        class="flex flex-col items-center justify-center py-12 text-center border-2 border-dashed border-base-200 rounded-xl">
                                        <x-mary-icon name="o-clipboard" class="w-12 h-12 text-gray-300 mb-2" />
                                        <p class="text-gray-500 font-medium">{{ __('No clinical history found.') }}</p>
                                        <p class="text-sm text-gray-400">
                                            {{ __('Events will appear here once recorded.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </x-mary-card>
                </div>

                {{-- 👉 RIGHT: Clinical Context (4 Cols) --}}
                <div class="lg:col-span-4 space-y-6">

                    {{-- Active Conditions --}}
                    <x-mary-card title="{{ __('Active Conditions') }}" shadow separator
                        class="border-t-4 border-error">
                        @forelse($patient->diagnoses->where('condition_status', 'Active') as $diagnosis)
                            <div class="flex gap-3 mb-4 last:mb-0 items-start">
                                <div class="mt-0.5 bg-error/10 p-1.5 rounded-lg text-error">
                                    <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-gray-800">{{ $diagnosis->icdCode->code }}</div>
                                    <div class="text-xs text-gray-500 leading-tight mt-0.5">
                                        {{ $diagnosis->icdCode->description }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-gray-400 text-sm text-center py-4 italic">
                                {{ __('No active conditions') }}</div>
                        @endforelse
                    </x-mary-card>

                    {{-- Quick Stats --}}
                    <div class="grid grid-cols-1 gap-4">
                        <x-mary-stat title="{{ __('Total Sessions') }}"
                            value="{{ $patient->therapySessions->count() }}" icon="o-calendar"
                            class="shadow-sm border border-base-200" />
                        <x-mary-stat title="{{ __('Last Visit') }}"
                            value="{{ $patient->therapySessions->sortByDesc('scheduled_at')->first()?->scheduled_at->diffForHumans() ?? 'N/A' }}"
                            icon="o-clock" class="shadow-sm border border-base-200" />
                    </div>
                </div>
            </div>
        </x-mary-tab>

        {{-- 💰 TAB 3: FINANCIALS (Unchanged) --}}
        <x-mary-tab name="financials" label="{{ __('Financials') }}" icon="o-banknotes">
            <div class="grid lg:grid-cols-3 gap-6 mt-6">
                <x-mary-stat title="{{ __('Total Billed') }}"
                    value="{{ format_currency($this->billingStats['total_billed']) }}" icon="o-currency-dollar"
                    class="shadow-sm border border-base-200" />
                <x-mary-stat title="{{ __('Paid') }}"
                    value="{{ format_currency($this->billingStats['total_paid']) }}" icon="o-check"
                    class="shadow-sm border border-base-200 text-success" />
                <x-mary-stat title="{{ __('Outstanding') }}"
                    value="{{ format_currency($this->billingStats['outstanding']) }}" icon="o-exclamation-circle"
                    class="shadow-sm border border-base-200 text-error" />
            </div>

            <x-mary-card title="{{ __('Invoices') }}" class="mt-6" shadow separator>
                <x-mary-table :headers="$invoiceHeaders" :rows="$patient->invoices->sortByDesc('issued_date')" :no-pagination="true">
                    @scope('cell_status', $invoice)
                        <x-mary-badge :value="$invoice->status"
                            class="badge-sm font-semibold {{ $invoice->status == 'Paid' ? 'badge-success/10 text-success' : 'badge-ghost' }}" />
                    @endscope
                    @scope('cell_amount_due', $invoice)
                        <span
                            class="{{ $invoice->amount_due > 0 ? 'text-error font-bold' : 'text-gray-400' }}">{{ format_currency($invoice->amount_due) }}</span>
                    @endscope
                    @scope('actions', $invoice)
                        <x-mary-button icon="o-eye" class="btn-sm btn-ghost"
                            link="{{ route('invoice.show', $invoice->id) }}" />
                    @endscope
                </x-mary-table>
            </x-mary-card>
        </x-mary-tab>

    </x-mary-tabs>

    {{-- 🔎 SESSION DETAILS DRAWER --}}
    <x-mary-drawer wire:model="showSessionDrawer" title="{{ __('Session Details') }}" right separator
        class="w-11/12 lg:w-1/3">
        @if ($selectedSession)
            <div class="space-y-6 p-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-primary">
                        {{ $selectedSession->focus_area ?? __('Regular Session') }}</h3>
                    <x-mary-badge :value="$selectedSession->status" class="badge-lg" />
                </div>

                <div class="grid grid-cols-2 gap-4 bg-base-200 p-4 rounded-xl">
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Date') }}</div>
                        <div class="font-bold text-lg">
                            {{ $selectedSession->scheduled_at->translatedFormat('M d, Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Time') }}</div>
                        <div class="font-bold text-lg">{{ $selectedSession->scheduled_at->format('H:i') }}</div>
                    </div>
                </div>

                @if ($selectedSession->notes)
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase mb-2">{{ __('Clinical Notes') }}</div>
                        <div
                            class="bg-amber-50 p-4 rounded-lg border border-amber-100 text-gray-800 italic leading-relaxed text-sm">
                            "{{ $selectedSession->notes }}"
                        </div>
                    </div>
                @endif

                <div class="pt-4 mt-auto">
                    <x-mary-button label="{{ __('Open Full Session Record') }}" icon="o-arrow-top-right-on-square"
                        class="btn-outline w-full" link="{{ route('sessions.detail', $selectedSession->id) }}" />
                </div>
            </div>
        @endif
    </x-mary-drawer>

</div>
