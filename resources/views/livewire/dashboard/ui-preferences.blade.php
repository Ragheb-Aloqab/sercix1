<div class="flex items-center gap-1 sm:gap-2">

    {{-- Language menu --}}
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button type="button"
            @click="open = ! open"
            class="p-2 sm:px-3 sm:py-2 rounded-xl border border-slate-200 dark:border-slate-800
                   hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold inline-flex items-center gap-2"
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
             class="absolute end-0 mt-2 w-40 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft py-1 z-50"
             style="display: none;">
            <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
               class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800 {{ app()->getLocale() === 'ar' ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-400' }}">
                {{ __('dashboard.lang_ar') }}
            </a>
            <a href="{{ route('set-locale', ['lang' => 'en']) }}"
               class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800 {{ app()->getLocale() === 'en' ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-400' }}">
                {{ __('dashboard.lang_en') }}
            </a>
        </div>
    </div>

    {{-- Toggle Theme --}}
    <button
        wire:click="toggleTheme"
        class="p-2 sm:px-3 sm:py-2 rounded-xl
               {{ $theme === 'dark'
                    ? 'bg-white text-slate-900'
                    : 'bg-slate-900 text-white'
               }}
               text-sm font-semibold"
        title="{{ $theme === 'dark' ? __('dashboard.theme_light') : __('dashboard.theme_dark') }}">
        <i class="fa-solid {{ $theme === 'dark' ? 'fa-sun' : 'fa-moon' }}"></i>
    </button>

</div>
