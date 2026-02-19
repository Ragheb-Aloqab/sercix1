
<aside id="sidebar"
    class="hidden lg:flex flex-col fixed inset-y-0 z-50 w-80 max-w-[90vw]
    left-0
    bg-white/80 dark:bg-slate-900/70 backdrop-blur border-slate-200/70 dark:border-slate-800
    border-e shadow-soft lg:shadow-none
    h-dvh overflow-hidden">
    <div class="px-6 py-6 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
        <a href="{{ route('index') }}" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
            @if(($siteLogoUrl ?? null))
                <img src="{{ $siteLogoUrl }}" alt="" class="w-11 h-11 rounded-2xl object-cover flex-shrink-0">
            @else
                <div
                    class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center text-white font-black flex-shrink-0">
                    S
                </div>
            @endif
            <div class="min-w-0">
                <p class="font-extrabold leading-5 truncate">
                    {{ $siteName ?? 'SERV.X' }}
                    @if ($role === 'admin')
                        {{ __('dashboard.admin') }}
                    @elseif($role === 'technician')
                        {{ __('dashboard.technician') }}
                    @elseif($role === 'company')
                        {{ __('dashboard.company') }}
                    @elseif($role === 'driver')
                        {{ __('dashboard.driver') }}
                    @else
                        {{ __('dashboard.guest') }}
                    @endif
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.dashboard_v1') }}</p>
            </div>
        </a>

        <button id="closeSidebar"
            class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl
            border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto overscroll-contain">
        @include('livewire.dashboard.partials._sidebar-admin')

        {{-- Nav — shared items + role-specific links --}}
        <nav class="px-4 pb-6">
            <p class="px-3 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">{{ __('dashboard.menu') }}</p>

            {{-- Overview (role-specific href) --}}
            <a href="{{ $overviewHref }}" class="mt-2 {{ $overviewActive ? $active : $link }}">
                <span class="{{ $overviewActive ? $iconWrapActive : $iconWrap }}">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <div class="flex-1">
                    <p class="font-bold leading-5">{{ __('dashboard.overview') }}</p>
                    <p class="text-xs opacity-80">{{ __('dashboard.overview_desc') }}</p>
                </div>
            </a>

            {{-- Admin menu — only when $role === 'admin' --}}
            @include('livewire.dashboard.partials._sidebar-nav-admin')

            {{-- Technician menu — only when $role === 'technician' --}}
            @include('livewire.dashboard.partials._sidebar-nav-technician')

            {{-- Company menu — only when $role === 'company' --}}
            @include('livewire.dashboard.partials._sidebar-nav-company')

            {{-- Guest / Driver: minimal nav (no role-specific links). Driver uses layouts.driver; this is fallback. --}}
            @if ($role === 'guest' || $role === 'driver')
                <a href="{{ route('sign-in.index') }}" class="mt-2 {{ $link }}">
                    <span class="{{ $iconWrap }}"><i class="fa-solid fa-right-to-bracket"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('login.sign_in') }}</p>
                        <p class="text-xs opacity-80">{{ __('dashboard.main_page_desc') }}</p>
                    </div>
                </a>
            @endif
        </nav>
    </div>

    {{-- Footer (Fixed Bottom) — logout / user info --}}
    <div class="p-6 border-t border-slate-200/70 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 flex items-center justify-center font-black">
                {{ $avatarLetter }}
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-bold leading-5 truncate">{{ $displayName }}</p>
                @if ($displayEmail)
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $displayEmail }}</p>
                @endif
            </div>

            {{-- Logout: company uses company.logout, driver uses driver.logout, web uses logout --}}
            @if ($role === 'company')
                <form method="POST" action="{{ route('company.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> {{ __('dashboard.logout') }}
                    </button>
                </form>
            @elseif($role === 'driver')
                <form method="POST" action="{{ route('driver.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> {{ __('dashboard.logout') }}
                    </button>
                </form>
            @elseif(in_array($role, ['admin', 'technician']))
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> {{ __('dashboard.logout') }}
                    </button>
                </form>
            @else
                <a href="{{ route('sign-in.index') }}"
                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> {{ __('login.sign_in') }}
                </a>
            @endif
        </div>
    </div>
</aside>
