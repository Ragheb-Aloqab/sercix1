<aside id="sidebar"
    class="flex flex-col w-full h-full min-h-dvh
    backdrop-blur shadow-soft lg:shadow-none
    border-e overflow-hidden
    {{ $role === 'company' ? 'bg-servx-black border-slate-600/50' : 'bg-white/80 dark:bg-slate-900/70 border-slate-200/70 dark:border-slate-800' }}">
    <div class="px-6 py-6 border-b {{ $role === 'company' ? 'border-slate-600/50' : 'border-slate-200/70 dark:border-slate-800' }} flex items-center justify-between">
        <a href="{{ route('index') }}" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
            <img src="{{ $siteLogoUrl ?? asset('images/serv.x logo.png') }}" alt="" width="44" height="44" class="w-11 h-11 rounded-full object-cover flex-shrink-0">
            <div class="min-w-0">
                <p class="font-extrabold leading-5 truncate {{ $role === 'company' ? 'text-white' : '' }}">
                    {{ $siteName ?? 'Servx Motors' }}
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
                <p class="text-xs {{ $role === 'company' ? 'text-slate-500' : 'text-slate-500 dark:text-slate-400' }}">{{ __('dashboard.dashboard_v1') }}</p>
            </div>
        </a>

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
        <nav class="px-4 pb-6" @click="$dispatch('close-sidebar')">
            <p class="px-3 text-xs font-semibold {{ $role === 'company' ? 'text-servx-silver' : 'text-slate-500 dark:text-slate-400' }} mb-2">{{ __('dashboard.menu') }}</p>

            {{-- Overview (role-specific href) --}}
            <a href="{{ $overviewHref }}" class="mt-2 {{ $overviewActive ? $active : $link }}">
                <span class="{{ $overviewActive ? $iconWrapActive : $iconWrap }}">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <div class="flex-1">
                    <p class="font-bold leading-5">{{ __('dashboard.overview') }}</p>
                    <p class="text-xs {{ $role === 'company' ? 'text-servx-silver' : 'opacity-80' }}">{{ __('dashboard.overview_desc') }}</p>
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
    <div class="p-6 border-t {{ $role === 'company' ? 'border-slate-600/50' : 'border-slate-200/70 dark:border-slate-800' }}">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-2xl flex items-center justify-center font-black {{ $role === 'company' ? 'bg-[#3B82F6] text-white' : 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' }}">
                {{ $avatarLetter }}
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-bold leading-5 truncate {{ $role === 'company' ? 'text-servx-silver-light' : '' }}">{{ $displayName }}</p>
                @if ($displayEmail)
                    <p class="text-xs truncate {{ $role === 'company' ? 'text-servx-silver' : 'text-slate-500 dark:text-slate-400' }}">{{ $displayEmail }}</p>
                @endif
            </div>

            {{-- Logout: company uses company.logout, driver uses driver.logout, web uses logout --}}
            @if ($role === 'company')
                    <form method="POST" action="{{ route('company.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 text-servx-silver text-sm font-semibold transition-colors">
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-right-from-bracket"></i>{{ __('dashboard.logout') }}</span>
                    </button>
                </form>
            @elseif($role === 'driver')
                <form method="POST" action="{{ route('driver.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-right-from-bracket"></i>{{ __('dashboard.logout') }}</span>
                    </button>
                </form>
            @elseif(in_array($role, ['admin', 'technician']))
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-right-from-bracket"></i>{{ __('dashboard.logout') }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('sign-in.index') }}"
                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-right-to-bracket"></i>{{ __('login.sign_in') }}</span>
                </a>
            @endif
        </div>
    </div>
</aside>
