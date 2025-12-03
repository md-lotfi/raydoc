<div class="space-y-6">

    {{-- 🟢 PAGE HEADER --}}
    <x-page-header title="{{ __('Medical Staff') }}" subtitle="{{ __('Manage doctor accounts and permissions.') }}"
        separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Add New Doctor') }}" icon="o-plus" class="btn-primary"
                link="{{ route('doctors.create') }}" />
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
            <span>{{ $this->doctors->total() }} {{ __('Total Doctors') }}</span>
        </div>
    </div>

    {{-- 📋 DOCTORS TABLE --}}
    <x-mary-card shadow class="bg-base-100">
        <x-mary-table container-class="" :headers="$this->headers()" :rows="$this->doctors" :sort-by="$sortBy" :link="route('doctors.edit', ['doctor' => '[id]'])"
            {{-- Auto-link rows to edit page --}} class="cursor-pointer hover:bg-base-50" with-pagination>

            {{-- 👤 Name & Avatar --}}
            @scope('cell_name', $doctor)
                <div class="flex items-center gap-3">
                    <x-mary-avatar :image="$doctor->avatar ?? null" :title="$doctor->name" class="!w-10 !h-10 border border-base-200" />
                    <div>
                        <div class="font-bold text-gray-800">{{ $doctor->name }}</div>
                        <div class="text-xs text-gray-400">{{ $doctor->specialization ?? __('General Practitioner') }}</div>
                    </div>
                </div>
            @endscope

            {{-- 📧 Contact --}}
            @scope('cell_email', $doctor)
                <div class="flex flex-col">
                    <span class="text-sm font-medium">{{ $doctor->email }}</span>
                    @if ($doctor->phone)
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <x-mary-icon name="o-phone" class="w-3 h-3" /> {{ $doctor->phone }}
                        </span>
                    @endif
                </div>
            @endscope

            {{-- 🗓️ Date --}}
            @scope('cell_created_at', $doctor)
                <div class="text-gray-500 text-sm">
                    {{ $doctor->created_at->translatedFormat('M d, Y') }}
                </div>
            @endscope

            {{-- ⚙️ Actions --}}
            @scope('actions', $doctor)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-sm btn-ghost btn-circle" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('Edit Profile') }}" icon="o-pencil"
                            link="{{ route('doctors.edit', $doctor->id) }}" />

                        {{-- Add specific doctor actions here (e.g. View Schedule) --}}
                        {{-- <x-mary-menu-item title="View Schedule" icon="o-calendar" link="#" /> --}}

                        <x-mary-menu-item title="{{ __('Delete Account') }}" icon="o-trash" class="text-error"
                            wire:click="confirmDelete({{ $doctor->id }})" />
                    </x-mary-dropdown>
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- 🗑️ Delete Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="{{ __('Delete Account') }}" class="backdrop-blur">
        <div class="mb-5 text-gray-600">
            {{ __('Are you sure you want to delete this doctor? This will revoke their access to the system immediately.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Confirm Delete') }}" wire:click="delete" class="btn-error" spinner />
        </x-slot:actions>
    </x-mary-modal>

</div>
