<aside id="sidebar"
    class="flex flex-col w-full h-full min-h-dvh
    backdrop-blur shadow-soft lg:shadow-none
    border-e overflow-hidden
    {{ $role === 'company' ? 'bg-servx-black border-slate-600/50' : 'bg-white/80 dark:bg-slate-900/70 border-slate-200/70 dark:border-slate-800' }}">
    <div class="px-4 py-4 border-b {{ $role === 'company' ? 'border-slate-600/50' : 'border-slate-200/70 dark:border-slate-800' }} flex items-center justify-between gap-2">
        <a href="{{ route('index') }}" class="flex items-center gap-3 hover:opacity-90 transition-opacity min-w-0 flex-1">
            <img src="{{ $siteLogoUrl ?? asset('images/serv.x logo.png') }}" alt="" width="40" height="40" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
            <div class="min-w-0 sidebar-brand-text transition-opacity duration-300">
                <p class="font-extrabold leading-5 truncate text-sm {{ $role === 'company' ? 'text-white' : 'text-slate-900 dark:text-white' }}">
                    {{ $siteName ?? 'Servx Motors' }}
                    @if (in_array($role, ['admin', 'super_admin']))
                        {{ __('dashboard.admin') }}
                    @elseif($role === 'company')
                        {{ __('dashboard.company') }}
                    @elseif($role === 'driver')
                        {{ __('dashboard.driver') }}
                    @else
                        {{ __('dashboard.guest') }}
                    @endif
                </p>
                <p class="text-xs {{ $role === 'company' ? 'text-slate-500' : 'text-slate-500 dark:text-slate-400' }}">{{ __('dashboard.dashboard_v1') }}</p>
            </div>
        </a>

        {{-- Collapse toggle (lg+ only) — dir=ltr keeps chevron direction consistent in RTL --}}
        <button type="button"
            @click="toggleSidebarCollapse()"
            class="hidden lg:inline-flex items-center justify-center w-9 h-9 rounded-lg shrink-0 transition-colors duration-300 {{ $role === 'company' ? 'hover:bg-slate-700/50 text-slate-400' : 'hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400' }}"
            :title="sidebarCollapsed ? '{{ __('admin_dashboard.sidebar_expand_tooltip') }}' : '{{ __('admin_dashboard.sidebar_toggle_tooltip') }}'"
            aria-label="{{ __('admin_dashboard.sidebar_toggle_tooltip') }}"
            dir="ltr">
            <i class="fa-solid fa-chevrons-left text-base transition-transform duration-300 {{ $role === 'company' ? 'text-slate-400' : 'text-slate-500 dark:text-slate-400' }}" :class="sidebarCollapsed ? 'fa-chevrons-right' : 'fa-chevrons-left'"></i>
        </button>

        <button type="button" id="closeSidebar"
            @click="$dispatch('close-sidebar')"
            class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl shrink-0
            {{ $role === 'company' ? 'border-slate-600/50 hover:bg-slate-700/50 text-slate-300' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
            aria-label="{{ __('dashboard.menu') }}">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto overscroll-contain">
        @include('livewire.dashboard.partials._sidebar-admin')

        {{-- Nav — shared items + role-specific links (click closes sidebar on mobile) --}}
        <nav class="px-3 pb-6" @click="$dispatch('close-sidebar')">
            <p class="sidebar-nav-section-header" style="margin-block-start:0;padding-block-start:0.5rem">{{ __('dashboard.menu') }}</p>

            {{-- Overview (role-specific href) — shown for admin, company, driver --}}
            @if (in_array($role, ['admin', 'super_admin', 'company', 'driver']))
            <a href="{{ $overviewHref }}"
               class="sidebar-nav-item {{ $overviewActive ? 'sidebar-nav-item--active' : '' }}"
               title="{{ __('dashboard.overview') }}">
                <span class="sidebar-nav-icon"><i class="fa-solid fa-chart-line"></i></span>
                <div class="sidebar-nav-text">
                    <p class="sidebar-nav-label">{{ __('dashboard.overview') }}</p>
                    <p class="sidebar-nav-sublabel">{{ __('dashboard.overview_desc') }}</p>
                </div>
            </a>
            @endif

            {{-- Admin menu — only when $role === 'admin' --}}
            @include('livewire.dashboard.partials._sidebar-nav-admin')

            {{-- Company menu — only when $role === 'company' --}}
            @include('livewire.dashboard.partials._sidebar-nav-company')

            {{-- Guest / Driver: minimal nav (no role-specific links). Driver uses layouts.driver; this is fallback. --}}
            @if ($role === 'guest' || $role === 'driver')
                <a href="{{ route('sign-in.index') }}" class="sidebar-nav-item" title="{{ __('login.sign_in') }}">
                    <span class="sidebar-nav-icon"><i class="fa-solid fa-right-to-bracket"></i></span>
                    <div class="sidebar-nav-text">
                        <p class="sidebar-nav-label">{{ __('login.sign_in') }}</p>
                        <p class="sidebar-nav-sublabel">{{ __('dashboard.main_page_desc') }}</p>
                    </div>
                </a>
            @endif
        </nav>
    </div>

    {{-- Footer (Fixed Bottom) — logout / user info --}}
    <div class="p-4 border-t {{ $role === 'company' ? 'border-slate-600/50' : 'border-slate-200/70 dark:border-slate-800' }}">
        <div class="flex items-center gap-3 sidebar-footer-inner">
            <div
                class="w-10 h-10 rounded-2xl flex items-center justify-center font-black {{ $role === 'company' ? 'bg-[#3B82F6] text-white' : 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' }}">
                {{ $avatarLetter }}
            </div>

            <div class="flex-1 min-w-0 sidebar-footer-text">
                <p class="font-bold leading-5 truncate text-sm {{ $role === 'company' ? 'text-servx-silver-light' : '' }}">{{ $displayName }}</p>
                @if ($displayEmail)
                    <p class="text-xs truncate {{ $role === 'company' ? 'text-servx-silver' : 'text-slate-500 dark:text-slate-400' }}">{{ $displayEmail }}</p>
                @endif
            </div>

            {{-- Logout: company uses company.logout, driver uses driver.logout, web uses logout --}}
            @if ($role === 'company')
                <form method="POST" action="{{ route('company.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 text-servx-silver text-sm font-semibold transition-colors sidebar-logout-btn"
                        title="{{ __('dashboard.logout') }}">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="sidebar-logout-text">{{ __('dashboard.logout') }}</span>
                    </button>
                </form>
            @elseif($role === 'driver')
                <form method="POST" action="{{ route('driver.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold sidebar-logout-btn"
                        title="{{ __('dashboard.logout') }}">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="sidebar-logout-text">{{ __('dashboard.logout') }}</span>
                    </button>
                </form>
            @elseif(in_array($role, ['admin', 'super_admin']))
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold sidebar-logout-btn"
                        title="{{ __('dashboard.logout') }}">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="sidebar-logout-text">{{ __('dashboard.logout') }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('sign-in.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold sidebar-logout-btn"
                    title="{{ __('login.sign_in') }}">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span class="sidebar-logout-text">{{ __('login.sign_in') }}</span>
                </a>
            @endif
        </div>
    </div>
</aside>
