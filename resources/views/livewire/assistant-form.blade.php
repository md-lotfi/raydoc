<div class="max-w-5xl mx-auto space-y-8">

    <x-page-header title="{{ $assistant ? __('Edit Assistant Profile') : __('New Assistant Profile') }}"
        subtitle="{{ $assistant ? __('Update details for ' . $name) : __('Register a new clinic assistant and configure access.') }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" link="{{ route('assistants.list') }}" class="btn-ghost" />
            <x-mary-button label="{{ $assistant ? __('Save Changes') : __('Create Account') }}" icon="o-check"
                class="btn-primary" wire:click="save" spinner="save" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 👤 LEFT COLUMN: Identity & Credentials --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Avatar & Identity --}}
            <x-mary-card class="bg-base-100 shadow-md text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-20 bg-gradient-to-r from-secondary/10 to-primary/10"></div>

                <div class="relative mt-8 mb-4">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}"
                            class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg" />
                    @elseif($existingAvatar)
                        <img src="{{ $existingAvatar }}"
                            class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg" />
                    @else
                        <div
                            class="w-32 h-32 rounded-full mx-auto bg-base-200 flex items-center justify-center border-4 border-white shadow-lg text-gray-400">
                            <x-mary-icon name="o-user" class="w-16 h-16" />
                        </div>
                    @endif
                    <label for="avatar-upload"
                        class="absolute bottom-0 right-1/4 translate-x-4 bg-secondary text-white p-2 rounded-full cursor-pointer hover:bg-secondary-focus transition shadow-md"
                        title="{{ __('Upload Photo') }}">
                        <x-mary-icon name="o-camera" class="w-4 h-4" />
                    </label>
                    <input type="file" id="avatar-upload" wire:model="avatar" class="hidden" accept="image/*" />
                </div>

                <div class="px-4 pb-6 space-y-4">
                    <x-mary-input label="{{ __('Full Name') }}" wire:model="name" icon="o-user"
                        placeholder="e.g. Sarah Smith" />
                    <x-mary-select label="{{ __('Gender') }}" :options="toSelectOptions()" wire:model="gender" icon="o-users"
                        placeholder="{{ __('Select...') }}" />
                    <x-mary-input label="{{ __('Date of Birth') }}" wire:model="dateOfBirth" type="date"
                        icon="o-calendar" />
                </div>
            </x-mary-card>

            {{-- Security --}}
            <x-mary-card title="{{ __('Security') }}" shadow separator>
                <div class="space-y-4">
                    <x-mary-input label="{{ __('Email (Login ID)') }}" wire:model="email" type="email"
                        icon="o-at-symbol" />

                    @if ($assistant)
                        <div class="text-xs text-warning flex gap-1">
                            <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                            <span>{{ __('Leave password blank to keep current one.') }}</span>
                        </div>
                    @endif

                    {{-- Password Field --}}
                    <div class="relative">
                        <x-mary-input label="{{ __('Password') }}" wire:model="password"
                            type="{{ $showPassword ? 'text' : 'password' }}" icon="o-key" />
                        <div class="absolute top-8 right-2 flex gap-1">
                            <button type="button" wire:click="$toggle('showPassword')"
                                class="btn btn-xs btn-ghost {{ $showPassword ? 'text-warning' : 'text-gray-400' }}">
                                <x-mary-icon name="{{ $showPassword ? 'o-eye-slash' : 'o-eye' }}" class="w-4 h-4" />
                            </button>
                            <button type="button" wire:click="generatePassword"
                                class="btn btn-xs btn-ghost text-secondary" title="{{ __('Generate Random') }}">
                                <x-mary-icon name="o-arrow-path" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <x-mary-input label="{{ __('Confirm Password') }}" wire:model="passwordConfirmation"
                        type="{{ $showPassword ? 'text' : 'password' }}" icon="o-key" />
                </div>
            </x-mary-card>
        </div>

        {{-- 📝 RIGHT COLUMN: Contact & Permissions --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Contact Info --}}
            <x-mary-card title="{{ __('Contact Information') }}" shadow separator>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-mary-input label="{{ __('Phone Number') }}" wire:model="phone" icon="o-phone" />
                    <x-mary-input label="{{ __('City') }}" wire:model="city" icon="o-building-office" />
                    <div class="md:col-span-2">
                        <x-mary-input label="{{ __('Full Address') }}" wire:model="address" icon="o-map-pin" />
                    </div>
                </div>
            </x-mary-card>

            {{-- Permissions Matrix --}}
            <x-mary-card title="{{ __('Access Control') }}"
                subtitle="{{ __('Configure permissions for this assistant.') }}" shadow separator>
                <x-slot:menu>
                    <x-mary-checkbox label="{{ __('Select All') }}" wire:model.live="selectAllPermissions" />
                </x-slot:menu>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto p-1">
                    @foreach ($availablePermissions as $permission)
                        <div
                            class="border border-base-200 rounded-lg p-3 hover:bg-base-50 transition-colors flex items-center justify-between">
                            <label class="cursor-pointer flex-1 flex items-center gap-3">
                                <x-mary-checkbox wire:model="permissions" value="{{ $permission }}" />
                                <span
                                    class="text-sm font-medium">{{ ucwords(str_replace('_', ' ', $permission)) }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </x-mary-card>

            {{-- Save Button (Mobile) --}}
            <div class="flex justify-end lg:hidden">
                <x-mary-button label="{{ __('Save Assistant') }}" icon="o-check" class="btn-primary w-full"
                    wire:click="save" spinner="save" />
            </div>

        </div>
    </div>
</div>
