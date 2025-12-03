@php
    $headers = [
        ['key' => 'invoice_number', 'label' => __('Invoice #'), 'class' => 'font-mono'],
        ['key' => 'patient.last_name', 'label' => __('Patient')],
        ['key' => 'issued_date', 'label' => __('Date'), 'class' => 'hidden md:table-cell'],
        ['key' => 'status', 'label' => __('Status'), 'class' => 'text-center'],
        ['key' => 'amount_due', 'label' => __('Balance'), 'class' => 'text-end'],
        ['key' => 'total_amount', 'label' => __('Total'), 'class' => 'text-end hidden lg:table-cell'],
    ];
@endphp

<div class="space-y-6">

    {{-- 🟢 PAGE HEADER --}}
    <x-page-header :title="$patient
        ? __('Billing: :name', ['name' => $patient->first_name . ' ' . $patient->last_name])
        : __('Financial Records')" :subtitle="$patient
        ? __('History of invoices and payments for this patient.')
        : __('Overview of all practice invoices.')" separator>
        <x-slot:actions>
            @if ($patientId)
                <x-mary-button label="{{ __('New Invoice') }}" icon="o-plus" class="btn-primary"
                    link="{{ route('invoice.generate', ['patient' => $patientId]) }}" />
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- 📊 STATS OVERVIEW --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-mary-stat title="{{ __('Total Billed') }}" value="{{ format_currency($stats['total_volume']) }}"
            icon="o-banknotes" class="bg-base-100 shadow-sm border border-base-200" />
        <x-mary-stat title="{{ __('Outstanding') }}" value="{{ format_currency($stats['outstanding']) }}"
            icon="o-exclamation-circle" class="bg-base-100 shadow-sm border border-base-200"
            color="{{ $stats['outstanding'] > 0 ? 'text-error' : 'text-gray-400' }}" />
        <x-mary-stat title="{{ __('Paid Invoices') }}" value="{{ $stats['paid_count'] }}" icon="o-check-badge"
            class="bg-base-100 shadow-sm border border-base-200" color="text-success" />
        <x-mary-stat title="{{ __('Overdue') }}" value="{{ $stats['overdue_count'] }}" icon="o-clock"
            class="bg-base-100 shadow-sm border border-base-200"
            color="{{ $stats['overdue_count'] > 0 ? 'text-warning' : 'text-gray-400' }}" />
    </div>

    {{-- 🎛️ CONTROLS BAR --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">

        {{-- Search --}}
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search invoice # or patient...') }}"
                wire:model.live.debounce.300ms="search" class="w-full" />
        </div>

        {{-- Filters --}}
        <div class="flex gap-2 w-full md:w-auto justify-end overflow-x-auto">
            <div class="join">
                <button class="join-item btn btn-sm {{ $statusFilter === '' ? 'btn-neutral' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', '')">
                    {{ __('All') }}
                </button>
                @foreach ($statuses as $status)
                    <button
                        class="join-item btn btn-sm {{ $statusFilter === $status ? 'btn-active btn-primary' : 'btn-ghost' }}"
                        wire:click="$set('statusFilter', '{{ $status }}')">
                        {{ __($status) }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 📋 INVOICE TABLE --}}
    <x-mary-card shadow class="bg-base-100">
        <x-mary-table :headers="$headers" :rows="$invoices" :sort-by="$sortBy" :link="route('invoice.show', ['invoice' => '[id]'])"
            class="cursor-pointer hover:bg-base-50" with-pagination>

            {{-- 🧾 Invoice Number --}}
            @scope('cell_invoice_number', $invoice)
                <div class="font-mono font-bold text-primary">
                    #{{ $invoice->invoice_number }}
                </div>
            @endscope

            {{-- 👤 Patient Info --}}
            @scope('cell_patient.last_name', $invoice, $patientId)
                @if ($patientId)
                    <div class="font-semibold">{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <x-mary-avatar :image="$invoice->patient->avatar" :title="$invoice->patient->first_name" class="!w-8 !h-8" />
                        <a href="{{ route('patient.health.folder', $invoice->patient_id) }}"
                            class="font-bold hover:underline hover:text-primary">
                            {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                        </a>
                    </div>
                @endif
            @endscope

            {{-- 🗓️ Date --}}
            @scope('cell_issued_date', $invoice)
                <div class="text-gray-600">
                    {{ $invoice->issued_date->translatedFormat('M d, Y') }}
                </div>
            @endscope

            {{-- 🏷️ Status Badge --}}
            @scope('cell_status', $invoice)
                <x-mary-badge :value="__($invoice->status)"
                    class="font-bold text-xs
                    @if ($invoice->status === 'Paid')
badge-success/10 text-success
@elseif($invoice->status === 'Sent')
badge-info/10 text-info
@elseif($invoice->status === 'Partially Paid')
badge-warning/10 text-warning
@elseif($invoice->status === 'Canceled')
badge-error/10 text-error
@else
badge-ghost
@endif" />
            @endscope

            {{-- 💰 Amounts --}}
            @scope('cell_amount_due', $invoice)
                <div class="{{ $invoice->amount_due > 0 ? 'text-error font-bold' : 'text-gray-400' }}">
                    {{ format_currency($invoice->amount_due) }}
                </div>
            @endscope

            @scope('cell_total_amount', $invoice)
                <div class="text-gray-600 font-medium">
                    {{ format_currency($invoice->total_amount) }}
                </div>
            @endscope

            {{-- ⚙️ ACTIONS DROPDOWN --}}
            @scope('actions', $invoice)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-sm btn-ghost btn-circle" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('View Invoice') }}" icon="o-eye"
                            link="{{ route('invoice.show', $invoice->id) }}" />

                        @if ($invoice->status !== 'Paid')
                            <x-mary-menu-item title="{{ __('Edit') }}" icon="o-pencil"
                                link="{{ route('invoice.edit', $invoice->id) }}" />
                        @endif

                        @if ($invoice->status === 'Draft')
                            <x-mary-menu-item title="{{ __('Delete') }}" icon="o-trash" class="text-error"
                                wire:click="confirmDelete({{ $invoice->id }})" />
                        @endif
                    </x-mary-dropdown>
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- 🗑️ Delete Confirmation Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="{{ __('Delete Invoice') }}" class="backdrop-blur">
        <div class="mb-5 text-gray-500">
            {{ __('Are you sure you want to delete this invoice? This action cannot be undone.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Delete') }}" wire:click="delete" class="btn-error" />
        </x-slot:actions>
    </x-mary-modal>

</div>
