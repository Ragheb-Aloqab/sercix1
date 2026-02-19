<header
    class="sticky top-0 z-30 bg-slate-50/70 dark:bg-slate-950/60 backdrop-blur border-b border-slate-200/70 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center gap-2 sm:gap-3 min-w-0">
        <div class="flex-1 min-w-0">
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 truncate">@yield('subtitle', __('dashboard.subtitle_default'))</p>
            <h1 class="text-base sm:text-xl md:text-2xl font-black tracking-tight truncate">@yield('page_title', __('dashboard.page_title_default'))</h1>
        </div>

        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
            <livewire:dashboard.ui-preferences />

            <livewire:dashboard.global-search />


            <livewire:dashboard.notifications-bell />


        </div>
    </div>
</header>
