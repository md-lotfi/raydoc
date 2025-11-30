<div>
    <x-page-header :title="__('Add New Doctor')" :subtitle="__('Create a new user and assign the Doctor role and permissions.')" />

    @if (session('success'))
        <x-mary-alert icon="o-check-circle" class="alert-success mb-4">
            {{ session('success') }}
        </x-mary-alert>
    @endif

    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 📝 Left Column: Required Credentials --}}
            <div class="lg:col-span-1 space-y-6">
                <x-mary-card title="{{ __('Login Credentials') }}" shadow separator>
                    <x-mary-input label="{{ __('Full Name') }}" wire:model="name" icon="o-user" required />
                    <x-mary-input label="{{ __('Email') }}" wire:model="email" type="email" icon="o-at-symbol"
                        required />

                    <x-mary-input label="{{ __('Password') }}" wire:model="password" type="password" icon="o-key"
                        required />
                    <x-mary-input label="{{ __('Confirm Password') }}" wire:model="passwordConfirmation" type="password"
                        icon="o-key" required />
                </x-mary-card>
            </div>

            {{-- 👤 Center Column: Personal Details --}}
            <div class="lg:col-span-1 space-y-6">
                <x-mary-card title="{{ __('Personal Details') }}" shadow separator>
                    <x-mary-input label="{{ __('Date of Birth') }}" wire:model="dateOfBirth" type="date"
                        icon="o-calendar" />

                    <x-mary-select label="{{ __('Gender') }}" :options="toSelectOptions()" single wire:model="gender"
                        icon="o-users" />

                    <x-mary-input label="{{ __('Phone') }}" wire:model="phone" icon="o-phone" />
                    <x-mary-input label="{{ __('Address') }}" wire:model="address" icon="o-map-pin" />
                    <x-mary-input label="{{ __('City') }}" wire:model="city" icon="o-building-office-2" />
                </x-mary-card>
            </div>

            {{-- 🔒 Right Column: Permissions --}}
            <div class="lg:col-span-1 space-y-6">
                <x-mary-card title="{!! __('Role & Permissions') !!}" shadow separator>
                    <x-mary-input label="{{ __('Assigned Role') }}" value="Doctor" readonly icon="o-shield-check"
                        class="text-info font-bold" />

                    <x-mary-header title="{{ __('Specific Permissions') }}"
                        subtitle="{{ __('Grant granular access rights.') }}" size="text-lg" separator />

                    <div class="space-y-2">
                        @foreach ($doctorPermissions as $permission)
                            <x-mary-checkbox :label="ucwords(str_replace('_', ' ', $permission))" wire:model.live="permissions"
                                value="{{ $permission }}" />
                        @endforeach
                    </div>
                </x-mary-card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="mt-8 flex justify-end">
            <x-mary-button type="submit" label="{{ __('Create Doctor') }}" icon="o-user-plus"
                class="btn-primary btn-lg" spinner />
        </div>
    </form>
</div>
