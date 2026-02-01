<div class="flex items-center gap-2">

    {{-- Toggle Direction --}}
    <button
        wire:click="toggleDir"
        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800
               hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
        <i class="fa-solid fa-right-left me-2"></i>
        {{ $dir === 'rtl' ? 'RTL' : 'LTR' }} ⇄ {{ $dir === 'rtl' ? 'LTR' : 'RTL' }}
    </button>

    {{-- Toggle Theme --}}
    <button
        wire:click="toggleTheme"
        class="px-3 py-2 rounded-xl
               {{ $theme === 'dark'
                    ? 'bg-white text-slate-900'
                    : 'bg-slate-900 text-white'
               }}
               text-sm font-semibold">
        <i class="fa-solid {{ $theme === 'dark' ? 'fa-sun' : 'fa-moon' }}"></i>
    </button>

</div>
