<div>
    <x-mary-header title="{{ __('Assistant Management') }}"
        subtitle="{{ __('List of all administrative and support staff.') }}" separator>
        <x-slot:actions>
            {{-- Search Input --}}
            <x-mary-input placeholder="{{ __('Search Assistants...') }}" wire:model.live.debounce="search"
                icon="o-magnifying-glass" clearable />

            {{-- Link to create a new assistant --}}
            <x-mary-button icon="o-plus" label="{{ __('Add New Assistant') }}" class="btn-primary"
                link="{{ route('assistants.create') }}" />
        </x-slot:actions>
    </x-mary-header>

    @if (session('success'))
        <x-mary-alert icon="o-check-circle" class="alert-success mb-4">
            {{ session('success') }}
        </x-mary-alert>
    @endif

    {{-- Mary UI Table --}}
    <x-mary-table :headers="$this->headers()" :rows="$assistants" :sort-by="$sortBy" with-pagination>

        {{-- Custom scope for Name (Link to assistant profile/edit page) --}}
        @scope('cell_name', $assistant)
            {{-- Assuming a route for editing/viewing assistant details exists --}}
            <a href="{{ route('assistants.edit', $assistant) }}" class="link link-secondary font-semibold">
                {{ $assistant->name }}
            </a>
        @endscope

        {{-- Custom scope for DOB formatting --}}
        @scope('cell_date_of_birth', $assistant)
            {{ $assistant->date_of_birth ? \Carbon\Carbon::parse($assistant->date_of_birth)->format('M d, Y') : 'N/A' }}
        @endscope

        {{-- Custom scope for actions column --}}
        @scope('actions', $assistant)
            <div class="flex items-center space-x-2">
                <x-mary-button icon="o-eye" link="{{ route('assistants.detail', $assistant) }}" class="btn-sm btn-ghost"
                    tooltip="{{ __('View Details') }}" />

                {{-- Delete Button with Livewire confirmation --}}
                <x-mary-button icon="o-trash" wire:click="delete({{ $assistant->id }})"
                    wire:confirm.icon.error="{{ __('Are you sure you want to remove this assistant?') }}"
                    class="btn-sm btn-error" tooltip="{{ __('Delete Assistant') }}" />
            </div>
        @endscope
    </x-mary-table>
</div>
