<div class="flex items-center gap-1 sm:gap-2">

    {{-- Language menu --}}
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button type="button"
            @click="open = ! open"
            class="min-w-[44px] min-h-[44px] p-2 sm:px-3 sm:py-2 rounded-xl text-sm font-semibold inline-flex items-center justify-center gap-2 {{ auth('company')->check() ? 'border border-slate-500/50 hover:bg-slate-700/50 text-slate-200' : 'border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
            title="{{ __('index.language') ?? 'Language' }}">
            <i class="fa-solid fa-globe"></i>
            <span class="hidden sm:inline">{{ app()->getLocale() === 'ar' ? __('dashboard.lang_ar') : __('dashboard.lang_en') }}</span>
            <i class="fa-solid fa-chevron-down text-xs opacity-70"></i>
        </button>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute end-0 mt-2 w-40 rounded-xl shadow-soft py-1 z-50 {{ auth('company')->check() ? 'bg-slate-800 border border-slate-600/50' : 'bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800' }}"
             style="display: none;">
            <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
               class="block px-4 py-3 min-h-[44px] flex items-center text-sm {{ auth('company')->check() ? (app()->getLocale() === 'ar' ? 'font-bold text-white hover:bg-slate-700/50' : 'text-slate-400 hover:bg-slate-700/50') : 'hover:bg-slate-100 dark:hover:bg-slate-800 ' . (app()->getLocale() === 'ar' ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-400') }}">
                {{ __('dashboard.lang_ar') }}
            </a>
            <a href="{{ route('set-locale', ['lang' => 'en']) }}"
               class="block px-4 py-3 min-h-[44px] flex items-center text-sm {{ auth('company')->check() ? (app()->getLocale() === 'en' ? 'font-bold text-white hover:bg-slate-700/50' : 'text-slate-400 hover:bg-slate-700/50') : 'hover:bg-slate-100 dark:hover:bg-slate-800 ' . (app()->getLocale() === 'en' ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-400') }}">
                {{ __('dashboard.lang_en') }}
            </a>
        </div>
    </div>

    {{-- Theme Toggle --}}
    <button
        wire:click="toggleTheme"
        class="min-w-[44px] min-h-[44px] p-2 sm:px-3 sm:py-2 rounded-xl flex items-center justify-center transition-colors duration-300
               {{ auth('company')->check() || auth('maintenance_center')->check()
                    ? ($theme === 'dark'
                        ? 'bg-slate-700 text-amber-400 hover:bg-slate-600 border border-slate-600/50'
                        : 'bg-slate-700 text-slate-300 hover:bg-slate-600 border border-slate-600/50')
                    : ($theme === 'dark'
                        ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-700/50 hover:bg-amber-200 dark:hover:bg-amber-900/50'
                        : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-300 dark:hover:bg-slate-600')
               }}
               text-sm font-semibold"
        title="{{ $theme === 'dark' ? __('dashboard.theme_light') : __('dashboard.theme_dark') }}"
        aria-label="{{ $theme === 'dark' ? __('dashboard.theme_light') : __('dashboard.theme_dark') }}">
        <i class="fa-solid {{ $theme === 'dark' ? 'fa-sun' : 'fa-moon' }} transition-transform duration-300"></i>
    </button>
</div>
