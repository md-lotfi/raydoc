<div>
    {{-- 🟢 Header with Actions --}}
    <x-page-header title="{{ __('Dashboard Overview') }}" subtitle="{{ __('Snapshot of your practice performance.') }}"
        separator progress-indicator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-path" class="btn-ghost btn-sm" wire:click="$refresh"
                tooltip="{{ __('Refresh Data') }}" />
            <x-mary-button icon="o-calendar" label="{{ __('This Month') }}" class="btn-outline btn-sm" />
        </x-slot:actions>
    </x-page-header>

    {{-- 📊 1. Key Performance Indicators (KPIs) --}}
    {{-- Improvement: Better responsive grid (1 -> 2 -> 3 -> 5 columns) and hover lift effects --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">

        {{-- Patients --}}
        <x-mary-stat title="{{ __('Total Patients') }}" value="{{ $totalPatients }}" icon="o-user-group"
            class="bg-base-100 shadow-md border border-base-200 hover:-translate-y-1 transition-all duration-300"
            tooltip="{{ __('Total active patients.') }}" />

        {{-- Sessions --}}
        <x-mary-stat title="{{ __('Sessions') }}" description="{{ __('This Month') }}" value="{{ $sessionsThisMonth }}"
            icon="o-calendar-days"
            class="bg-base-100 shadow-md border border-base-200 hover:-translate-y-1 transition-all duration-300"
            color="text-info" />

        {{-- Pending Invoices (Warning Color Background for Urgency) --}}
        <x-mary-stat title="{{ __('Pending Invoices') }}" value="{{ $pendingInvoicesCount }}" icon="o-receipt-percent"
            class="bg-warning/10 shadow-md border border-warning/20 hover:-translate-y-1 transition-all duration-300"
            color="text-warning-content" tooltip="{{ __('Invoices sent but unpaid.') }}" />

        {{-- No Shows --}}
        <x-mary-stat title="{{ __('No Shows') }}" value="{{ $absentPatientsThisMonth }}" icon="o-face-frown"
            class="bg-base-100 shadow-md border border-base-200 hover:-translate-y-1 transition-all duration-300"
            color="text-error" tooltip="{{ __('Sessions marked absent this month.') }}" />

        {{-- Revenue (Highlighted Card) --}}
        <x-mary-stat title="{{ __('Revenue (30d)') }}" value="{{ format_currency($revenueLast30Days) }}"
            icon="o-banknotes"
            class="bg-success/10 shadow-md border border-success/20 hover:-translate-y-1 transition-all duration-300"
            color="text-success-content" tooltip="{{ __('Payments in last 30 days.') }}" />
    </div>

    {{-- 📈 2. Analytics Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">

        {{-- Session Status (Takes up 4/12 columns) --}}
        <x-mary-card title="{{ __('Session Distribution') }}" separator shadow
            class="lg:col-span-4 border border-base-200">
            <div class="h-72 relative flex items-center justify-center">
                <x-mary-chart wire:model="sessionStatusChart" class="w-full h-full" />
            </div>
        </x-mary-card>

        {{-- Revenue Trend (Takes up 8/12 columns) --}}
        <x-mary-card title="{{ __('Financial Performance') }}"
            subtitle="{{ __('Weekly revenue over the last month') }}" separator shadow
            class="lg:col-span-8 border border-base-200">
            <x-slot:menu>
                <x-mary-button icon="o-document-chart-bar" label="{{ __('Full Report') }}" class="btn-xs btn-ghost"
                    link="#" />
            </x-slot:menu>

            <div class="h-72">
                <x-mary-chart wire:model="weeklyRevenueChart" class="w-full h-full" />
            </div>
        </x-mary-card>
    </div>

    {{-- 📌 3. Actionable Items (Unbilled Sessions) --}}
    <x-mary-card title="{{ __('Ready to Bill') }}"
        subtitle="{{ __('Completed sessions pending invoice generation') }}" shadow separator
        class="border border-base-200">
        <x-slot:menu>
            <div class="badge badge-warning gap-2">
                {{ $recentUnbilledSessions->count() }} {{ __('Pending') }}
            </div>
        </x-slot:menu>

        @php
            $sessionHeaders = [
                ['key' => 'patient.first_name', 'label' => __('Patient')],
                ['key' => 'focus_area', 'label' => __('Focus')],
                ['key' => 'actual_end_at', 'label' => __('Date'), 'class' => 'hidden md:table-cell'], // Hide on mobile
                ['key' => 'duration_minutes', 'label' => __('Duration'), 'class' => 'text-center'],
                ['key' => 'actions', 'label' => '', 'sortable' => false],
            ];
        @endphp

        @if ($recentUnbilledSessions->isNotEmpty())
            <x-mary-table :headers="$sessionHeaders" :rows="$recentUnbilledSessions" :no-pagination="true" class="table-md">

                {{-- Patient Name & Avatar --}}
                @scope('cell_patient.first_name', $session)
                    <div class="flex items-center gap-3">
                        <x-mary-avatar :image="$session->patient->avatar ?? null" :title="$session->patient->first_name" class="!w-8 !h-8 bg-gray-200" />
                        <a href="{{ route('patient.health.folder', $session->patient_id) }}"
                            class="font-bold hover:underline hover:text-primary transition">
                            {{ $session->patient->first_name }} {{ $session->patient->last_name }}
                        </a>
                    </div>
                @endscope

                {{-- Date Formatting --}}
                @scope('cell_actual_end_at', $session)
                    <div class="text-gray-500 text-sm">
                        {{ \Carbon\Carbon::parse($session->actual_end_at)->translatedFormat('M d, Y') }}
                    </div>
                @endscope

                {{-- Duration Badge --}}
                @scope('cell_duration_minutes', $session)
                    <div class="flex justify-center">
                        <span
                            class="badge badge-ghost font-mono">{{ __('min :min', ['min' => $session->duration_minutes]) }}</span>
                    </div>
                @endscope

                {{-- Action Button --}}
                @scope('actions', $session)
                    <div class="flex justify-end">
                        <x-mary-button icon="o-currency-dollar" label="{{ __('Invoice') }}"
                            class="btn-sm btn-warning btn-outline"
                            link="{{ route('invoice.generate', ['patient' => $session->patient_id, 'sessionIds' => [$session->id]]) }}" />
                    </div>
                @endscope

            </x-mary-table>
        @else
            {{-- Attractive Empty State --}}
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="bg-base-200 p-4 rounded-full mb-3">
                    <x-mary-icon name="o-check-circle" class="w-10 h-10 text-success" />
                </div>
                <h3 class="font-bold text-lg">{{ __('All Caught Up!') }}</h3>
                <p class="text-gray-500">{{ __('There are no pending sessions to bill at this time.') }}</p>
            </div>
        @endif
    </x-mary-card>
</div>
