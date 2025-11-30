@props([
    // 'color' determines the Tailwind color palette used for the alert: 'danger', 'success', 'warning', 'info', or 'dark' (default).
    'color' => 'dark',
    // 'title' is the heading text for the alert.
    'title' => 'Attention:',
])

@php
    // Define class mappings for different alert colors
    $colorClasses = [
        'danger' => 'text-red-100 rounded-lg border border-red-900 bg-red-700',
        'success' => 'text-green-100 rounded-lg border border-green-900 bg-green-700',
        'warning' => 'text-amber-100 rounded-lg border border-amber-900 bg-amber-700',
        'info' => 'text-indigo-100 rounded-lg border border-indigo-900 bg-indigo-800',
        // Dark theme based on your request
        'dark' => 'text-gray-100 rounded-lg border border-gray-600 bg-gray-700',
    ];

    // Select the appropriate classes, defaulting to 'dark' if an unknown color is passed
    $alertClasses = $colorClasses[$color] ?? $colorClasses['dark'];

    // Define text colors for the nested slot content (list items, etc.)
    $contentTextColor = match ($color) {
        'dark' => 'text-gray-100', // Keep light text for dark background
        default => 'text-current', // Inherit parent text color for light backgrounds
    };

    // Define the accessibility role text
    $roleText = match ($color) {
        'danger' => 'Danger',
        'success' => 'Success',
        'warning' => 'Warning',
        'info' => 'Info',
        default => 'Information',
    };

@endphp

<div class="flex p-4 mb-4 text-sm {{ $alertClasses }}" role="alert">

    <!-- Icon (Uses the same icon for simplicity, color handled by current text color) -->
    <svg class="w-5 h-5 mr-3 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
        fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>

    <span class="sr-only">{{ $roleText }}</span>

    <div>
        <!-- Heading/Title -->
        <span class="font-semibold">{{ $title }}</span>

        <!-- Main Content Slot -->
        <div class="mt-2 {{ $contentTextColor }}">
            {{ $slot }}
        </div>
    </div>


</div>
