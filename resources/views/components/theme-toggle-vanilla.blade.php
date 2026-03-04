<button type="button"
    id="theme-toggle-vanilla"
    class="min-w-[44px] min-h-[44px] p-2 rounded-xl flex items-center justify-center transition-colors duration-300
           border border-slate-300 dark:border-slate-600/50 bg-slate-200/80 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600/50 hover:text-slate-900 dark:hover:text-white"
    title="{{ __('dashboard.theme_light') }} / {{ __('dashboard.theme_dark') }}"
    aria-label="{{ __('dashboard.theme_light') }}"
    data-theme-url="{{ route('theme-preference') }}"
    data-csrf="{{ csrf_token() }}"
    data-label-dark="{{ __('dashboard.theme_dark') }}"
    data-label-light="{{ __('dashboard.theme_light') }}">
    <i class="fa-solid fa-moon theme-icon-dark" aria-hidden="true"></i>
    <i class="fa-solid fa-sun theme-icon-light hidden" aria-hidden="true"></i>
</button>
<script src="{{ asset('js/theme-toggle.js') }}"></script>
