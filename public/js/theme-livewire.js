/**
 * Livewire theme + dir change listeners - external file to avoid ModSecurity block on Hostinger.
 */
document.addEventListener('livewire:init', function() {
    Livewire.on('ui-theme-changed', function(event) {
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
