<div>

    {{-- HEADER --}}
    <x-mary-header title="{{ __('Patient Record') }}"
        subtitle="{{ $patient->first_name . ' ' . $patient->last_name . ' | ID: ' . $patient->id }}">
        {{-- Optional: Add Edit/Print button --}}
        <x-slot:actions>
            <x-mary-button label="{{ __('Edit Patient') }}" icon="o-pencil" class="btn-warning"
                link="{{ route('patient.edit', $patient) }}" />
        </x-slot:actions>
    </x-mary-header>

    {{-- TABS NAVIGATION --}}
    <x-mary-tabs wire:model.live="currentTab" class="mt-4">
        <x-mary-tab name="profile" label="{{ __('Profile & History') }}" icon="o-user" />
        <x-mary-tab name="diagnosis" label="{{ __('Diagnosis & Clinical') }}" icon="o-clipboard-document-list" />
        <x-mary-tab name="sessions" label="{{ __('Therapy Sessions') }}" icon="o-calendar-days" />
        <x-mary-tab name="billing" label="{{ __('Billing & Payments') }}" icon="o-receipt-percent" />
    </x-mary-tabs>

    <div>

        {{-- 👤 PROFILE TAB --}}
        @if ($currentTab === 'profile')
            <div class="grid lg:grid-cols-3 gap-6">
                {{-- Left Column: Profile Card --}}
                <div class="lg:col-span-1">
                    <x-mary-card title="{!! __('Contact & Administrative Details') !!}" shadow separator>
                        <p><strong>{{ __('E-mail') }}:</strong> {{ $patient->email }}</p>
                        <p><strong>{{ __('Phone') }}:</strong> {{ $patient->phone }}</p>
                        <p><strong>{{ __('Gender') }}:</strong> {{ $patient->gender ?? __('N/A') }}</p>
                        <p><strong>{{ __('Date of Birth') }}:</strong> {{ $patient->date_of_birth?->format('Y-m-d') }}
                        </p>
                        <p><strong>{{ __('Address') }}:</strong> {{ $patient->address }}, {{ $patient->city }}</p>
                        <p class="mt-4 border-t pt-2"><strong>{{ __('Registered By') }}:</strong>
                            {{ $patient->user->name }}</p>
                        <p><strong>{{ __('Registered On') }}:</strong> {{ $patient->created_at->format('Y-m-d') }}</p>
                    </x-mary-card>
                </div>

                {{-- Right Column: History/Notes --}}
                <div class="lg:col-span-2">
                    <x-mary-card title="{{ __('Medical History Summary') }}" shadow separator>
                        <p>{{ $patient->medical_history_summary ?? __('No formal summary recorded.') }}</p>
                    </x-mary-card>
                </div>
            </div>
        @endif

        {{-- 🧠 DIAGNOSIS TAB --}}
        @if ($currentTab === 'diagnosis')
            @php
                // Use computed properties
                $clinicalSummary = $this->clinicalSummary;
                $diagnosisSummary = $this->diagnosisSummary;
            @endphp

            <div class="grid lg:grid-cols-4 gap-6 mb-8">
                {{-- Session Stats --}}
                <x-mary-stat title="{{ __('Total Sessions') }}" :value="$clinicalSummary['total_sessions']" icon="o-calendar-days"
                    class="bg-base-100 shadow-lg" />
                <x-mary-stat title="{{ __('Completed Sessions') }}" :value="$clinicalSummary['completed']" icon="o-check-circle"
                    color="text-success" class="bg-base-100 shadow-lg" />
                <x-mary-stat title="{{ __('Total Diagnoses') }}" :value="$diagnosisSummary['total_diagnoses']" icon="o-clipboard-document-list"
                    class="bg-base-100 shadow-lg" />
                <x-mary-stat title="{{ __('Active Diagnoses') }}" :value="$diagnosisSummary['active_count']" icon="o-clipboard-document-check"
                    color="text-warning" class="bg-base-100 shadow-lg" />
            </div>

            {{-- ACTIVE DIAGNOSES TABLE --}}
            <x-mary-card title="{{ __('Active Diagnoses') }}" shadow separator class="mb-8">
                @if ($diagnosisSummary['active']->isNotEmpty())
                    <x-mary-table :headers="$diagnosisHeaders" :rows="$diagnosisSummary['active']" :no-pagination="true">

                        @scope('cell_icd_code_info', $diagnosis)
                            <div class="font-bold text-primary">{{ $diagnosis->icdCode->code }}</div>
                            <div class="text-sm text-gray-600">{{ $diagnosis->icdCode->description }}</div>
                        @endscope

                        @scope('cell_start_date', $diagnosis)
                            {{ $diagnosis->start_date?->format('Y-m-d') }}
                        @endscope

                        @scope('cell_condition_status', $diagnosis)
                            <x-mary-badge value="{{ __('Active') }}" class="badge-success" />
                        @endscope

                    </x-mary-table>
                @else
                    <x-mary-alert icon="o-information-circle"
                        class="alert-info">{{ __('No active diagnoses recorded.') }}
                    </x-mary-alert>
                @endif
            </x-mary-card>

            {{-- PAST DIAGNOSES TABLE --}}
            <x-mary-card title="{!! __('Past & Resolved Diagnoses') !!}" shadow separator>
                @if ($diagnosisSummary['past']->isNotEmpty())
                    <x-mary-table :headers="$diagnosisHeaders" :rows="$diagnosisSummary['past']" :no-pagination="true">

                        @scope('cell_icd_code_info', $diagnosis)
                            <div class="font-bold text-gray-500">{{ $diagnosis->icdCode->code }}</div>
                            <div class="text-sm text-gray-600">{{ $diagnosis->icdCode->description }}</div>
                        @endscope

                        @scope('cell_start_date', $diagnosis)
                            {{ $diagnosis->start_date?->format('Y-m-d') }}
                        @endscope

                        @scope('cell_condition_status', $diagnosis)
                            <x-mary-badge :value="ucfirst($diagnosis->condition_status)" class="badge-neutral" />
                        @endscope

                    </x-mary-table>
                @else
                    <x-mary-alert icon="o-information-circle"
                        class="alert-info">{{ __('No past diagnoses recorded.') }}
                    </x-mary-alert>
                @endif
            </x-mary-card>
        @endif

        {{-- 📅 SESSIONS TAB --}}
        @if ($currentTab === 'sessions')
            <x-mary-card title="{{ __('Therapy Sessions History') }}" shadow separator>
                <x-mary-table :headers="$sessionHeaders" :rows="$patient->therapySessions->sortByDesc('scheduled_at')" :no-pagination="true">

                    {{-- Format Date & Time --}}
                    @scope('cell_scheduled_at', $session)
                        <div class="font-semibold">
                            {{ $session->scheduled_at->format('Y-m-d') }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $session->scheduled_at->format('H:i') }}
                        </div>
                    @endscope

                    {{-- Format Status Badge --}}
                    @scope('cell_status', $session)
                        <x-mary-badge :value="$session->status"
                            class="@if ($session->status === 'Completed')
