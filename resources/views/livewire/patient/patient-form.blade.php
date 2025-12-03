<div class="max-w-5xl mx-auto space-y-6">

    {{-- 🟢 HEADER --}}
    <x-page-header title="{{ $patient ? __('Edit Patient') : __('Register Patient') }}"
        subtitle="{{ $patient ? $first_name . ' ' . $last_name : __('Create a new patient record') }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" link="{{ route('patient.list') }}" class="btn-ghost" />
            <x-mary-button label="{{ __('Save Changes') }}" icon="o-check" class="btn-primary" wire:click="save"
                spinner="save" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- 🖼️ LEFT COLUMN: Identity & Status --}}
        <div class="lg:col-span-1 space-y-6">

            <x-mary-card class="bg-base-100 shadow-md text-center relative overflow-hidden">
                {{-- Background decorative pattern --}}
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-primary/10 to-secondary/10"></div>

                <div class="relative mt-6 mb-4">
                    {{-- Avatar Preview Logic --}}
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}"
                            class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg" />
                    @elseif($existingAvatar)
                        <img src="{{ $existingAvatar }}"
                            class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg" />
                    @else
                        <div
                            class="w-32 h-32 rounded-full mx-auto bg-base-200 flex items-center justify-center border-4 border-white shadow-lg">
                            <x-mary-icon name="o-user" class="w-16 h-16 text-gray-400" />
                        </div>
                    @endif

                    {{-- Upload Button Overlay --}}
                    <label for="avatar-upload"
                        class="absolute bottom-0 right-1/4 translate-x-4 bg-primary text-white p-2 rounded-full cursor-pointer hover:bg-primary-focus transition shadow-md"
                        title="{{ __('Change Avatar') }}">
                        <x-mary-icon name="o-camera" class="w-4 h-4" />
                    </label>
                    <input type="file" id="avatar-upload" wire:model="avatar" class="hidden" accept="image/*" />
                </div>

                <div class="px-4 pb-4">
                    <h2 class="text-xl font-bold">
                        {{ $first_name && $last_name ? "$first_name $last_name" : __('New Patient') }}</h2>
                    <p class="text-gray-500 text-sm mb-4">{{ $email ?? __('No email provided') }}</p>

                    {{-- Status Toggle --}}
                    <div class="flex justify-center">
                        <div class="form-control">
                            <label
                                class="label cursor-pointer gap-2 border border-base-200 rounded-full px-4 py-1 hover:bg-base-200 transition">
                                <span
                                    class="label-text font-semibold {{ $is_active ? 'text-success' : 'text-gray-400' }}">
                                    {{ $is_active ? __('Active Record') : __('Archived') }}
                                </span>
                                <input type="checkbox" wire:model="is_active" class="toggle toggle-success toggle-sm" />
                            </label>
                        </div>
                    </div>
                </div>
            </x-mary-card>

            {{-- Validation Errors Summary (Optional but helpful) --}}
            @if ($errors->any())
                <x-mary-alert icon="o-exclamation-triangle" class="alert-error text-sm">
                    {{ __('Please check the form for errors.') }}
                </x-mary-alert>
            @endif
        </div>

        {{-- 📝 RIGHT COLUMN: Form Data --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Personal Info --}}
            <x-mary-card title="{{ __('Personal Information') }}" separator shadow>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-mary-input label="{{ __('First Name') }}" wire:model="first_name" icon="o-user"
                        placeholder="e.g. John" />

                    <x-mary-input label="{{ __('Last Name') }}" wire:model="last_name" icon="o-user"
                        placeholder="e.g. Doe" />

                    <x-mary-input label="{{ __('Date of Birth') }}" wire:model="date_of_birth" type="date"
                        icon="o-calendar" />

                    <x-mary-select label="{{ __('Gender') }}" wire:model="gender" :options="$genderOptions" icon="o-users"
                        placeholder="{{ __('Select...') }}" />

                </div>
            </x-mary-card>

            {{-- Contact Info --}}
            <x-mary-card title="{{ __('Contact Details') }}" separator shadow>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-mary-input label="{{ __('Email Address') }}" wire:model="email" type="email"
                        icon="o-envelope" />

                    <x-mary-input label="{{ __('Phone Number') }}" wire:model="phone_number" icon="o-phone" />

                    <div class="md:col-span-2">
                        <x-mary-input label="{{ __('Address') }}" wire:model="address" icon="o-map-pin"
                            placeholder="{{ __('Street address, Apt, Suite') }}" />
                    </div>

                    <x-mary-input label="{{ __('City') }}" wire:model="city" icon="o-building-office" />

                </div>
            </x-mary-card>

            {{-- Action Buttons (Mobile Friendly Bottom Bar) --}}
            <div class="flex justify-end gap-3 pt-4">
                <x-mary-button label="{{ __('Cancel') }}" link="{{ route('patient.list') }}" />
                <x-mary-button label="{{ __('Save Patient') }}" class="btn-primary" icon="o-check" type="submit"
                    wire:click="save" spinner="save" />
            </div>

        </div>
    </div>
</div>
