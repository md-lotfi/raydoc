<div class="space-y-6">

    {{-- 🟢 PAGE HEADER --}}
    <x-page-header title="{{ __('Assistants & Staff') }}" subtitle="{{ __('Manage administrative support accounts.') }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Add New Assistant') }}" icon="o-plus" class="btn-primary"
                link="{{ route('assistants.create') }}" />
        </x-slot:actions>
    </x-page-header>

    {{-- 🎛️ CONTROLS --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search name, email, or phone...') }}"
                wire:model.live.debounce.300ms="search" class="w-full" />
        </div>

        {{-- Stats Badge --}}
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <x-mary-icon name="o-users" class="w-4 h-4" />
            <span>{{ $this->assistants->total() }} {{ __('Total Assistants') }}</span>
        </div>
    </div>

    {{-- 📋 ASSISTANTS TABLE --}}
    <x-mary-card shadow class="bg-base-100">
        <x-mary-table container-class="" :headers="$this->headers()" :rows="$this->assistants" :sort-by="$sortBy" :link="route('assistants.edit', ['assistant' => '[id]'])"
            {{-- Auto-link rows to edit page --}} class="cursor-pointer hover:bg-base-50" with-pagination>

            {{-- 👤 Name & Avatar --}}
            @scope('cell_name', $assistant)
                <div class="flex items-center gap-3">
                    <x-mary-avatar :image="$assistant->avatar ?? null" :title="$assistant->name" class="!w-10 !h-10 border border-base-200" />
                    <div>
                        <div class="font-bold text-gray-800">{{ $assistant->name }}</div>
                        <div class="text-xs text-gray-400">{{ __('Admin Support') }}</div>
                    </div>
                </div>
            @endscope

            {{-- 📧 Contact --}}
            @scope('cell_email', $assistant)
                <div class="flex flex-col">
                    <span class="text-sm font-medium">{{ $assistant->email }}</span>
                    @if ($assistant->phone)
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <x-mary-icon name="o-phone" class="w-3 h-3" /> {{ $assistant->phone }}
                        </span>
                    @endif
                </div>
            @endscope

            {{-- 🗓️ Date --}}
            @scope('cell_created_at', $assistant)
                <div class="text-gray-500 text-sm">
                    {{ $assistant->created_at->translatedFormat('M d, Y') }}
                </div>
            @endscope

            {{-- ⚙️ Actions --}}
            @scope('actions', $assistant)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-sm btn-ghost btn-circle" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('Edit Profile') }}" icon="o-pencil"
                            link="{{ route('assistants.edit', $assistant->id) }}" />

                        <x-mary-menu-item title="{{ __('Delete Account') }}" icon="o-trash" class="text-error"
                            wire:click="confirmDelete({{ $assistant->id }})" />
                    </x-mary-dropdown>
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- 🗑️ Delete Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="{{ __('Delete Account') }}" class="backdrop-blur">
        <div class="mb-5 text-gray-600">
            {{ __('Are you sure you want to delete this assistant? This will revoke their access to the system immediately.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Confirm Delete') }}" wire:click="delete" class="btn-error" spinner />
        </x-slot:actions>
    </x-mary-modal>

</div>
