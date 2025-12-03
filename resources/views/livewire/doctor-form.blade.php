<div class="max-w-5xl mx-auto space-y-8">

    <x-page-header title="{{ $doctor ? __('Edit Doctor Profile') : __('New Doctor Profile') }}"
        subtitle="{{ $doctor ? __('Update details for Dr. ' . $name) : __('Register a medical professional and configure their access.') }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" link="{{ route('doctors.list') }}" class="btn-ghost" />
            <x-mary-button label="{{ $doctor ? __('Save Changes') : __('Create Account') }}" icon="o-check"
                class="btn-primary" wire:click="save" spinner="save" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 👤 LEFT COLUMN: Identity & Credentials --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Avatar & Identity --}}
            <x-mary-card class="bg-base-100 shadow-md text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-20 bg-gradient-to-r from-primary/10 to-secondary/10"></div>

                <div class="relative mt-8 mb-4">
                    {{-- ✅ Logic: New Upload > Existing DB Avatar > Placeholder --}}
                    @if ($avatar)
                        {{-- New Upload Preview --}}
                        <img src="{{ $avatar->temporaryUrl() }}"
                            class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg" />
                    @elseif($existingAvatar)
                        {{-- Existing Database Avatar --}}
                        <img src="{{ $existingAvatar }}"
                            class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg" />
                    @else
                        {{-- Placeholder --}}
                        <div
                            class="w-32 h-32 rounded-full mx-auto bg-base-200 flex items-center justify-center border-4 border-white shadow-lg text-gray-400">
                            <x-mary-icon name="o-user" class="w-16 h-16" />
                        </div>
                    @endif

                    <label for="avatar-upload"
                        class="absolute bottom-0 right-1/4 translate-x-4 bg-primary text-white p-2 rounded-full cursor-pointer hover:bg-primary-focus transition shadow-md"
                        title="{{ __('Upload Photo') }}">
                        <x-mary-icon name="o-camera" class="w-4 h-4" />
                    </label>
                    <input type="file" id="avatar-upload" wire:model="avatar" class="hidden" accept="image/*" />
                </div>

                {{-- ... (Name inputs remain the same) ... --}}
                <div class="px-4 pb-6 space-y-4">
                    <x-mary-input label="{{ __('Full Name') }}" wire:model="name" icon="o-user"
                        placeholder="Dr. John Doe" />
                    {{-- ... --}}
                </div>
            </x-mary-card>

            {{-- Security --}}
            <x-mary-card title="{{ __('Security') }}" shadow separator>
                <div class="space-y-4">
                    <x-mary-input label="{{ __('Email (Login ID)') }}" wire:model="email" type="email"
                        icon="o-at-symbol" />

                    {{-- ✅ Helper text for Edit Mode --}}
                    @if ($doctor)
                        <div class="text-xs text-warning flex gap-1">
                            <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                            <span>{{ __('Leave password blank to keep current one.') }}</span>
                        </div>
                    @endif

                    {{-- Password Fields (Same as before) --}}
                    <div class="relative">
                        <x-mary-input label="{{ __('Password') }}" wire:model="password"
                            type="{{ $showPassword ? 'text' : 'password' }}" icon="o-key" />
                        {{-- ... Buttons ... --}}
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
                subtitle="{{ __('Configure what this doctor can access in the system.') }}" shadow separator>
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

            {{-- Save Button (Bottom Mobile) --}}
            <div class="flex justify-end lg:hidden">
                <x-mary-button label="{{ __('Create Doctor Account') }}" icon="o-check" class="btn-primary w-full"
                    wire:click="save" spinner="save" />
            </div>

        </div>
    </div>
</div>
