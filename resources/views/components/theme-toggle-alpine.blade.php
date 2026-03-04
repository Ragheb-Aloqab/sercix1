@php
    $themeService = app(\App\Services\ThemeService::class);
    $effective = $themeService->getEffectiveTheme();
@endphp
<button type="button"
    x-data="{ theme: @js($effective) }"
    x-init="
        $watch('theme', val => {
            document.documentElement.classList.toggle('dark', val === 'dark');
            document.documentElement.style.colorScheme = val === 'dark' ? 'dark' : 'light';
            try { localStorage.setItem('sercix_theme', val); } catch (e) {}
        })
    "
    @click="theme = theme === 'dark' ? 'light' : 'dark'"
    class="min-w-[44px] min-h-[44px] p-2 rounded-xl flex items-center justify-center transition-colors duration-300
           border border-slate-300 dark:border-slate-600/50
           {{ in_array(request()->route()->getName() ?? '', ['driver.*']) ? 'bg-slate-200/80 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600/50' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600' }}"
    :title="theme === 'dark' ? '{{ __('dashboard.theme_light') }}' : '{{ __('dashboard.theme_dark') }}'"
    :aria-label="theme === 'dark' ? '{{ __('dashboard.theme_light') }}' : '{{ __('dashboard.theme_dark') }}'">
    <i class="fa-solid" :class="theme === 'dark' ? 'fa-sun' : 'fa-moon'"></i>
</button>
