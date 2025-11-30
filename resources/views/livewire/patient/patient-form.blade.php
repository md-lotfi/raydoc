<div class="p-4">

    <x-page-header :title="__('Patient Edit')" :subtitle="$first_name . ' ' . $last_name" />

    <form wire:submit.prevent="save">

        {{-- Success/Error Alerts at the top --}}
        @if (session()->has('error'))
            <x-mary-alert icon="o-exclamation-triangle" class="alert-error mb-4">
                {{ session('error') }}
            </x-mary-alert>
        @endif
        @if (session()->has('success'))
            <x-mary-alert icon="o-check-circle" class="alert-success mb-4">
                {{ session('success') }}
            </x-mary-alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 📝 Left Column: Personal and Contact Details --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- 1. Personal Information Card --}}
                <x-mary-card title="{{ __('Personal Information') }}" shadow separator>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input label="{{ __('First name') }}" wire:model="first_name"
                            placeholder="{{ __('Enter first name') }}" />

                        <flux:input label="{{ __('Last name') }}" wire:model="last_name"
                            placeholder="{{ __('Enter last name') }}" />

                        <flux:input label="{{ __('Birth data') }}" type="date" wire:model="date_of_birth"
                            placeholder="{{ __('Enter birthdate') }}" />

                        {{-- Gender Select Field --}}
                        <div>
                            <flux:label>{{ __('Gender') }}</flux:label>
                            <flux:select wire:model="gender" placeholder="Choose patient gender...">
                                @foreach (config('constants.GENDERS') as $g)
                                    <flux:select.option value="{{ $g }}">{{ $g }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>
                </x-mary-card>


                {{-- 2. Contact & Location Card --}}
                <x-mary-card title="{{ __('Contact & Location') }}" shadow separator>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input label="{{ __('Email') }}" type="email" wire:model="email"
                            placeholder="{{ __('Enter email') }}" />

                        <flux:input label="{{ __('Phone number') }}" type="tel" wire:model="phone_number"
                            placeholder="{{ __('Enter phone number') }}" />

                        <div class="md:col-span-2">
                            <flux:input label="{{ __('Address') }}" wire:model="address"
                                placeholder="{{ __('Enter full street address') }}" />
                        </div>

                        <flux:input label="{{ __('City') }}" wire:model="city"
                            placeholder="{{ __('Enter city') }}" />

                        {{-- Add a State/Province field for completeness --}}
                        <flux:input label="{{ __('State/Region') }}" wire:model="state"
                            placeholder="{{ __('Enter state or region') }}" />
                    </div>
                </x-mary-card>

                {{-- Submit Button --}}
                <div class="flex justify-end pt-4">
                    <flux:button type="submit" class="btn-primary">
                        <span wire:loading.remove>{{ __('Save Patient') }}</span>
                        <span wire:loading>{{ __('Saving ...') }}</span>
                    </flux:button>
                </div>

            </div>

            {{-- 🖼️ Right Column: Avatar Management --}}
            <div class="lg:col-span-1 space-y-6">

                <x-mary-card title="{{ __('Patient Avatar') }}" shadow separator>

                    {{-- Large Avatar Display --}}
                    <div class="flex justify-center mb-6">
                        @if ($currentAvatarPath)
                            <x-mary-avatar :image="$currentAvatarPath" class="w-48 h-48 rounded-full shadow-xl" />
                        @else
                            <x-mary-avatar icon="o-user-circle"
                                class="w-48 h-48 rounded-full bg-gray-200 text-gray-500 shadow-xl" />
                        @endif
                    </div>

                    {{-- Upload Area --}}
                    <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false"
                        x-on:livewire-upload-error="isUploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress">

                        <div class="space-y-4">

                            {{-- File Input --}}
                            <div>
                                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1">Upload New
                                    Avatar (Max 8MB)</label>
                                <input type="file" id="avatar" wire:model="avatar"
                                    class="file-input file-input-bordered file-input-primary w-full max-w-xs text-sm"
                                    accept="image/*">
                                @error('avatar')
                                    <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Progress Bar (Only visible during upload) --}}
                        <div x-show="isUploading" class="mt-4">
                            <x-mary-progress :value="0" :max="100" x-bind:value="progress"
                                class="progress-primary w-full" />
                            <span class="text-xs text-gray-500" x-text="progress + '% uploaded'"></span>
                        </div>
                    </div>

                </x-mary-card>
            </div>
        </div>
    </form>
</div>
