/**
 * Theme toggle button - reads config from data attributes to avoid inline scripts.
 * External file avoids ModSecurity/CSP blocking on Hostinger.
 */
(function() {
    var btn = document.getElementById('theme-toggle-vanilla');
    if (!btn) return;

    var iconDark = btn.querySelector('.theme-icon-dark');
    var iconLight = btn.querySelector('.theme-icon-light');
    var storageKey = 'sercix_theme';
    var url = btn.getAttribute('data-theme-url') || '/theme-preference';
    var csrf = btn.getAttribute('data-csrf') || '';
    var labelDark = btn.getAttribute('data-label-dark') || 'Dark';
    var labelLight = btn.getAttribute('data-label-light') || 'Light';

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function updateIcon() {
        if (iconDark && iconLight) {
            iconDark.classList.toggle('hidden', isDark());
            iconLight.classList.toggle('hidden', !isDark());
        }
        btn.setAttribute('aria-label', isDark() ? labelLight : labelDark);
        btn.setAttribute('title', isDark() ? labelLight : labelDark);
    }

    function setTheme(dark) {
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        try { localStorage.setItem(storageKey, dark ? 'dark' : 'light'); } catch (e) {}
        updateIcon();
        var theme = dark ? 'dark' : 'light';
        var fd = new FormData();
        fd.append('theme', theme);
        fd.append('_token', csrf);
        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        }).catch(function() {});
    }

    btn.addEventListener('click', function() {
        setTheme(!isDark());
    });

    updateIcon();
})();
