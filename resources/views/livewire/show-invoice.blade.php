<div>

    <x-page-header :title="__('Invoice :number', ['number' => $invoice->invoice_number])" :subtitle="__('Issued to :patient', [
        'patient' => $invoice->patient->first_name . ' ' . $invoice->patient->last_name,
    ])" />

    @if (session()->has('success') || session()->has('warning') || session()->has('error') || session()->has('info'))
        <x-alert :color="session('success')
            ? 'success'
            : (session('warning')
                ? 'warning'
                : (session('error')
                    ? 'error'
                    : 'info'))" title="{{ session('success') ? 'Success' : 'Attention' }}" class="mb-4">
            {{ session('success') ?? (session('warning') ?? (session('error') ?? session('info'))) }}
        </x-alert>
    @endif

    {{-- ACTION BUTTONS --}}
    <x-mary-card shadow separator class="mb-8">
        <div class="flex justify-between items-center">

            <div class="flex space-x-3">
                @if ($invoice->status === 'Draft')
                    <x-mary-button label="{{ __('Mark as Sent') }}" icon="o-paper-airplane" wire:click="markAsSent"
                        class="btn-primary" spinner />
                    <x-mary-button label="{{ __('Edit Invoice') }}" icon="o-pencil-square" wire:click="redirectToEdit"
                        class="btn-outline" />
                @elseif ($invoice->status === 'Sent' || $invoice->status === 'Partially Paid')
                    <x-mary-button label="{{ __('Record Payment') }}" icon="o-currency-dollar"
                        wire:click="$toggle('showPaymentModal')" class="btn-success" />
                    <x-mary-button label="{{ __('Resend') }}" icon="o-paper-airplane" class="btn-outline" />
                @endif

                @if ($invoice->status !== 'Canceled')
                    <x-mary-button label="{{ __('Cancel') }}" icon="o-trash" wire:click="cancelInvoice"
                        class="btn-warning" spinner />
                @endif
            </div>

            {{-- Status Display --}}
            <div>
                <x-mary-badge :value="$invoice->status" :class="[
                    'Draft' => 'badge-neutral',
                    'Sent' => 'badge-info',
                    'Partially Paid' => 'badge-warning',
                    'Paid' => 'badge-success',
                    'Canceled' => 'badge-error',
                ][$invoice->status] ?? 'badge-neutral'" class="text-xl font-bold p-3" />
            </div>
        </div>
    </x-mary-card>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- INVOICE DETAILS (Left Column) --}}
        <div class="lg:col-span-2 space-y-8">
            <x-mary-card title="{{ __('Invoice Summary') }}" shadow separator>
                <div class="grid grid-cols-2 gap-4">
                    <x-mary-stat title="{{ __('Patient') }}" :value="$invoice->patient->first_name . ' ' . $invoice->patient->last_name" icon="o-user" />
                    <x-mary-stat title="{{ __('Therapist/Creator') }}" :value="$invoice->user->name" icon="o-user-circle" />
                    <x-mary-stat title="{{ __('Issued Date') }}" :value="$invoice->issued_date->format('Y-m-d')" icon="o-calendar" />
                    <x-mary-stat title="{{ __('Due Date') }}" :value="$invoice->due_date->format('Y-m-d')" icon="o-calendar-days" />
                </div>
            </x-mary-card>

            {{-- LINE ITEMS TABLE --}}
            <x-mary-card title="{{ __('Billed Services') }}" shadow separator>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 font-semibold text-gray-600">
                            <th class="p-2 text-left w-1/6">{{ __('Code') }}</th>
                            <th class="p-2 text-left w-1/2">{{ __('Description') }}</th>
                            <th class="p-2 text-center w-1/12">{{ __('Units') }}</th>
                            <th class="p-2 text-right w-1/12">{{ __('Rate') }}</th>
                            <th class="p-2 text-right w-1/12">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->lineItems as $item)
                            <tr class="border-b hover:bg-neutral/50">
                                <td class="p-2 font-mono text-sm">{{ $item->billingCode->code ?? 'N/A' }}</td>
                                <td class="p-2">{{ $item->service_description }}</td>
                                <td class="p-2 text-center">{{ $item->units }}</td>
                                <td class="p-2 text-right">{{ format_currency($item->unit_price, 2) }}</td>
                                <td class="p-2 text-right font-medium">{{ format_currency($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-mary-card>
        </div>

        {{-- TOTALS (Right Column) --}}
        <div class="lg:col-span-1">
            <x-mary-card title="{{ __('Financial Summary') }}" shadow separator>
                <div class="space-y-4 text-gray-700">
                    <div class="flex justify-between border-b pb-2">
                        <span>{{ __('Subtotal:') }}</span>
                        <span class="font-medium">{{ format_currency($invoice->total_amount) }}</span>
                    </div>

                    {{-- Placeholder for total paid (assuming future payment relation) --}}
                    <div class="flex justify-between border-b pb-2">
                        <span>{{ __('Paid:') }}</span>
                        <span class="font-medium text-success">
                            {{ format_currency($invoice->total_amount - $invoice->amount_due) }}
                        </span>
                    </div>

                    <div class="flex justify-between font-bold text-xl pt-2">
                        <span class="text-red-600">{{ __('Amount Due:') }}</span>
                        <span class="text-red-600">{{ format_currency($invoice->amount_due) }}</span>
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>

    {{-- MODAL FOR RECORDING PAYMENT (Future feature) --}}
    <x-mary-modal wire:model="showPaymentModal" title="{{ __('Record New Payment') }}" separator>
        <h3 class="text-2xl font-bold mb-4">
            {{ __('Record Payment for Invoice :number', ['number' => $invoice->invoice_number]) }}</h3>

        <div class="space-y-4">

            {{-- Display Current Totals --}}
            <div class="grid grid-cols-2 gap-4 bg-base-200 p-4 rounded-lg">
                <div>
                    <span class="text-sm font-semibold text-gray-500">{{ __('Invoice Total:') }}</span>
                    <p class="text-lg font-bold">{{ format_currency($invoice->total_amount) }}</p>
                </div>
                <div>
                    <span class="text-sm font-semibold text-gray-500">{{ __('Remaining Due:') }}</span>
                    <p class="text-xl font-extrabold text-red-600">{{ format_currency($invoice->amount_due) }}</p>
                </div>
            </div>

            <form wire:submit.prevent="recordPayment" class="space-y-4">
                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session()->get('error') }}
                    </div>
                @endif
                {{-- Payment Amount --}}
                <x-mary-input label="{{ __('Amount Paid') }}" type="number" wire:model="paymentAmount" step="0.01"
                    :min="0.01" :max="$invoice->amount_due" required />
                @error('paymentAmount')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror

                {{-- Payment Date --}}
                <x-mary-input label="{{ __('Date of Payment') }}" type="date" wire:model="paymentDate" required />
                @error('paymentDate')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror

                {{-- Payment Method --}}
                <flux:select wire:model="paymentMethod" placeholder="{{ __('Payment Method') }}" required>
                    @foreach ($paymentMethods as $g)
                        <flux:select.option value="{{ $g }}">{{ $g }}</flux:select.option>
                    @endforeach

                </flux:select>
                @error('paymentMethod')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror

                {{-- Notes --}}
                <flux:textarea label="{{ __('Notes (e.g., Check #123)') }}" wire:model="notes" rows="3" />
                @error('notes')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </form>
        </div>

        {{-- Actions Slot for the Modal (Requires parent modal wrapper) --}}
        <x-slot:actions>
            {{-- This button assumes the parent component (ShowInvoice) provides a close action --}}
            <x-mary-button label="{{ __('Cancel') }}" onclick="closePaymentModal()" />

            <x-mary-button label="{{ __('Confirm Payment') }}" wire:click="recordPayment" class="btn-success"
                spinner="recordPayment" />
        </x-slot:actions>
    </x-mary-modal>

</div>
