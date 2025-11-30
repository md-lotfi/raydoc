<div>
    <x-page-header :title="__('Session Details')" :subtitle="__('Patient: ' . $session->patient->first_name . ' ' . $session->patient->last_name)" />

    {{-- Status Alert (Success/Warning) --}}
    @if (session()->has('success'))
        <x-mary-alert icon="o-check-circle" class="alert-success mb-4">
            {{ session('success') }}
        </x-mary-alert>
    @endif
    @if (session()->has('warning'))
        <x-mary-alert icon="o-exclamation-triangle" class="alert-warning mb-4">
            {{ session('warning') }}
        </x-mary-alert>
    @endif

    {{-- Session Status Display --}}
    <div class="flex mb-6">
        <h2 class="text-xl font-semibold mb-2">{{ __('Current Status') }}</h2>
        <x-mary-badge :value="$session->status" :class="[
            'Scheduled' => 'badge-info',
            'Completed' => 'badge-success',
            'Cancelled' => 'badge-error',
            'No Show' => 'badge-warning',
        ][$session->status] ?? 'ms-4 badge-neutral'" class="text-lg font-bold ms-4 p-3" />

        @if ($session->cancellation_reason)
            <p class="text-error mt-2 italic">{{ __('Reason:') }} {{ $session->cancellation_reason }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- 📚 1. Scheduled & Metadata Card --}}
        <x-mary-card title="{{ __('Scheduling & Metadata') }}" shadow class="lg:col-span-1">

            {{-- REPLACED x-mary-list with standard HTML definition list (dl) --}}
            <dl class="divide-y divide-base-200">

                {{-- Therapist --}}
                <div class="py-3 flex items-start space-x-2">
                    <x-mary-icon name="o-user-circle" class="w-5 h-5 flex-shrink-0 text-primary" />
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Therapist') }}</dt>
                        <dd class="mt-1 text-base font-semibold">{{ $session->user->name }}</dd>
                    </div>
                </div>

                {{-- Scheduled Date --}}
                <div class="py-3 flex items-start space-x-2">
                    <x-mary-icon name="o-calendar" class="w-5 h-5 flex-shrink-0 text-primary" />
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Scheduled Date') }}</dt>
                        <dd class="mt-1 text-base font-semibold">{{ $session->scheduled_at->format('Y-m-d') }}</dd>
                    </div>
                </div>

                {{-- Scheduled Time --}}
                <div class="py-3 flex items-start space-x-2">
                    <x-mary-icon name="o-clock" class="w-5 h-5 flex-shrink-0 text-primary" />
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Scheduled Time') }}</dt>
                        <dd class="mt-1 text-base font-semibold">{{ $session->scheduled_at->format('H:i') }}</dd>
                    </div>
                </div>

                {{-- Duration --}}
                <div class="py-3 flex items-start space-x-2">
                    <x-mary-icon name="o-arrow-path-rounded-square" class="w-5 h-5 flex-shrink-0 text-primary" />
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Duration') }}</dt>
                        <dd class="mt-1 text-base font-semibold">{{ $session->duration_minutes . ' minutes' }}</dd>
                    </div>
                </div>

                {{-- Focus Area --}}
                <div class="py-3 flex items-start space-x-2">
                    <x-mary-icon name="o-tag" class="w-5 h-5 flex-shrink-0 text-primary" />
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Focus Area') }}</dt>
                        <dd class="mt-1 text-base font-semibold">{{ $session->focus_area }}</dd>
                    </div>
                </div>

                {{-- Billing Status --}}
                <div class="py-3 flex items-start space-x-2">
                    <x-mary-icon name="o-currency-dollar" class="w-5 h-5 flex-shrink-0 text-primary" />
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Billing Status') }}</dt>
                        <dd class="mt-1">
                            <x-mary-badge :value="$session->billing_status" :class="[
                                'Billed' => 'badge-primary',
                                'Pending' => 'badge-neutral',
                                'Paid' => 'badge-success',
                                'Not Applicable' => 'badge-ghost',
                            ][$session->billing_status] ?? 'badge-neutral'" class="font-semibold" />
                        </dd>
                    </div>
                </div>

            </dl>
        </x-mary-card>

        {{-- 📝 2. Clinical Notes & Time Log Card --}}
        <x-mary-card title="{{ __('Clinical Documentation') }}" shadow class="lg:col-span-2">
            <form wire:submit.prevent="updateClinicalDetails" class="space-y-6">

                <h3 class="font-semibold">{{ __('Actual Session Times') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-mary-input label="{{ __('Actual Start Time') }}" type="datetime-local"
                        wire:model="actualStartAt" :disabled="$session->status === 'Cancelled'" />
                    <x-mary-input label="{{ __('Actual End Time') }}" type="datetime-local" wire:model="actualEndAt"
                        :disabled="$session->status === 'Cancelled'" />
                </div>

                <x-mary-textarea label="{{ __('Session Notes') }}" wire:model="notes" rows="6"
                    :disabled="$session->status === 'Cancelled'" />
                <x-mary-textarea label="{{ __('Homework Assigned') }}" wire:model="homeworkAssigned" rows="3"
                    :disabled="$session->status === 'Cancelled'" />

                @if ($session->status !== 'Cancelled')
                    <div class="flex justify-end pt-4">
                        <x-mary-button type="submit" label="{{ __('Save Clinical Details') }}" icon="o-paper-clip"
                            class="btn-primary" spinner="updateClinicalDetails" />
                    </div>
                @endif
            </form>
        </x-mary-card>
    </div>

    <hr class="my-8" />

    {{-- ⚡ 3. Action Buttons --}}
    <x-mary-card title="{{ __('Session Actions') }}" shadow class="mt-6">
        <div class="flex flex-wrap gap-4">
            @if ($session->status === 'Scheduled')
                <x-mary-button label="{{ __('Mark as Completed') }}" icon="o-check-badge" wire:click="markAsCompleted"
                    class="btn-success" spinner="markAsCompleted" />

                <x-mary-button label="{{ __('Cancel Session') }}" icon="o-x-circle" wire:click="openCancelModal"
                    class="btn-error" />

                <x-mary-button label="{{ __('Mark as No Show') }}" icon="o-user-minus" class="btn-warning"
                    wire:click="$set('newStatus', 'No Show')" />
            @elseif ($session->status === 'Completed' && $session->billing_status === 'Pending')
                {{-- Action to easily go to invoicing --}}
                <x-mary-button label="{{ __('Create Invoice for this Session') }}" icon="o-receipt-percent"
                    class="btn-warning"
                    link="{{ route('invoices.create', ['patientId' => $session->patient_id, 'sessionIds' => [$session->id]]) }}" />
            @endif
        </div>
    </x-mary-card>

    {{-- 🚫 Cancellation Modal --}}
    <x-mary-modal wire:model="showCancelModal" title="{{ __('Cancel Session') }}" separator>
        <p class="text-lg mb-4">{{ __('Are you sure you want to cancel this session?') }}</p>

        <x-mary-textarea label="{{ __('Reason for Cancellation') }}" wire:model="cancellationReason"
            placeholder="{{ __('Enter a detailed reason...') }}" rows="4" required />

        <x-slot:actions>
            <x-mary-button label="{{ __('Close') }}" @click="$wire.showCancelModal = false" />
            <x-mary-button label="{{ __('Confirm Cancellation') }}" wire:click="cancelSession" class="btn-error"
                spinner="cancelSession" />
        </x-slot:actions>
    </x-mary-modal>

</div>
