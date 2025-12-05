<div>
    <x-page-header :title="__('Edit Invoice :number', ['number' => $invoice->invoice_number])" :subtitle="__(
        'Patient: ' .
            $patient->first_name .
            ' ' .
            $patient->last_name .
            ' (' .
            Str::padLeft($patient->id, 4, '0') .
            ')',
    )" />

    @if (session()->has('error'))
        <x-alert color="error" title="Error" class="mb-4">
            {{ session('error') }}
        </x-alert>
    @endif
    @if (session()->has('success'))
        <x-alert color="success" title="Success" class="mb-4">
            {{ session('success') }}
        </x-alert>
    @endif

    {{-- Billing errors are not expected on edit, but kept for consistency --}}
    @if (!empty($billingErrors))
        <x-alert color="warning" title="{{ __('Automatic Billing Code Matching Failed') }}" class="mb-4">
            <p>{{ __('The system could not automatically determine the correct billing code for the following sessions:') }}
            </p>
            <ul class="list-disc list-inside mt-2">
                @foreach ($billingErrors as $error)
                    <li>
                        Session ID **{{ $error['sessionId'] }}**: {{ $error['message'] }}
                    </li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form wire:submit.prevent="updateInvoice" class="space-y-8">

        {{-- INVOICE DETAILS --}}
        <x-mary-card title="{!! __('Invoice Header Details') !!}" shadow separator>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                {{-- Invoice Number (Read-Only) --}}
                <x-mary-input label="{{ __('Invoice Number') }}" :value="$invoice->invoice_number" readonly icon="o-receipt-percent" />

                {{-- Issued Date --}}
                <x-mary-input label="{!! __('Issued Date') !!}" type="date" wire:model.live="issued_date" required />

                {{-- Due Date --}}
                <x-mary-input label="{!! __('Due Date') !!}" type="date" wire:model.live="due_date" required />

                {{-- Status --}}
                <div>
                    {{-- Note: Status options are restricted in the component's rules() based on payments --}}
                    <x-mary-select label="{{ __('Status') }}" :options="[['name' => 'Draft'], ['name' => 'Sent'], ['name' => 'Canceled']]" option-value="name"
                        wire:model.live="status" required>
                    </x-mary-select>
                </div>
            </div>
        </x-mary-card>

        {{-- LINE ITEMS TABLE --}}
        <x-mary-card title="{{ __('Services Billed (Line Items)') }}" shadow separator>
            <x-slot:actions separator>
                <div class="flex justify-end w-full">
                    <x-mary-button label="{{ __('Add Manual Line Item') }}" icon="o-plus-circle"
                        wire:click="addLineItem" class="btn-sm btn-outline" />
                </div>
            </x-slot:actions>
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="p-2 text-left w-1/9">{{ __('Code') }}</th>
                        <th class="p-2 text-left w-2/7">{{ __('Description') }}</th>
                        <th class="p-2 text-right w-1/12">{{ __('Units') }}</th>
                        <th class="p-2 text-right w-1/12">
                            @if (settings()->default_currency_position === 'prefix')
                                {{ __(':symb Rate', ['symb' => settings()->currency->symbol ?? '$']) }}
                            @else
                                {{ __('Rate :symb', ['symb' => settings()->currency->symbol ?? '$']) }}
                            @endif
                        </th>
                        <th class="p-2 text-right w-1/12">{{ __('Subtotal') }}</th>
                        <th class="p-2 w-1/12"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lineItems as $index => $item)
                        <tr class="border-b hover:bg-neutral">
                            {{-- Billing Code --}}
                            <td class="p-2">
                                <x-mary-select wire:model.live="lineItems.{{ $index }}.billing_code_id"
                                    :options="$availableBillingCodes" option-label="code" option-value="id" placeholder="Select Code" />
                                {{-- Hidden input to track item ID for update/delete --}}
                                <input type="hidden" wire:model="lineItems.{{ $index }}.id">
                            </td>

                            {{-- Description (includes session ID if relevant) --}}
                            <td class="p-2">
                                <x-mary-input wire:model.live="lineItems.{{ $index }}.service_description" />
                                @if (isset($item['therapy_session_id']))
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ __('Linked Session ID: :id', ['id' => $item['therapy_session_id']]) }}
                                    </p>
                                @endif
                            </td>

                            {{-- Units --}}
                            <td class="p-2 text-right">
                                <x-mary-input type="number" wire:model.live="lineItems.{{ $index }}.units"
                                    min="1" class="text-right" />
                            </td>

                            {{-- Unit Price --}}
                            <td class="p-2 text-right">
                                <x-mary-input type="number" wire:model.live="lineItems.{{ $index }}.unit_price"
                                    step="0.01" class="text-right" />
                            </td>

                            {{-- Subtotal (Readonly) --}}
                            <td class="p-2 text-right">
                                <x-mary-input :value="format_currency($item['subtotal'], 2)" readonly class="text-right font-semibold" />
                            </td>

                            {{-- Actions --}}
                            <td class="p-2 text-center">
                                <x-mary-button icon="o-x-mark" wire:click="removeLineItem({{ $index }})"
                                    class="btn-sm btn-error" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                {{ __('No line items on this invoice.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-mary-card>

        {{-- 💳 TOTALS SUMMARY CARD --}}
        <div class="grid justify-end">
            <x-mary-card title="{{ __('Invoice Totals') }}" shadow class="w-full md:w-96">
                <div class="space-y-3">
                    <div class="flex justify-between font-medium text-lg">
                        <span>{{ __('Subtotal:') }}</span>
                        <span>{{ format_currency($total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-medium text-lg border-t pt-3">
                        <span>{{ __('Total Paid:') }}</span>
                        <span
                            class="text-success">{{ format_currency($invoice->total_amount - $invoice->amount_due, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-xl border-t pt-3">
                        <span class="text-red-600">{{ __('New Amount Due:') }}</span>
                        <span class="text-red-600">{{ format_currency($amount_due) }}</span>
                    </div>
                </div>
            </x-mary-card>
        </div>


        {{-- SAVE BUTTON --}}
        <div class="flex justify-end pt-4">
            <x-mary-button type="submit" label="{{ __('Update Invoice') }}" icon="o-check-circle"
                class="btn-primary w-full md:w-auto text-lg" spinner="updateInvoice" />
        </div>
    </form>
</div>
