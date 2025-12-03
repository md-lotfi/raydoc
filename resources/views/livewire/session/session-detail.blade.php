<div class="space-y-6">

    {{-- 🟢 HEADER: Navigation & Context --}}
    <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center">

        {{-- Left: Breadcrumbs & Navigation --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                @if ($previousSession)
                    <x-mary-button icon="o-chevron-left" class="btn-xs btn-circle btn-ghost"
                        tooltip="{{ __('Previous Session') }}"
                        link="{{ route('sessions.detail', $previousSession->id) }}" />
                @endif

                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Session Record') }}</span>

                @if ($nextSession)
                    <x-mary-button icon="o-chevron-right" class="btn-xs btn-circle btn-ghost"
                        tooltip="{{ __('Next Session') }}" link="{{ route('sessions.detail', $nextSession->id) }}" />
                @endif
            </div>
            <h1 class="text-2xl font-bold flex items-center gap-3">
                {{ $session->patient->first_name }} {{ $session->patient->last_name }}
                <span class="text-gray-400 font-light">|</span>
                <span
                    class="text-lg font-medium text-gray-600">{{ $session->scheduled_at->translatedFormat('M d, Y') }}</span>
            </h1>
        </div>

        {{-- Right: Actions --}}
        <div class="flex gap-2">
            <x-mary-button label="{{ __('Print') }}" icon="o-printer" class="btn-ghost" onclick="window.print()" />
            <x-mary-button label="{{ __('Back to List') }}" icon="o-arrow-left" class="btn-outline"
                link="{{ route('sessions.list') }}" />
        </div>
    </div>

    {{-- 🔴 CANCELLATION BANNER --}}
    @if ($session->status === 'Cancelled')
        <div class="alert alert-error shadow-sm">
            <x-mary-icon name="o-exclamation-triangle" />
            <div>
                <h3 class="font-bold">{{ __('Session Cancelled') }}</h3>
                <div class="text-sm">{{ $session->cancellation_reason }}</div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 📝 LEFT COLUMN: Clinical Workspace (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Documentation Card --}}
            <x-mary-card title="{{ __('Clinical Documentation') }}" separator shadow>
                <x-slot:menu>
                    <div class="badge badge-neutral">{{ $session->focus_area }}</div>
                </x-slot:menu>

                <form wire:submit.prevent="updateClinicalDetails" class="space-y-6">

                    {{-- Time Logging --}}
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-base-200/50 p-4 rounded-xl border border-base-200">
                        <x-mary-input label="{{ __('Actual Start') }}" type="datetime-local" wire:model="actualStartAt"
                            icon="o-play" :disabled="$session->status === 'Cancelled'" />
                        <x-mary-input label="{{ __('Actual End') }}" type="datetime-local" wire:model="actualEndAt"
                            icon="o-stop" :disabled="$session->status === 'Cancelled'" />
                    </div>

                    {{-- Notes --}}
                    <x-mary-textarea label="{{ __('Session Notes') }}" wire:model="notes"
                        placeholder="{{ __('Record clinical observations, patient progress, and interventions...') }}"
                        rows="8" class="font-mono text-sm leading-relaxed" :disabled="$session->status === 'Cancelled'" />

                    {{-- Homework --}}
                    <x-mary-textarea label="{{ __('Homework / Next Steps') }}" wire:model="homeworkAssigned"
                        icon="o-clipboard-document-check" rows="3" :disabled="$session->status === 'Cancelled'" />

                    @if ($session->status !== 'Cancelled')
                        <div class="flex justify-end border-t pt-4">
                            <x-mary-button type="submit" label="{{ __('Save Changes') }}" icon="o-check"
                                class="btn-primary" spinner="updateClinicalDetails" />
                        </div>
                    @endif
                </form>
            </x-mary-card>
        </div>

        {{-- 📊 RIGHT COLUMN: Metadata & Actions (1/3) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Status Card --}}
            <x-mary-card
                class="border-t-4 {{ $session->status === 'Completed' ? 'border-success' : ($session->status === 'Scheduled' ? 'border-info' : 'border-base-300') }} shadow-md">
                <div class="text-center">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                        {{ __('Current Status') }}</div>
                    <div
                        class="text-2xl font-black {{ $session->status === 'Completed' ? 'text-success' : ($session->status === 'Scheduled' ? 'text-info' : 'text-gray-500') }}">
                        {{ __($session->status) }}
                    </div>

                    {{-- Status Actions --}}
                    @if (in_array($session->status, ['Scheduled', 'Checked In', 'In Session']))
                        <div class="grid grid-cols-2 gap-2 mt-6">
                            <x-mary-button label="{{ __('Complete') }}" class="btn-success btn-sm text-white"
                                wire:click="markAsCompleted" />

                            {{-- Only show "No Show" if they haven't started yet --}}
                            @if ($session->status !== 'In Session')
                                <x-mary-button label="{{ __('No Show') }}" class="btn-warning btn-sm"
                                    wire:click="$set('newStatus', 'No Show')" />
                            @endif

                            <x-mary-button label="{{ __('Cancel') }}" class="btn-error btn-outline btn-sm col-span-2"
                                wire:click="$set('showCancelModal', true)" />
                        </div>
                    @endif
                </div>
            </x-mary-card>

            {{-- Metadata List --}}
            <x-mary-card title="{{ __('Session Details') }}" separator shadow>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">{{ __('Type') }}</span>
                        <span class="font-medium flex items-center gap-1">
                            <x-mary-icon
                                name="{{ $session->session_type === 'appointment' ? 'o-calendar' : 'o-user' }}"
                                class="w-4 h-4 text-gray-400" />
                            {{ $session->session_type === 'appointment' ? __('Appointment') : __('Walk-in') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Therapist') }}</span>
                        <span class="font-medium">{{ $session->user->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Scheduled Duration') }}</span>
                        <span class="font-medium">{{ $session->duration_minutes }} {{ __('min') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">{{ __('Billing') }}</span>
                        <x-mary-badge :value="__($session->billing_status)" class="badge-ghost badge-sm" />
                    </div>
                </div>

                {{-- Invoice Action --}}
                @if ($session->status === 'Completed' && $session->billing_status === 'Pending')
                    <div class="mt-6 pt-4 border-t">
                        <x-mary-button label="{{ __('Generate Invoice') }}" icon="o-banknotes"
                            class="btn-warning w-full"
                            link="{{ route('invoice.generate', ['patient' => $session->patient_id, 'sessionIds' => [$session->id]]) }}" />
                    </div>
                @endif
            </x-mary-card>

            {{-- Patient Quick Link --}}
            <a href="{{ route('patient.health.folder', $session->patient_id) }}" class="block group">
                <div
                    class="flex items-center gap-4 p-4 bg-base-100 rounded-xl border border-base-200 shadow-sm group-hover:border-primary transition-colors">
                    <x-mary-avatar :image="$session->patient->avatar" class="!w-12 !h-12" />
                    <div>
                        <div class="text-xs text-gray-500">{{ __('View Health Folder') }}</div>
                        <div class="font-bold group-hover:text-primary">{{ $session->patient->first_name }}
                            {{ $session->patient->last_name }}</div>
                    </div>
                    <x-mary-icon name="o-chevron-right" class="ml-auto text-gray-300 group-hover:text-primary" />
                </div>
            </a>

        </div>
    </div>

    {{-- 🚫 Modal --}}
    <x-mary-modal wire:model="showCancelModal" title="{{ __('Cancel Session') }}" class="backdrop-blur">
        <div class="mb-4 text-gray-600">
            {{ __("Please provide a reason for cancelling. This will be recorded in the patient's history.") }}</div>
        <x-mary-textarea wire:model="cancellationReason" placeholder="{{ __('Reason...') }}" rows="3" />
        <x-slot:actions>
            <x-mary-button label="{{ __('Keep Session') }}" @click="$wire.showCancelModal = false" />
            <x-mary-button label="{{ __('Confirm Cancel') }}" class="btn-error" wire:click="cancelSession" />
        </x-slot:actions>
    </x-mary-modal>

</div>
