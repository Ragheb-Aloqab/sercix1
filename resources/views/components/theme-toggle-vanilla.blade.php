<button type="button"
    id="theme-toggle-vanilla"
    class="min-w-[44px] min-h-[44px] p-2 rounded-xl flex items-center justify-center transition-colors duration-300
           border border-slate-300 dark:border-slate-600/50 bg-slate-200/80 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600/50 hover:text-slate-900 dark:hover:text-white"
    title="{{ __('dashboard.theme_light') }} / {{ __('dashboard.theme_dark') }}"
    aria-label="{{ __('dashboard.theme_light') }}">
    <i class="fa-solid fa-moon theme-icon-dark" aria-hidden="true"></i>
    <i class="fa-solid fa-sun theme-icon-light hidden" aria-hidden="true"></i>
</button>
<script>
(function() {
    var btn = document.getElementById('theme-toggle-vanilla');
    if (!btn) return;
    var iconDark = btn.querySelector('.theme-icon-dark');
    var iconLight = btn.querySelector('.theme-icon-light');
    var storageKey = 'sercix_theme';
    var url = '{{ route("theme-preference") }}';
    var csrf = '{{ csrf_token() }}';

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function updateIcon() {
        if (iconDark && iconLight) {
            iconDark.classList.toggle('hidden', isDark());
            iconLight.classList.toggle('hidden', !isDark());
        }
        btn.setAttribute('aria-label', isDark() ? '{{ __("dashboard.theme_light") }}' : '{{ __("dashboard.theme_dark") }}');
        btn.setAttribute('title', isDark() ? '{{ __("dashboard.theme_light") }}' : '{{ __("dashboard.theme_dark") }}');
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
</script>
