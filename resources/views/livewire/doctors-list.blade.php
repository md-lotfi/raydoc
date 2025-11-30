<div>
    <x-mary-header title="{{ __('Doctor Management') }}"
        subtitle="{{ __('List of all registered medical professionals.') }}" separator>
        <x-slot:actions>
            {{-- Search Input --}}
            <x-mary-input placeholder="{{ __('Search Doctors...') }}" wire:model.live.debounce="search"
                icon="o-magnifying-glass" clearable />

            {{-- Link to create a new doctor --}}
            <x-mary-button icon="o-plus" label="{{ __('Add New Doctor') }}" class="btn-primary"
                link="{{ route('doctors.create') }}" />
        </x-slot:actions>
    </x-mary-header>

    @if (session('success'))
        <x-mary-alert icon="o-check-circle" class="alert-success mb-4">
            {{ session('success') }}
        </x-mary-alert>
    @endif

    {{-- Mary UI Table --}}
    <x-mary-table :headers="$this->headers()" :rows="$doctors" :sort-by="$sortBy" with-pagination>

        {{-- Custom scope for Name (Link to doctor profile/edit page) --}}
        @scope('cell_name', $doctor)
            <a href="{{ route('doctors.edit', $doctor) }}" class="link link-primary font-semibold">
                {{ $doctor->name }}
            </a>
        @endscope

        {{-- Custom scope for DOB formatting --}}
        @scope('cell_date_of_birth', $doctor)
            {{ $doctor->date_of_birth ? \Carbon\Carbon::parse($doctor->date_of_birth)->format('M d, Y') : 'N/A' }}
        @endscope

        {{-- Custom scope for actions column --}}
        @scope('actions', $doctor)
            <div class="flex items-center space-x-2">
                <x-mary-button icon="o-eye" link="{{ route('doctors.detail', $doctor) }}" class="btn-sm btn-ghost"
                    tooltip="{{ __('View Details') }}" />

                {{-- Delete Button with Livewire confirmation --}}
                <x-mary-button icon="o-trash" wire:click="delete({{ $doctor->id }})"
                    wire:confirm.icon.error="{{ __('Are you sure you want to remove this doctor?') }}"
                    class="btn-sm btn-error" tooltip="{{ __('Delete Doctor') }}" />
            </div>
        @endscope
    </x-mary-table>
</div>
