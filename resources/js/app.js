// 1. Check immediately on load
if (document.documentElement.classList.contains('dark')) {
    document.documentElement.setAttribute('data-theme', 'dark');
} else {
    document.documentElement.setAttribute('data-theme', 'light');
}

// 2. Watch for changes (When you click the toggle button)
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.attributeName === 'class') {
            const isDark = document.documentElement.classList.contains('dark');
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        }
    });
});

observer.observe(document.documentElement, { attributes: true });