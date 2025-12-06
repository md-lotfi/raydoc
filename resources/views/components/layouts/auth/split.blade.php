<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 antialiased">

    {{-- 🌍 GLOBAL TOOLS (Top Right) --}}
    <div class="absolute top-4 end-4 z-50 flex items-center gap-2">

        {{-- Theme Toggle --}}
        <flux:button icon="sun" x-on:click="$flux.appearance = 'light'" x-show="$flux.appearance === 'dark'"
            variant="ghost" class="!p-2" />
        <flux:button icon="moon" x-on:click="$flux.appearance = 'dark'" x-show="$flux.appearance !== 'dark'"
            variant="ghost" class="!p-2" />

        {{-- Language Switcher --}}
        <flux:dropdown align="end">
            <flux:button variant="ghost" class="uppercase font-bold text-xs !p-2">
                {{ app()->getLocale() }}
            </flux:button>

            <flux:menu>
                <flux:menu.item href="{{ route('switch-language', 'en') }}"
                    :current="app()->getLocale() === 'en'">English</flux:menu.item>
                <flux:menu.item href="{{ route('switch-language', 'fr') }}"
                    :current="app()->getLocale() === 'fr'">Français</flux:menu.item>
                <flux:menu.item href="{{ route('switch-language', 'ar') }}"
                    :current="app()->getLocale() === 'ar'">العربية</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </div>

    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">

        {{-- 🎨 LEFT COLUMN: Brand & Visuals --}}
        <div
            class="relative hidden h-full flex-col bg-zinc-900 p-10 text-white lg:flex dark:border-r dark:border-zinc-800 overflow-hidden">

            {{-- ✅ UPDATED: Animated Gradient Background --}}
            {{-- 1. Base Dark Layer --}}
            <div class="absolute inset-0 bg-zinc-900"></div>

            {{-- 2. Primary Color Glow (Bottom Right) --}}
            {{-- Added 'animate-blob' --}}
            <div
                class="absolute bottom-0 right-0 w-[800px] h-[800px] bg-primary/20 rounded-full blur-3xl translate-y-1/2 translate-x-1/3 pointer-events-none animate-blob">
            </div>

            {{-- 3. Secondary Subtle Glow (Top Left) --}}
            {{-- Added 'animate-blob' and 'animation-delay-2000' --}}
            <div
                class="absolute top-0 left-0 w-[600px] h-[600px] bg-zinc-800/50 rounded-full blur-3xl -translate-y-1/4 -translate-x-1/4 pointer-events-none animate-blob animation-delay-2000">
            </div>

            {{-- 4. Mesh Texture (Optional) --}}
            <div class="absolute inset-0 opacity-[0.03]"
                style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 32px 32px;">
            </div>

            {{-- Logo --}}
            <div class="relative z-20 flex items-center gap-2 text-lg font-medium">
                <x-app-logo class="h-8 w-auto text-white" />
            </div>

            {{-- Inspiring Quote --}}
            @php
                [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
            @endphp

            <div class="relative z-20 mt-auto">
                <blockquote class="space-y-2">
                    <p class="text-lg font-medium leading-relaxed text-zinc-100">
                        &ldquo;{{ trim($message) }}&rdquo;
                    </p>
                    <footer class="text-sm text-zinc-400">— {{ trim($author) }}</footer>
                </blockquote>
            </div>
        </div>

        {{-- 📝 RIGHT COLUMN: Form --}}
        <div class="w-full lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[400px]">

                {{-- Mobile Logo --}}
                <div class="flex flex-col items-center gap-2 font-medium lg:hidden mb-4">
                    <x-app-logo class="h-10 w-auto text-primary" />
                    <span class="sr-only">{{ config('app.name') }}</span>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>

    @fluxScripts
</body>

</html>
