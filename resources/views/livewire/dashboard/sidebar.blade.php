<aside id="sidebar"
    class="flex flex-col w-full h-full min-h-dvh
    backdrop-blur shadow-soft lg:shadow-none
    border-e overflow-hidden transition-colors duration-300
    {{ (in_array($role, ['company', 'maintenance_center']) && ($wlBranding ?? false)) ? 'sidebar-wl bg-white border-e border-slate-200' : '' }}
    {{ (in_array($role, ['company', 'maintenance_center']) && !($wlBranding ?? false)) ? 'bg-white/95 dark:bg-servx-black border-slate-200/70 dark:border-slate-600/50' : '' }}
    {{ !in_array($role, ['company', 'maintenance_center']) ? 'bg-white/80 dark:bg-slate-900/70 border-slate-200/70 dark:border-slate-800' : '' }}">
    <div class="px-4 py-4 border-b transition-colors duration-300 {{ (in_array($role, ['company', 'maintenance_center']) && ($wlBranding ?? false)) ? 'border-slate-200' : (in_array($role, ['company', 'maintenance_center']) ? 'border-slate-200/70 dark:border-slate-600/50' : 'border-slate-200/70 dark:border-slate-800') }} flex items-center justify-end gap-2">
        {{-- Collapse toggle (lg+ only) — dir=ltr keeps chevron direction consistent in RTL --}}
        <button type="button"
            @click="toggleSidebarCollapse()"
            class="hidden lg:inline-flex items-center justify-center w-9 h-9 rounded-lg shrink-0 transition-colors duration-300 {{ (in_array($role, ['company', 'maintenance_center']) && ($wlBranding ?? false)) ? 'bg-slate-100 hover:bg-slate-200 text-slate-600' : (in_array($role, ['company', 'maintenance_center']) ? 'bg-sky-100 dark:bg-sky-900/40 hover:bg-sky-200 dark:hover:bg-sky-800/50 text-sky-700 dark:text-sky-300' : 'bg-slate-100 dark:bg-slate-800/50 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300') }}"
            :title="sidebarCollapsed ? '{{ __('admin_dashboard.sidebar_expand_tooltip') }}' : '{{ __('admin_dashboard.sidebar_toggle_tooltip') }}'"
            aria-label="{{ __('admin_dashboard.sidebar_toggle_tooltip') }}"
            dir="ltr">
            <i class="fa-solid fa-chevrons-left text-base transition-transform duration-300" :class="sidebarCollapsed ? 'fa-chevrons-right' : 'fa-chevrons-left'"></i>
        </button>

        <button type="button" id="closeSidebar"
            @click="$dispatch('close-sidebar')"
            class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl shrink-0 transition-colors duration-300
            {{ (in_array($role, ['company', 'maintenance_center']) && ($wlBranding ?? false)) ? 'text-slate-600 hover:bg-slate-100' : (in_array($role, ['company', 'maintenance_center']) ? 'border-slate-300 dark:border-slate-600/50 hover:bg-slate-200 dark:hover:bg-slate-700/50' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800') }}"
            aria-label="{{ __('dashboard.menu') }}">
            <i class="fa-solid fa-xmark {{ (in_array($role, ['company', 'maintenance_center']) && ($wlBranding ?? false)) ? 'text-slate-600' : (in_array($role, ['company', 'maintenance_center']) ? 'text-slate-700 dark:text-slate-300' : 'text-slate-600 dark:text-slate-400') }}"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto overscroll-contain">
        @include('livewire.dashboard.partials._sidebar-admin')

        {{-- Nav — shared items + role-specific links (click closes sidebar on mobile) --}}
        <nav class="px-3 pb-6" @click="$dispatch('close-sidebar')">
            <p class="sidebar-nav-section-header" style="margin-block-start:0;padding-block-start:0.5rem">{{ __('dashboard.menu') }}</p>

            {{-- Overview (role-specific href) — shown for admin, driver, maintenance_center. Company has its own nav with Dashboard. --}}
            @if (in_array($role, ['admin', 'super_admin', 'driver', 'maintenance_center']))
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

            {{-- Maintenance Center menu — only when $role === 'maintenance_center' --}}
            @include('livewire.dashboard.partials._sidebar-nav-maintenance-center')

            {{-- Guest / Driver: minimal nav (no role-specific links). Driver uses layouts.driver; this is fallback. --}}
            @if (in_array($role, ['guest', 'driver']))
                <a href="{{ route('login') }}" class="sidebar-nav-item" title="{{ __('login.sign_in') }}">
                    <span class="sidebar-nav-icon"><i class="fa-solid fa-right-to-bracket"></i></span>
                    <div class="sidebar-nav-text">
                        <p class="sidebar-nav-label">{{ __('login.sign_in') }}</p>
                        <p class="sidebar-nav-sublabel">{{ __('dashboard.main_page_desc') }}</p>
                    </div>
                </a>
            @endif
        </nav>
    </div>

    {{-- Footer (Fixed Bottom) — logout / user info — hidden for company/maintenance (moved to topbar) --}}
    @if (!in_array($role, ['company', 'maintenance_center']))
    <div class="p-4 border-t transition-colors duration-300 border-slate-200/70 dark:border-slate-800">
        <div class="flex items-center gap-3 sidebar-footer-inner">
            <div
                class="w-10 h-10 rounded-2xl flex items-center justify-center font-black {{ $role === 'company' ? 'bg-[#3B82F6] text-white' : 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' }}">
                {{ $avatarLetter }}
            </div>

            <div class="flex-1 min-w-0 sidebar-footer-text">
                <p class="font-bold leading-5 truncate text-sm text-slate-900 dark:text-servx-silver-light">{{ $displayName }}</p>
                @if ($displayEmail)
                    <p class="text-xs truncate text-slate-500 dark:text-servx-silver">{{ $displayEmail }}</p>
                @endif
            </div>

            {{-- Logout: company, maintenance_center, driver, web --}}
            @if ($role === 'maintenance_center')
                <form method="POST" action="{{ route('maintenance-center.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-200 dark:hover:bg-slate-700/50 text-slate-600 dark:text-servx-silver text-sm font-semibold transition-colors duration-300 sidebar-logout-btn"
                        title="{{ __('dashboard.logout') }}">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="sidebar-logout-text">{{ __('dashboard.logout') }}</span>
                    </button>
                </form>
            @elseif($role === 'company')
                <form method="POST" action="{{ route('company.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-200 dark:hover:bg-slate-700/50 text-slate-600 dark:text-servx-silver text-sm font-semibold transition-colors duration-300 sidebar-logout-btn"
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
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold sidebar-logout-btn"
                    title="{{ __('login.sign_in') }}">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span class="sidebar-logout-text">{{ __('login.sign_in') }}</span>
                </a>
            @endif
        </div>
    </div>
    @endif
</aside>
