{{-- Inline script: must run before any content to prevent theme flash --}}
<script>
(function() {
    const storageKey = 'sercix_theme';
    const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    function getStored() {
        try { return localStorage.getItem(storageKey); } catch (e) { return null; }
    }

    let theme = @json($initialTheme ?? null);
    const preference = @json($initialPreference ?? null);
    if (!theme) {
        theme = getStored();
        if (!theme || theme === 'system') {
            theme = systemDark ? 'dark' : 'light';
        }
    } else if (theme === 'system') {
        theme = systemDark ? 'dark' : 'light';
    }
    const isDark = theme === 'dark';
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
    window.__sercix_theme = { effective: theme, preference: preference || 'system' };
})();
</script>
