@php
    $headers = [
        ['key' => 'invoice_number', 'label' => __('Invoice #')],
        ['key' => 'patient.full_name', 'label' => __('Patient')],
        ['key' => 'issued_date', 'label' => __('Issued Date')],
        ['key' => 'due_date', 'label' => __('Due Date')],
        ['key' => 'total_amount', 'label' => __('Total')],
        ['key' => 'amount_due', 'label' => __('Due')],
        ['key' => 'status', 'label' => __('Status')],
    ];
@endphp

<div>
    {{-- Dynamic Header Title --}}
    <x-page-header :title="$patient
        ? __('Invoices for :name', ['name' => $patient->first_name . ' ' . $patient->last_name])
        : __('All Invoices')" :subtitle="$patient ? __('Review patient billing history.') : __('Manage all financial records.')" />

    {{-- Filters and Search --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">

        {{-- Search Input --}}
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search by invoice # or patient name...') }}"
                wire:model.live.debounce.300ms="search" />
        </div>

        {{-- Status Filter --}}
        <div class="w-full md:w-1/4">
            <x-mary-select label="{{ __('Filter by Status') }}" :options="$statuses" wire:model.live="statusFilter"
                placeholder="{{ __('Select Status') }}" />
        </div>

        {{-- Create Button (Optional - adjust route as needed) --}}
        @if ($patientId)
            <a href="{{ route('invoice.generate', ['patient' => $patientId]) }}">
                <x-mary-button label="{{ __('Create New Invoice') }}" icon="o-plus" class="btn-primary" />
            </a>
        @endif
    </div>

    {{-- Invoice Table --}}
    <x-mary-table :headers="$headers" :rows="$invoices" with-pagination>

        {{-- Custom scope for Patient name (only show link if viewing 'All Invoices') --}}
        @scope('cell_patient.full_name', $invoice, $patientId)
            @if ($patientId)
                <span>{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</span>
            @else
                {{-- Link to patient's profile/invoices --}}
                <a href="{{ route('patient.health.folder', ['patient' => $invoice->patient->id]) }}"
                    class="link link-primary">
                    {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                </a>
            @endif
        @endscope

        {{-- Custom scope for Amounts --}}
        @scope('cell_total_amount', $invoice)
            <span class="font-medium">{{ format_currency($invoice->total_amount) }}</span>
        @endscope
        @scope('cell_amount_due', $invoice)
            <span class="font-bold text-red-600">{{ format_currency($invoice->amount_due) }}</span>
        @endscope

        {{-- Custom scope for Status --}}
        @scope('cell_status', $invoice)
            <x-mary-badge :value="$invoice->status" :class="[
                'Draft' => 'badge-neutral',
                'Sent' => 'badge-info',
                'Partially Paid' => 'badge-warning',
                'Paid' => 'badge-success',
                'Canceled' => 'badge-error',
            ][$invoice->status] ?? 'badge-neutral'" class="font-semibold" />
        @endscope

        {{-- Actions column (Invoice Number) --}}
        @scope('cell_invoice_number', $invoice)
            <a href="{{ route('invoice.show', $invoice->id) }}" class="link link-secondary font-bold">
                {{ $invoice->invoice_number }}
            </a>
        @endscope

    </x-mary-table>

</div>
