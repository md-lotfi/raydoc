<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Raydoc') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    {{-- Scripts & Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Flux Theme Manager --}}
    @fluxAppearance

    {{-- Auto-Sync Theme Script (Matches Sidebar Logic) --}}
    <script>
        (function() {
            function syncDaisyUI() {
                const isDark = document.documentElement.classList.contains('dark');
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            }
            syncDaisyUI();
            const observer = new MutationObserver(syncDaisyUI);
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        })();
    </script>
</head>

<body
    class="min-h-screen bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 antialiased selection:bg-primary selection:text-white">

    {{-- 🟢 HEADER --}}
    <header
        class="fixed top-0 w-full z-50 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                {{-- Logo --}}
                <div class="flex-shrink-0 flex items-center gap-2">
                    <x-app-logo class="h-8 w-auto text-primary" />

                </div>

                {{-- Navigation --}}
                <div class="flex items-center gap-4">

                    {{-- Language Switcher (Simple Dropdown) --}}
                    <div class="relative group">
                        <button class="flex items-center gap-1 text-sm font-medium hover:text-primary transition">
                            <x-mary-icon name="o-language" class="w-5 h-5" />
                            <span class="uppercase">{{ app()->getLocale() }}</span>
                        </button>
                        <div
                            class="absolute right-0 mt-2 w-32 bg-white dark:bg-zinc-800 rounded-lg shadow-xl border border-zinc-100 dark:border-zinc-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform origin-top-right">
                            <div class="py-1">
                                <a href="{{ route('switch-language', 'en') }}"
                                    class="block px-4 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ app()->getLocale() == 'en' ? 'font-bold text-primary' : '' }}">English</a>
                                <a href="{{ route('switch-language', 'fr') }}"
                                    class="block px-4 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ app()->getLocale() == 'fr' ? 'font-bold text-primary' : '' }}">Français</a>
                                <a href="{{ route('switch-language', 'ar') }}"
                                    class="block px-4 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ app()->getLocale() == 'ar' ? 'font-bold text-primary' : '' }}">العربية</a>
                            </div>
                        </div>
                    </div>

                    {{-- Auth Buttons --}}
                    @if (Route::has('login'))
                        <div class="flex gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}">
                                    <x-mary-button label="{{ __('Dashboard') }}" class="btn-primary btn-sm"
                                        icon="o-home" />
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="hidden sm:block">
                                    <x-mary-button label="{{ __('Log in') }}" class="btn-ghost btn-sm" />
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}">
                                        <x-mary-button label="{{ __('Get Started') }}" class="btn-primary btn-sm" />
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>

    {{-- 🚀 HERO SECTION --}}
    <main class="pt-32 pb-16 sm:pt-40 sm:pb-24 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">

            {{-- Background decorative blobs --}}
            <div
                class="absolute -top-24 -left-20 w-72 h-72 bg-primary/20 rounded-full blur-3xl opacity-50 pointer-events-none">
            </div>
            <div
                class="absolute top-40 right-0 w-96 h-96 bg-secondary/20 rounded-full blur-3xl opacity-50 pointer-events-none">
            </div>

            <div class="text-center relative z-10">
                <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-zinc-900 dark:text-white mb-6">
                    {{ __('Modern Practice Management') }} <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">
                        {{ __('For Healthcare Professionals') }}
                    </span>
                </h1>
                <p class="mt-4 text-lg sm:text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    {{ __('Streamline patient records, scheduling, and billing in one secure platform. Designed for doctors who care about efficiency.') }}
                </p>
                <div class="mt-8 flex justify-center gap-4">
                    <a href="{{ route('register') }}">
                        <x-mary-button label="{{ __('Start Free Trial') }}"
                            class="btn-primary btn-lg shadow-lg hover:scale-105 transition-transform"
                            icon="o-rocket-launch" />
                    </a>
                    <a href="#features">
                        <x-mary-button label="{{ __('Learn More') }}" class="btn-outline btn-lg"
                            icon="o-information-circle" />
                    </a>
                </div>
            </div>
        </div>
    </main>

    {{-- ✨ FEATURES GRID --}}
    <section id="features" class="py-20 bg-white dark:bg-zinc-900/50 border-t border-zinc-200 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">{{ __('Everything You Need') }}</h2>
                <p class="text-zinc-500">{{ __('A complete suite of tools to manage your clinic effectively.') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div
                    class="p-6 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4 text-primary">
                        <x-mary-icon name="o-user-group" class="w-6 h-6" />
                    </div>
                    <h3 class="text-xl font-bold mb-2">{{ __('Patient Directory') }}</h3>
                    <p class="text-zinc-500 dark:text-zinc-400">
                        {{ __('Manage comprehensive patient profiles, medical history, and documents in one secure place.') }}
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div
                    class="p-6 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 hover:shadow-xl transition-shadow duration-300">
                    <div
                        class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mb-4 text-secondary">
                        <x-mary-icon name="o-calendar-days" class="w-6 h-6" />
                    </div>
                    <h3 class="text-xl font-bold mb-2">{{ __('Smart Scheduling') }}</h3>
                    <p class="text-zinc-500 dark:text-zinc-400">
                        {{ __('Effortless appointment booking with a visual drag-and-drop waiting room system.') }}
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div
                    class="p-6 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center mb-4 text-warning">
                        <x-mary-icon name="o-banknotes" class="w-6 h-6" />
                    </div>
                    <h3 class="text-xl font-bold mb-2">{{ __('Billing & Invoices') }}</h3>
                    <p class="text-zinc-500 dark:text-zinc-400">
                        {{ __('Generate professional invoices, track payments, and manage financial reports seamlessly.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 🦶 FOOTER --}}
    <footer class="py-12 border-t border-zinc-200 dark:border-zinc-800">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <x-app-logo class="h-6 w-auto text-zinc-400" />
                <span class="text-zinc-500 font-semibold">&copy; {{ date('Y') }} {{ config('app.name') }}.
                    {{ __('All rights reserved.') }}</span>
            </div>
            <div class="flex gap-6 text-sm text-zinc-500">
                <a href="#" class="hover:text-primary">{{ __('Privacy Policy') }}</a>
                <a href="#" class="hover:text-primary">{{ __('Terms of Service') }}</a>
                <a href="#" class="hover:text-primary">{{ __('Support') }}</a>
            </div>
        </div>
    </footer>

    @fluxScripts
</body>

</html>
