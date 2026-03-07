/**
 * Theme initialization - runs before content to prevent flash.
 * Server passes initial theme via <meta name="sercix-initial-theme" content="dark|light|system|">
 * If <meta name="sercix-force-theme" content="light"> is present (white-label), always use light.
 */
(function() {
    var meta = document.querySelector('meta[name="sercix-initial-theme"]');
    var prefMeta = document.querySelector('meta[name="sercix-initial-preference"]');
    var forceMeta = document.querySelector('meta[name="sercix-force-theme"]');
    var theme = meta ? (meta.getAttribute('content') || '') : '';
    var preference = prefMeta ? (prefMeta.getAttribute('content') || 'system') : 'system';
    var forceLight = forceMeta && (forceMeta.getAttribute('content') || '').toLowerCase() === 'light';
    var storageKey = 'sercix_theme';
    var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    function getStored() {
        try { return localStorage.getItem(storageKey); } catch (e) { return null; }
    }

    if (forceLight) {
        theme = 'light';
    } else if (!theme) {
        theme = getStored();
        if (!theme || theme === 'system') {
            theme = systemDark ? 'dark' : 'light';
        }
    } else if (theme === 'system') {
        theme = systemDark ? 'dark' : 'light';
    }
    var isDark = forceLight ? false : (theme === 'dark');
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
    if (document.body) document.body.classList.toggle('dark', isDark);
    else document.addEventListener('DOMContentLoaded', function() { document.body.classList.toggle('dark', isDark); });
    window.__sercix_theme = { effective: forceLight ? 'light' : theme, preference: preference, forceLight: !!forceLight };
})();
