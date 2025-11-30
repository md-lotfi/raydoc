@props([
    // The Livewire property name to bind to (e.g., 'search'). Required for Livewire functionality.
    'model',
    // The placeholder text for the input field.
    'placeholder' => __('Search...'),
])

{{-- Outer container uses 'justify-between' to align buttons left and search right --}}

<div class="flex items-center justify-between mb-4 space-x-4">

    {{-- 1. ACTION BUTTONS SLOT (New) --}}
    {{-- This slot is where the user will place 'Add', 'Export', etc. buttons --}}
    <div class="flex items-center space-x-3">
        {{ $actions ?? '' }} {{-- Use $actions ?? '' to allow the slot to be optional --}}
    </div>

    {{-- 2. SEARCH BAR CONTAINER --}}
    <div class="w-full max-w-sm min-w-[200px]">

        <div class="relative flex items-center">

            {{-- Search Icon (SVG) --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                class="absolute w-5 h-5 top-2.5 left-2.5 text-slate-600">
                <path fill-rule="evenodd"
                    d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z"
                    clip-rule="evenodd" />
            </svg>

            {{-- Input Field --}}
            <input
                {{ $attributes->merge([
                    'class' =>
                        'w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md pl-10 pr-3 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm focus:shadow',
                ]) }}
                wire:model.live.debounce.300ms="{{ $model }}" {{-- Added debounce for performance --}}
                placeholder="{{ $placeholder }}" type="text" />
        </div>
    </div>
</div>
