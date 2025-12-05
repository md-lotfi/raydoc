<div class="min-h-screen flex items-center justify-center">
    <x-mary-card title="Activate Raydoc" separator shadow class="w-full max-w-md text-center">
        <div class="mb-4">
            <p class="text-gray-500 text-sm">
                {{ __('Please enter your license key to activate this copy of Raydoc.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                {{ __('Internet connection required for initial setup.') }}
            </p>
        </div>

        <div class="space-y-4">
            <x-mary-input label="{{ __('License Key') }}" placeholder="XXXX-XXXX-XXXX-XXXX" wire:model="licenseKey"
                icon="o-key" />

            @if ($error)
                <div class="text-red-500 text-sm font-bold">{{ $error }}</div>
            @endif
            <x-mary-button label="{{ __('Activate Now') }}" wire:click="activate" class="btn-primary w-full"
                spinner="activate" />
        </div>
    </x-mary-card>
</div>
