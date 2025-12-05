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
    {{ $slot }}

    @fluxScripts
</body>

</html>
