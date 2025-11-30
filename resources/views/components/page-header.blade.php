@props([
    'title',
    'subtitle' => null, // 'subtitle' is optional, defaulting to null
])

<div class="relative mb-6 w-full">
    {{-- Display the primary title --}}
    <flux:heading size="xl" level="1">{{ $title }}</flux:heading>

    {{-- Display the optional subtitle if provided --}}
    @if ($subtitle)
        <flux:subheading size="lg" class="mb-6">
            {{ $subtitle }}
        </flux:subheading>
    @endif

    <flux:separator variant="subtle" />
</div>
