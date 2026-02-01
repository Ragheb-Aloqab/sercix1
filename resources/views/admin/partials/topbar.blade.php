<header
    class="sticky top-0 z-30 bg-slate-50/70 dark:bg-slate-950/60 backdrop-blur border-b border-slate-200/70 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
        <button id="openSidebar"
            class="lg:hidden inline-flex items-center justify-center w-11 h-11 rounded-2xl
      border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="flex-1">
            <p class="text-sm text-slate-500 dark:text-slate-400">@yield('subtitle', 'لوحة التحكم')</p>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight">@yield('page_title', 'نظرة عامة على التشغيل')</h1>
        </div>

        <div class="flex items-center gap-2">
            <livewire:dashboard.ui-preferences />

            <livewire:dashboard.global-search />


            <livewire:dashboard.notifications-bell />


        </div>
    </div>
</header>
