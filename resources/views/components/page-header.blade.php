@props([
    'title',
    'subtitle' => null,
    'separator' => false, // Make separator optional
])

<div {{ $attributes->class(['mb-8']) }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        {{-- 🟢 Left Side: Title & Subtitle --}}
        <div>
            <flux:heading size="xl" level="1">{{ $title }}</flux:heading>

            @if ($subtitle)
                <flux:subheading size="lg" class="mt-1 text-gray-500">
                    {{ $subtitle }}
                </flux:subheading>
            @endif
        </div>

        {{-- 🔵 Right Side: Actions Slot --}}
        @if (isset($actions))
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endif

    </div>

    {{-- Optional Separator --}}
    @if ($separator)
        <flux:separator variant="subtle" class="mt-6" />
    @endif
</div>
