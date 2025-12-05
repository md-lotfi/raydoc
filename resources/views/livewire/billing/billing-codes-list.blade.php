<div class="space-y-6">

    {{-- 🟢 PAGE HEADER --}}
    <x-page-header title="{{ __('Billing Codes') }}"
        subtitle="{{ __('Manage CPT codes, service rates, and duration rules.') }}" separator>
        <x-slot:actions>
            <x-mary-button label="{{ __('Add Service Code') }}" icon="o-plus" class="btn-primary"
                link="{{ route('billing.codes.create') }}" />
        </x-slot:actions>
    </x-page-header>

    {{-- 📊 STATS OVERVIEW --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-mary-stat title="{{ __('Total Services') }}" value="{{ $stats['total'] }}" icon="o-tag"
            class="bg-base-100 shadow-sm border border-base-200" />
        <x-mary-stat title="{{ __('Active Codes') }}" value="{{ $stats['active'] }}" icon="o-check-circle"
            class="bg-base-100 shadow-sm border border-base-200" color="text-success" />
        <x-mary-stat title="{{ __('Inactive/Archived') }}" value="{{ $stats['inactive'] }}" icon="o-archive-box"
            class="bg-base-100 shadow-sm border border-base-200" color="text-gray-400" />
    </div>

    {{-- 🎛️ CONTROLS BAR --}}
    <div
        class="flex flex-col md:flex-row gap-4 justify-between items-center bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
        <div class="w-full md:w-1/3">
            <x-mary-input icon="o-magnifying-glass" placeholder="{{ __('Search code or name...') }}"
                wire:model.live.debounce.300ms="search" class="w-full" />
        </div>

        <div class="flex gap-2">
            <div class="join">
                <button class="join-item btn btn-sm {{ $statusFilter === '' ? 'btn-neutral' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', '')">{{ __('All') }}</button>
                <button
                    class="join-item btn btn-sm {{ $statusFilter === '1' ? 'btn-active btn-success text-white' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', '1')">{{ __('Active') }}</button>
                <button
                    class="join-item btn btn-sm {{ $statusFilter === '0' ? 'btn-active btn-ghost bg-base-300' : 'btn-ghost' }}"
                    wire:click="$set('statusFilter', '0')">{{ __('Inactive') }}</button>
            </div>
        </div>
    </div>

    {{-- 📋 CODES TABLE --}}
    <x-mary-card shadow class="bg-base-100">
        <x-mary-table container-class="" :headers="$this->headers()" :rows="$billingCodes" :sort-by="$sortBy" :link="route('billing.codes.edit', ['billingCode' => '[id]'])"
            class="cursor-pointer hover:bg-base-50" with-pagination>

            {{-- 🏷️ Code --}}
            @scope('cell_code', $code)
                <span class="font-mono text-primary font-bold text-lg">{{ $code->code }}</span>
            @endscope

            {{-- 📝 Name & Description --}}
            @scope('cell_name', $code)
                <div>
                    <div class="font-bold text-gray-800">{{ $code->name }}</div>
                    @if ($code->description)
                        <div class="text-xs text-gray-500 truncate max-w-xs mt-0.5">{{ $code->description }}</div>
                    @endif
                </div>
            @endscope

            {{-- 💲 Rate --}}
            @scope('cell_standard_rate', $code)
                <span class="text-success font-bold font-mono">{{ format_currency($code->standard_rate) }}</span>
            @endscope

            {{-- ⏱️ Duration Badge --}}
            @scope('cell_duration', $code)
                @if ($code->min_duration_minutes || $code->max_duration_minutes)
                    <div class="text-xs text-gray-600 bg-base-200 px-2 py-1 rounded inline-flex items-center gap-1">
                        <x-mary-icon name="o-clock" class="w-3 h-3" />
                        @if ($code->min_duration_minutes && $code->max_duration_minutes)
                            {{ $code->min_duration_minutes }}-{{ $code->max_duration_minutes }}m
                        @elseif($code->min_duration_minutes)
                            {{ $code->min_duration_minutes }}m+
                        @else
                            Max {{ $code->max_duration_minutes }}m
                        @endif
                    </div>
                @else
                    <span class="text-gray-300 text-xs">-</span>
                @endif
            @endscope

            {{-- 💡 Status Toggle --}}
            @scope('cell_is_active', $code)
                <div @click.stop>
                    <x-mary-badge :value="$code->is_active ? __('Active') : __('Hidden')"
                        class="cursor-pointer {{ $code->is_active ? 'badge-success/10 text-success' : 'badge-ghost text-gray-400' }}"
                        wire:click="toggleStatus({{ $code->id }})" />
                </div>
            @endscope

            {{-- ⚙️ Actions --}}
            @scope('actions', $code)
                <div @click.stop>
                    <x-mary-dropdown right>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-vertical" class="btn-sm btn-ghost btn-circle" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="{{ __('Edit') }}" icon="o-pencil"
                            link="{{ route('billing.codes.edit', $code->id) }}" />

                        <x-mary-menu-item title="{{ $code->is_active ? __('Deactivate') : __('Activate') }}" icon="o-power"
                            wire:click="toggleStatus({{ $code->id }})" />

                        <x-mary-menu-item title="{{ __('Delete') }}" icon="o-trash" class="text-error"
                            wire:click="confirmDelete({{ $code->id }})" />
                    </x-mary-dropdown>
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- 🗑️ Delete Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="{{ __('Delete Service Code') }}" class="backdrop-blur">
        <div class="mb-5 text-gray-600">
            {{ __('Are you sure you want to delete this service code? This may affect historical invoices if they reference this code.') }}
        </div>
        <x-slot:actions>
            <x-mary-button label="{{ __('Cancel') }}" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="{{ __('Confirm Delete') }}" wire:click="delete" class="btn-error" spinner />
        </x-slot:actions>
    </x-mary-modal>

</div>
