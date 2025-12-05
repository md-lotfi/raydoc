<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    @include('partials.head')
    @fluxAppearance
    <script>
        (function() {
            function syncDaisyUI() {
                const isDark = document.documentElement.classList.contains('dark');
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            }
            syncDaisyUI();
            document.addEventListener('livewire:navigated', syncDaisyUI);
            const observer = new MutationObserver(syncDaisyUI);
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        })();
    </script>
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible
        class="border-e border-slate-800 bg-slate-900 text-white dark:bg-zinc-900 dark:border-zinc-700">

        {{-- 🟢 HEADER: Logo & Toggle --}}
        <flux:sidebar.header>
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            {{-- Use flux:brand for automatic collapsing behavior --}}
            {{-- Note: We use a wrapper to render your dynamic logo component --}}
            <flux:brand href="{{ route('dashboard') }}" wire:navigate>
                <x-slot:logo>
                    <x-app-logo class="h-8 w-auto text-primary" />
                </x-slot:logo>
            </flux:brand>

            <flux:spacer />

            <flux:sidebar.collapse />
        </flux:sidebar.header>

        {{-- 🔵 NAVIGATION (Switched to flux:sidebar.nav) --}}
        <flux:sidebar.nav>

            {{-- Overview --}}
            <flux:sidebar.group heading="{{ __('Daily Operations') }}">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="calendar" href="{{ route('sessions.schedule') }}"
                    :current="request()->routeIs('sessions.schedule')" wire:navigate>
                    {{ __('Schedule') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="clock" href="{{ route('sessions.waiting-room') }}"
                    :current="request()->routeIs('sessions.waiting-room')" wire:navigate>
                    {{ __('Waiting Room') }}
                </flux:sidebar.item>

                @hasanyrole('super-admin|doctor')
                    <flux:sidebar.item icon="chart-bar" href="{{ route('dashboard.report') }}"
                        :current="request()->routeIs('dashboard.report')" wire:navigate>
                        {{ __('Analytics') }}
                    </flux:sidebar.item>
                @endhasanyrole
            </flux:sidebar.group>

            {{-- Clinical --}}
            <flux:sidebar.group heading="{{ __('Clinical') }}">
                {{-- Note: Sidebar sub-groups (expandable) work best when sidebar is expanded. 
                     Flux handles hover/popovers for collapsed state automatically. --}}

                <flux:sidebar.item icon="user-group" href="{{ route('patient.list') }}"
                    :current="request()->routeIs('patient.*')">
                    {{ __('Patients') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="clipboard-document-list" href="{{ route('sessions.list') }}"
                    :current="request()->routeIs('sessions.*')">
                    {{ __('Sessions') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="heart" href="{{ route('clinical.diagnoses.list') }}"
                    :current="request()->routeIs('clinical.diagnoses.*')">
                    {{ __('Diagnoses') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            {{-- Practice --}}
            @role('super-admin')
                <flux:sidebar.group heading="{{ __('Practice') }}">
                    <flux:sidebar.item icon="users" href="{{ route('doctors.list') }}"
                        :current="request()->routeIs('doctors.*')">{{ __('Doctors') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="user" href="{{ route('assistants.list') }}"
                        :current="request()->routeIs('assistants.*')">{{ __('Assistants') }}</flux:sidebar.item>
                </flux:sidebar.group>
            @endrole

            @hasanyrole('super-admin|assistant')
                <flux:sidebar.group heading="{{ __('Financial') }}">
                    <flux:sidebar.item icon="banknotes" href="{{ route('invoice.list') }}"
                        :current="request()->routeIs('invoice.*')">{{ __('Invoices') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="tag" href="{{ route('billing.codes.list') }}"
                        :current="request()->routeIs('billing.codes.*')">{{ __('Billing Codes') }}</flux:sidebar.item>
                </flux:sidebar.group>
            @endhasanyrole

            {{-- System --}}
            @role('super-admin')
                <flux:sidebar.group heading="{{ __('System') }}">
                    <flux:sidebar.item icon="cog-6-tooth" href="{{ route('settings.app.edit') }}"
                        :current="request()->routeIs('settings.*')
                                                                        && !request()->routeIs('settings.currency.list')
                                                                        && !request()->routeIs('settings.currency.edit')
                                                                        && !request()->routeIs('settings.currency.create')">
                        {{ __('Settings') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="currency-dollar" href="{{ route('settings.currency.list') }}"
                        :current="request()->routeIs('settings.currency.*')">
                        {{ __('Currencies') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endrole

        </flux:sidebar.nav>

        {{-- 🟣 FOOTER: User Menu --}}
        <flux:sidebar.footer>
            <flux:dropdown position="bottom" align="start">
                <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down" />

                <flux:menu class="w-[240px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('My Profile') }}
                    </flux:menu.item>
                    <flux:menu.separator />

                    {{-- Preferences --}}
                    <flux:menu.radio.group heading="{{ __('Preferences') }}">
                        <div class="flex justify-evenly px-2 py-2 mb-1 bg-zinc-100 dark:bg-zinc-700 rounded-md mx-2">
                            <a href="{{ route('switch-language', 'en') }}"
                                class="text-xs font-bold px-2 py-1 rounded {{ app()->getLocale() == 'en' ? 'bg-white dark:bg-zinc-600 shadow-sm text-primary' : 'text-zinc-500' }}">EN</a>
                            <a href="{{ route('switch-language', 'fr') }}"
                                class="text-xs font-bold px-2 py-1 rounded {{ app()->getLocale() == 'fr' ? 'bg-white dark:bg-zinc-600 shadow-sm text-primary' : 'text-zinc-500' }}">FR</a>
                            <a href="{{ route('switch-language', 'ar') }}"
                                class="text-xs font-bold px-2 py-1 rounded {{ app()->getLocale() == 'ar' ? 'bg-white dark:bg-zinc-600 shadow-sm text-primary' : 'text-zinc-500' }}">AR</a>
                        </div>
                        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'"
                            x-show="$flux.appearance === 'dark'">{{ __('Switch to Light') }}</flux:menu.item>
                        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'"
                            x-show="$flux.appearance !== 'dark'">{{ __('Switch to Dark') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar.footer>
    </flux:sidebar>

    {{-- Mobile User Menu (Unchanged) --}}
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
            <flux:menu>
                <flux:menu.radio.group heading="{{ __('Preferences') }}">
                    <div class="flex gap-4 px-4 py-2">
                        <a href="{{ route('switch-language', 'en') }}"
                            class="{{ app()->getLocale() == 'en' ? 'font-bold text-primary' : '' }}">EN</a>
                        <a href="{{ route('switch-language', 'fr') }}"
                            class="{{ app()->getLocale() == 'fr' ? 'font-bold text-primary' : '' }}">FR</a>
                        <a href="{{ route('switch-language', 'ar') }}"
                            class="{{ app()->getLocale() == 'ar' ? 'font-bold text-primary' : '' }}">AR</a>
                    </div>
                    <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'"
                        x-show="$flux.appearance === 'dark'">{{ __('Light Mode') }}</flux:menu.item>
                    <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'"
                        x-show="$flux.appearance !== 'dark'">{{ __('Dark Mode') }}</flux:menu.item>
                </flux:menu.radio.group>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
