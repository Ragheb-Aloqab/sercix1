<header
    class="sticky top-0 z-20 backdrop-blur border-b {{ auth('company')->check() || auth('maintenance_center')->check() ? 'bg-slate-900/70 border-slate-600/50 topbar-app-mobile' : 'bg-slate-50/70 dark:bg-slate-950/60 border-slate-200/70 dark:border-slate-800' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center gap-2 sm:gap-3 min-w-0">
        {{-- Hamburger: mobile only --}}
        <button type="button"
                @click="sidebarOpen = true"
                class="lg:hidden flex items-center justify-center w-10 h-10 rounded-xl -ms-2 {{ auth('company')->check() || auth('maintenance_center')->check() ? 'text-slate-300 hover:bg-slate-700/50' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                aria-label="{{ __('dashboard.menu') }}">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <div class="flex-1 min-w-0">
            <p class="text-xs sm:text-sm truncate {{ auth('company')->check() || auth('maintenance_center')->check() ? 'text-slate-500' : 'text-slate-500 dark:text-slate-400' }}">@yield('subtitle', __('dashboard.subtitle_default'))</p>
            <h1 class="text-base sm:text-xl md:text-2xl font-black tracking-tight truncate {{ auth('company')->check() || auth('maintenance_center')->check() ? 'text-white' : '' }}">@yield('page_title', __('dashboard.page_title_default'))</h1>
        </div>

        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
            <livewire:dashboard.ui-preferences />

            <livewire:dashboard.global-search />


            <livewire:dashboard.notifications-bell />


        </div>
    </div>
</header>