badge-success
@elseif ($session->status === 'Cancelled')
badge-warning
@elseif ($session->status === 'No Show')
badge-error
@else
badge-info
@endif" />
                    @endscope

                    {{-- Display Billing Status --}}
                    @scope('cell_billing_status', $session)
                        <x-mary-badge :value="$session->billing_status"
                            class="@if ($session->billing_status === 'Billed')
badge-success/50
@elseif ($session->billing_status === 'Pending')
badge-warning/50
@else
badge-gray-500
@endif" />
                    @endscope

                    {{-- Action/Notes Column --}}
                    @scope('actions', $session)
                        <x-mary-button icon="o-clipboard-document-list" class="btn-sm btn-ghost"
                            tooltip="{{ $session->cancellation_reason ?? __('View Session Notes') }}"
                            {{-- Placeholder action: link to session detail or modal --}} link="{{ route('sessions.detail', $session) }}" />
                    @endscope
                </x-mary-table>
            </x-mary-card>
        @endif

        {{-- 🧾 BILLING TAB --}}
        @if ($currentTab === 'billing')
            @php
                $summary = $this->billingSummary;
            @endphp

            <x-mary-card title="{{ __('Financial Overview') }}" shadow separator>

                {{-- Financial Stats --}}
                <div class="grid grid-cols-1 md:grid-cols-3 text-center gap-6 mb-8">
                    <x-mary-stat title="{{ __('Total Billed') }}" :value="format_currency($summary['total_billed'])" icon="o-wallet"
                        class="bg-base-100 shadow-lg" />
                    <x-mary-stat title="{{ __('Total Paid') }}" :value="format_currency($summary['total_paid'])" icon="o-check-circle"
                        color="text-success" class="bg-base-100 shadow-lg" />
                    <x-mary-stat title="{{ __('Outstanding Balance') }}" :value="format_currency($summary['total_due'])" icon="o-receipt-percent"
                        color="text-error" class="bg-base-100 shadow-lg" />
                </div>

                <h4 class="font-semibold mt-6 mb-3 border-t pt-4">{{ __('Invoices History') }}</h4>

                {{-- Invoices Table --}}
                <x-mary-table :headers="$invoiceHeaders" :rows="$summary['invoices']->sortByDesc('issued_date')" :no-pagination="true">

                    {{-- Format Amounts --}}
                    @scope('cell_total_amount', $invoice)
                        <span class="font-semibold">{{ format_currency($invoice->total_amount) }}</span>
                    @endscope
                    @scope('cell_amount_due', $invoice)
                        <span class="font-bold text-red-700">{{ format_currency($invoice->amount_due) }}</span>
                    @endscope

                    {{-- Format Status Badge --}}
                    @scope('cell_status', $invoice)
                        <x-mary-badge :value="$invoice->status"
                            class="@if ($invoice->status === 'Paid')
badge-success
@elseif ($invoice->status === 'Sent')
badge-warning
@elseif ($invoice->status === 'Draft')
badge-info
@else
badge-neutral
@endif" />
                    @endscope

                    {{-- Action Column --}}
                    @scope('actions', $invoice)
                        <x-mary-button icon="o-document-magnifying-glass" class="btn-sm btn-ghost"
                            tooltip="{{ __('View Invoice') }}" {{-- Placeholder action: link to invoice detail --}}
                            link="{{ route('invoice.show', $invoice) }}" />
                    @endscope

                </x-mary-table>

                @if ($summary['invoices']->isEmpty())
                    <x-mary-alert icon="o-information-circle"
                        class="alert-info mt-4">{{ __('No invoices recorded for this patient.') }}</x-mary-alert>
                @endif
            </x-mary-card>
        @endif

    </div>

</div>
