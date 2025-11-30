<div>

    <x-page-header :title="__('Add New Billing Code')" :subtitle="__('Define CPT/Service code and rate')" />

    @if (session()->has('success'))
        <x-alert color="success" title="Success" class="mb-4">
            {{ session('success') }}
        </x-alert>
    @endif

    <form wire:submit.prevent="saveCode" class="space-y-6">

        <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-accent-700">{{ 'Service Definition' }}</h3>

        {{-- Code and Name --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Billing Code (e.g., CPT 90837) --}}
            <div>
                <flux:input label="{{ 'Billing Code (CPT/Service)' }}" :disabled="!empty($code)" wire:model="code"
                    required placeholder="{{ __('Eg., 90837') }}" />
                @error('code')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Service Name --}}
            <div>
                <flux:input label="{{ __('Service Name') }}" wire:model="name" required
                    placeholder="{{ __('Eg., Psychotherapy, 60 minutes') }}" />
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Rate and Description --}}
        <div>
            <flux:textarea label="{{ __('Description (Optional)') }}" wire:model="description" rows="3"
                placeholder="{{ __('Detailed description of the service and its purpose.') }}" />
            @error('description')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <h3 class="text-lg font-semibold border-b pb-2 mb-4 pt-4 text-accent-700">
            {{ __('Pricing and Duration Rules') }}
        </h3>

        {{-- Standard Rate --}}
        <div>
            <flux:input icon="currency-dollar" label="{{ __('Standard Rate') }}" type="number"
                wire:model="standard_rate" step="0.01" min="0.01" required prefix="$" placeholder="150.00" />
            @error('standard_rate')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Duration Eligibility (Optional) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Minimum Duration --}}
            <div>
                <flux:input label="{{ __('Minimum Duration (minutes)') }}" type="number"
                    wire:model="min_duration_minutes" min="1" placeholder="{{ __('Eg., 53') }}" />
                <p class="text-xs text-gray-500 mt-1">
                    {{ __('If applicable, minimum time required to bill this code.') }}</p>
                @error('min_duration_minutes')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Maximum Duration --}}
            <div>
                <flux:input label="{{ __('Maximum Duration (minutes)') }}" type="number"
                    wire:model="max_duration_minutes" min="1" placeholder="{{ __('Eg., 68') }}" />
                <p class="text-xs text-gray-500 mt-1">
                    {{ __('If applicable, maximum time allowed to bill this code.') }}
                </p>
                @error('max_duration_minutes')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Active Status Toggle --}}
        <div class="pt-4">
            <flux:checkbox label="{{ __('Is Active?') }}" wire:model="is_active"
                description="{{ __('If unchecked, this code will not be available for new invoices.') }}" />
        </div>

        {{-- Submit Button --}}
        <div class="pt-6">
            <x-mary-button type="submit"
                label="{{ !empty($code) ? __('Edit Billing Code') : __('Save Billing Code') }}" icon="o-check-circle"
                class="btn-primary w-full" spinner="saveCode" />
        </div>
    </form>
</div>
