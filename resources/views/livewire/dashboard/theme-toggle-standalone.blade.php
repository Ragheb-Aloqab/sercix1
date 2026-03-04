<button
    wire:click="toggleTheme"
    type="button"
    class="min-w-[44px] min-h-[44px] p-2 rounded-xl flex items-center justify-center transition-colors duration-300
           {{ $theme === 'dark'
                ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-700/50 hover:bg-amber-200 dark:hover:bg-amber-900/50'
                : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-300 dark:hover:bg-slate-600'
           }}"
    title="{{ $theme === 'dark' ? __('dashboard.theme_light') : __('dashboard.theme_dark') }}"
    aria-label="{{ $theme === 'dark' ? __('dashboard.theme_light') : __('dashboard.theme_dark') }}">
    <i class="fa-solid {{ $theme === 'dark' ? 'fa-sun' : 'fa-moon' }}"></i>
</button>
