/**
 * Livewire theme + dir change listeners - external file to avoid ModSecurity block on Hostinger.
 * When body has data-wl-branding (white-label), ignore theme changes so we stay light-only.
 */
document.addEventListener('livewire:init', function() {
    Livewire.on('ui-theme-changed', function(event) {
        if (document.body && document.body.getAttribute('data-wl-branding') !== null) {
            return;
        }
        var theme = event.theme;
        var isDark = theme === 'dark';
        document.documentElement.classList.toggle('dark', isDark);
        document.body?.classList.toggle('dark', isDark);
        document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
        try { localStorage.setItem('sercix_theme', theme); } catch (e) {}
    });
    Livewire.on('ui-dir-changed', function(event) {
        var dir = event.dir;
        if (dir) {
            document.documentElement.setAttribute('dir', dir);
            document.documentElement.setAttribute('lang', dir === 'rtl' ? 'ar' : 'en');
        }
    });
});
