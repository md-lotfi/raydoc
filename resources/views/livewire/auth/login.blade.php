<x-layouts.auth>
    <div class="flex flex-col gap-6">

        {{-- Header --}}
        <div class="flex flex-col gap-2 text-center">
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                {{ __('Welcome back') }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Enter your credentials to access your account') }}
            </p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input name="email" :label="__('Email address')" :value="old('email')" type="email" required
                autofocus autocomplete="email" placeholder="doctor@raydoc.com" icon="envelope" />

            <div class="relative">
                <flux:input name="password" :label="__('Password')" type="password" required
                    autocomplete="current-password" placeholder="••••••••" viewable icon="lock-closed" />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-xs end-0 text-zinc-500 hover:text-primary"
                        :href="route('password.request')" wire:navigate>
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Keep me logged in')" :checked="old('remember')" />

            <div class="flex flex-col gap-4">
                <flux:button variant="primary" type="submit" class="w-full shadow-md"
                    icon="arrow-right-end-on-rectangle">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        {{-- Divider --}}
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t border-zinc-200 dark:border-zinc-700"></span>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-zinc-50 dark:bg-zinc-900 px-2 text-zinc-500">
                    {{ __('Or continue with') }}
                </span>
            </div>
        </div>

        {{-- Social Login Placeholders --}}
        <div class="grid grid-cols-2 gap-3">
            <flux:button variant="outline" class="w-full">
                <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4" />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853" />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                        fill="#FBBC05" />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        fill="#EA4335" />
                </svg>
                Google
            </flux:button>
            <flux:button variant="outline" class="w-full">
                <svg class="mr-2 h-4 w-4 fill-current" viewBox="0 0 24 24">
                    <path
                        d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.419-1.305.762-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
                </svg>
                GitHub
            </flux:button>
        </div>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate class="font-bold text-primary hover:underline">
                    {{ __('Sign up') }}
                </flux:link>
            </div>
        @endif
    </div>
</x-layouts.auth>
