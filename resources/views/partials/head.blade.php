<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

{{-- 🔄 AUTO-SYNC SCRIPT: Centralized here --}}
<script>
    (function() {
        function syncDaisyUI() {
            const isDark = document.documentElement.classList.contains('dark');
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        }

        // 1. Run immediately
        syncDaisyUI();

        // 2. Run after navigation
        document.addEventListener('livewire:navigated', syncDaisyUI);

        // 3. Watch for class changes (e.g. Toggle button click)
        const observer = new MutationObserver(syncDaisyUI);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    })();
</script>
