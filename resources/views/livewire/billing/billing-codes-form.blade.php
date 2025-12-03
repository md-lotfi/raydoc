<div class="max-w-5xl mx-auto space-y-8">

    {{-- 🟢 HEADER --}}
    <x-page-header title="{{ $billingCode ? __('Edit Service Code') : __('New Service Code') }}"
        subtitle="{{ $billingCode ? __('Update pricing and rules for code :code', ['code' => $code]) : __('Define a new billable service or product.') }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" link="{{ route('billing.codes.list') }}" class="btn-ghost" />
            <x-mary-button label="{{ __('Save Code') }}" icon="o-check" class="btn-primary" wire:click="save"
                spinner="save" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 📝 LEFT COLUMN: Identification --}}
        <div class="lg:col-span-2 space-y-6">
            <x-mary-card title="{{ __('Service Details') }}"
                subtitle="{{ __('Basic information about this service.') }}" separator shadow>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Code --}}
                    <x-mary-input label="{{ __('CPT / Service Code') }}" wire:model="code" icon="o-tag"
                        placeholder="e.g. 90837" hint="{{ __('Unique identifier for billing.') }}" />

                    {{-- Name --}}
                    <x-mary-input label="{{ __('Service Name') }}" wire:model="name" icon="o-identification"
                        placeholder="{{ __('e.g. Psychotherapy, 60 min') }}" />
                </div>

                {{-- Description --}}
                <div class="mt-4">
                    <x-mary-textarea label="{{ __('Description (Optional)') }}" wire:model="description"
                        placeholder="{{ __('Internal notes or invoice description...') }}" rows="3" />
                </div>

            </x-mary-card>

            {{-- ⏱️ Constraints --}}
            <x-mary-card title="{{ __('Time Constraints') }}"
                subtitle="{{ __('Optional duration limits for this service.') }}" separator shadow>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-mary-input label="{{ __('Min Duration (min)') }}" wire:model="min_duration_minutes"
                        type="number" icon="o-clock" suffix="min" />
                    <x-mary-input label="{{ __('Max Duration (min)') }}" wire:model="max_duration_minutes"
                        type="number" icon="o-clock" suffix="min" />
                </div>
            </x-mary-card>
        </div>

        {{-- 💰 RIGHT COLUMN: Pricing & Status --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Status Card --}}
            <x-mary-card
                class="bg-base-100 shadow-md border-t-4 {{ $is_active ? 'border-success' : 'border-base-300' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold">{{ __('Status') }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ $is_active ? __('Visible in invoices') : __('Hidden from selection') }}</p>
                    </div>
                    <x-mary-toggle wire:model="is_active" class="toggle-success" />
                </div>
            </x-mary-card>

            {{-- Pricing Card --}}
            <x-mary-card title="{{ __('Pricing') }}" separator shadow>

                <x-mary-input label="{{ __('Standard Rate') }}" wire:model="standard_rate" type="number"
                    step="0.01" icon="o-currency-dollar" class="font-bold text-lg" />

                <div class="mt-4 text-xs text-gray-500 bg-base-200 p-3 rounded">
                    <x-mary-icon name="o-information-circle" class="w-4 h-4 inline mr-1" />
                    {{ __('This rate will be used as the default price. You can override it per invoice.') }}
                </div>

            </x-mary-card>

            {{-- Mobile Save Button --}}
            <div class="lg:hidden">
                <x-mary-button label="{{ __('Save Changes') }}" icon="o-check" class="btn-primary w-full"
                    wire:click="save" />
            </div>

        </div>
    </div>
</div>
