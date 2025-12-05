<div class="space-y-8">

    {{-- 🟢 PAGE HEADER --}}
    <x-page-header title="{{ __('Invoice #') . $invoice->invoice_number }}"
        subtitle="{{ __('Issued to') }} {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Back') }}" icon="o-arrow-left" class="btn-ghost print:hidden"
                link="{{ route('invoice.list') }}" />
            {{-- Print Trigger --}}
            <x-mary-button label="{{ __('Print / PDF') }}" icon="o-printer" class="btn-outline print:hidden"
                onclick="window.print()" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 📄 LEFT COLUMN: INVOICE DOCUMENT (Printable Area) --}}
        <div class="lg:col-span-2 space-y-6 print:col-span-3">

            <x-mary-card
                class="bg-base-100 shadow-lg border border-base-200 p-8 rounded-none md:rounded-xl relative overflow-hidden">

                {{-- Status Watermark (for visual flair) --}}
                <div class="absolute top-4 right-4 opacity-10 rotate-12 pointer-events-none">
                    <span class="text-6xl font-black uppercase text-gray-900">{{ $invoice->status }}</span>
                </div>

                {{-- Header Section --}}
                <div class="flex justify-between items-start border-b border-gray-100 pb-8 mb-8">
                    <div>
                        {{-- Company Logo / Name --}}
                        <div class="flex items-center gap-2 mb-4 text-primary">
                            {{-- Optional: Use site_logo if available, else icon --}}
                            @if (settings()->site_logo)
                                <img src="{{ asset(settings()->site_logo) }}" class="h-10 w-auto" alt="Logo">
                            @else
                                <x-mary-icon name="o-cube-transparent" class="w-10 h-10" />
                            @endif
                            <span
                                class="font-bold text-2xl tracking-tight uppercase">{{ settings()->company_name }}</span>
                        </div>

                        {{-- ✅ DYNAMIC COMPANY SETTINGS --}}
                        <div class="text-sm text-gray-500 space-y-1">
                            {{-- We accept new lines in address field using nl2br --}}
                            <div class="whitespace-pre-line">{{ settings()->company_address }}</div>
                            <p>{{ settings()->company_phone }}</p>
                            <p>{{ settings()->company_email }}</p>
                        </div>
                    </div>

                    <div class="text-right">
                        <h2 class="text-3xl font-bold text-gray-800">{{ __('INVOICE') }}</h2>
                        <p class="text-gray-500 mt-1">#{{ $invoice->invoice_number }}</p>

                        <div class="mt-4 space-y-1 text-sm">
                            <div class="flex justify-end gap-4">
                                <span class="text-gray-400">{{ __('Issued:') }}</span>
                                <span
                                    class="font-medium">{{ $invoice->issued_date->translatedFormat('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-end gap-4">
                                <span class="text-gray-400">{{ __('Due:') }}</span>
                                <span class="font-medium">{{ $invoice->due_date->translatedFormat('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Addresses --}}
                <div class="grid grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('Bill To') }}
                        </h3>
                        <div class="font-bold text-lg">{{ $invoice->patient->first_name }}
                            {{ $invoice->patient->last_name }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            <p>{{ $invoice->patient->address }}</p>
                            <p>{{ $invoice->patient->city }}</p>
                            <p>{{ $invoice->patient->email }}</p>
                        </div>
                    </div>
                    <div>
                        {{-- Optional "Ship To" or extra details could go here --}}
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="overflow-x-auto mb-8">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b-2 border-gray-100 text-left text-gray-400 uppercase text-xs tracking-wider">
                                <th class="py-3 pl-2">{{ __('Description') }}</th>
                                <th class="py-3 text-center">{{ __('Qty') }}</th>
                                <th class="py-3 text-right">{{ __('Price') }}</th>
                                <th class="py-3 text-right pr-2">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($invoice->lineItems as $item)
                                <tr>
                                    <td class="py-4 pl-2">
                                        <div class="font-bold text-gray-700">{{ $item->service_description }}</div>
                                        <div class="text-xs text-gray-400 font-mono mt-0.5">
                                            {{ $item->billingCode->code ?? 'CUSTOM' }}</div>
                                    </td>
                                    <td class="py-4 text-center">{{ $item->units }}</td>
                                    <td class="py-4 text-right">{{ format_currency($item->unit_price) }}</td>
                                    <td class="py-4 text-right font-bold pr-2">{{ format_currency($item->subtotal) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="flex justify-end">
                    <div class="w-full md:w-1/2 lg:w-1/3 space-y-3">
                        <div class="flex justify-between text-gray-500">
                            <span>{{ __('Subtotal') }}</span>
                            <span>{{ format_currency($invoice->total_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-success">
                            <span>{{ __('Amount Paid') }}</span>
                            <span>{{ format_currency($invoice->total_amount - $invoice->amount_due) }}</span>
                        </div>
                        <div
                            class="flex justify-between border-t border-gray-200 pt-3 text-xl font-black text-gray-800">
                            <span>{{ __('Total Due') }}</span>
                            <span>{{ format_currency($invoice->amount_due) }}</span>
                        </div>
                    </div>
                </div>

            </x-mary-card>
        </div>

        {{-- 🎛️ RIGHT COLUMN: ACTIONS & HISTORY (Screen Only) --}}
        <div class="lg:col-span-1 space-y-6 print:hidden">

            {{-- Status Card --}}
            <x-mary-card
                class="border-t-4 {{ $invoice->status === 'Paid' ? 'border-success' : ($invoice->status === 'Sent' ? 'border-info' : 'border-base-300') }}">
                <div class="text-center mb-4">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Status') }}</div>
                    <div class="text-2xl font-black mt-1">{{ __($invoice->status) }}</div>
                </div>

                {{-- Action Buttons Stack --}}
                <div class="flex flex-col gap-2">

                    {{-- DRAFT ACTIONS --}}
                    @if ($invoice->status === 'Draft')
                        <x-mary-button label="{{ __('Send Invoice') }}" icon="o-paper-airplane"
                            class="btn-primary w-full" wire:click="markAsSent" />
                        <x-mary-button label="{{ __('Edit Details') }}" icon="o-pencil" class="btn-ghost w-full"
                            wire:click="redirectToEdit" />
                        <x-mary-button label="{{ __('Delete') }}" icon="o-trash" class="text-error w-full"
                            wire:click="cancelInvoice" />

                        {{-- ACTIVE ACTIONS --}}
                    @elseif (in_array($invoice->status, ['Sent', 'Partially Paid']))
                        <x-mary-button label="{{ __('Record Payment') }}" icon="o-credit-card"
                            class="btn-success w-full text-white shadow-md"
                            wire:click="$set('showPaymentModal', true)" />
                        <x-mary-button label="{!! __('Resend Email') !!}" icon="o-envelope" class="btn-outline w-full" />
                        <x-mary-button label="{{ __('Cancel Invoice') }}" icon="o-x-circle"
                            class="btn-ghost text-error w-full" wire:click="cancelInvoice"
                            confirm="{{ __('Are you sure? This will reset all items.') }}" />

                        {{-- PAID ACTIONS --}}
                    @elseif ($invoice->status === 'Paid')
                        <div class="alert alert-success text-sm py-2">
                            <x-mary-icon name="o-check-circle" /> {{ __('Fully Paid') }}
                        </div>
                    @endif

                </div>
            </x-mary-card>

            {{-- Payment History --}}
            @if ($invoice->payments->isNotEmpty())
                <x-mary-card title="{{ __('Payment History') }}" separator>
                    <div class="space-y-4">
                        @foreach ($invoice->payments as $payment)
                            <div class="flex items-start gap-3 text-sm">
                                <div class="bg-success/10 p-2 rounded-full text-success">
                                    <x-mary-icon name="o-banknotes" class="w-4 h-4" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between font-bold">
                                        <span>{{ format_currency($payment->amount) }}</span>
                                        <span
                                            class="text-xs text-gray-400">{{ $payment->created_at->translatedFormat('M d') }}</span>
                                    </div>
                                    <div class="text-gray-500 text-xs mt-0.5">
                                        {{ $payment->payment_method ?? 'Manual' }}
                                        @if ($payment->notes)
                                            <span class="italic text-gray-400">-
                                                {{ Str::limit($payment->notes, 20) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>
            @endif

        </div>
    </div>

    {{-- 💰 PAYMENT MODAL --}}
    <x-mary-modal wire:model="showPaymentModal" title="{{ __('Receive Payment') }}" class="backdrop-blur">

        <div class="mb-6 bg-base-200 p-4 rounded-lg flex justify-between items-center">
            <span class="text-gray-500">{{ __('Balance Due') }}</span>
            <span class="text-xl font-bold text-error">{{ format_currency($invoice->amount_due) }}</span>
        </div>

        {{-- ❌ REMOVED <form> wrapper to avoid scope issues with slot actions --}}

        <div class="space-y-4" onkeydown="return event.key != 'Enter';"> {{-- Prevent accidental submit on Enter if needed --}}

            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="{!! __('Amount') !!}" prefix="$" wire:model="paymentAmount"
                    type="number" step="0.01" class="font-bold text-lg" />
                <x-mary-input label="{{ __('Date') }}" wire:model="paymentDate" type="date" />
            </div>

            <x-mary-select label="{{ __('Payment Method') }}" :options="$paymentMethods" wire:model="paymentMethod"
                icon="o-credit-card" placeholder="{{ __('Select...') }}" />

            <x-mary-textarea label="{{ __('Notes') }}" wire:model="notes" placeholder="{!! __('Transaction ID, Check Number, etc.') !!}"
                rows="2" />

        </div>

        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showPaymentModal = false" />

            {{-- ✅ DIRECT WIRE:CLICK --}}
            <x-mary-button label="{{ __('Confirm Payment') }}" class="btn-primary" wire:click="recordPayment"
                spinner="recordPayment" />
        </x-slot:actions>
    </x-mary-modal>

</div>
