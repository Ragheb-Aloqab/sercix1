@php
    $isCompanyApp = auth('company')->check() || auth('maintenance_center')->check();
    $displayName = $isCompanyApp
        ? (auth('company')->user()?->company_name ?? auth('maintenance_center')->user()?->name ?? '')
        : (auth('web')->user()?->name ?? '');
    $displayEmail = $isCompanyApp
        ? (auth('company')->user()?->email ?? auth('maintenance_center')->user()?->email ?? '')
        : (auth('web')->user()?->email ?? '');
    $avatarLetter = strtoupper(substr($displayName ?: 'U', 0, 1));
    $isWlTopbar = ($wlBranding ?? false) && $isCompanyApp;
@endphp
<header
    class="sticky top-0 z-20 transition-colors duration-300 {{ $isWlTopbar ? 'topbar-wl-white' : ($isCompanyApp ? 'bg-slate-50/90 dark:bg-slate-900/70 border-slate-200/70 dark:border-slate-600/50 topbar-app-mobile backdrop-blur border-b' : 'bg-slate-50/70 dark:bg-slate-950/60 border-slate-200/70 dark:border-slate-800 backdrop-blur border-b') }} {{ ($wlBranding ?? false) && !$isWlTopbar ? 'topbar-wl-branded' : '' }} {{ $isWlTopbar ? 'topbar-wl-branded' : '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center gap-2 sm:gap-3 min-w-0">
        {{-- Single row: mobile (company) = [Notifications][Search][Toggle]; desktop = full layout --}}
        <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 flex-wrap sm:flex-nowrap {{ $isCompanyApp ? 'flex-row' : '' }}">
            {{-- Start (RTL: right): Hamburger + Logo/Name (WL) or Menu + User (non-WL) --}}
            <div class="flex items-center gap-3 shrink-0 min-w-0 {{ $isCompanyApp ? 'hidden sm:flex' : '' }}">
                <button type="button"
                        @click="sidebarOpen = true"
                        class="lg:hidden flex items-center justify-center w-10 h-10 rounded-xl transition-colors duration-300 {{ $isWlTopbar ? 'text-slate-600 hover:bg-slate-100' : ($isCompanyApp ? 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/80 dark:hover:bg-slate-700/50' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800') }} -ms-2"
                        aria-label="{{ __('dashboard.menu') }}">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                @if($isWlTopbar)
                    {{-- White-label: logo + company name column (start side) --}}
                    <div class="flex flex-col items-start justify-center shrink-0">
                        @if($siteLogoUrl ?? null)
                            <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? '' }}" class="wl-topbar-logo max-h-10 w-auto object-contain" width="120" height="40" />
                        @else
                            <div class="h-10 flex items-center justify-center text-xl font-bold" style="color: var(--wl-primary);">{{ $siteName ? strtoupper(substr($siteName, 0, 2)) : '' }}</div>
                        @endif
                        <span class="wl-topbar-site-name text-[0.75rem] mt-0.5 truncate max-w-[140px] sm:max-w-[180px]" style="color: var(--wl-primary);">{{ $siteName ?? '' }}</span>
                    </div>
                @elseif($isCompanyApp && $displayName)
                    <div class="hidden sm:block relative" x-data="{ companyMenuOpen: false }" @click.outside="companyMenuOpen = false">
                        <button type="button"
                                @click="companyMenuOpen = !companyMenuOpen"
                                class="flex items-center gap-2 min-w-0 rounded-xl px-1 py-1.5 -mx-1 hover:bg-slate-200/80 dark:hover:bg-slate-700/50 transition-colors text-left w-full">
                            @if(app()->bound('company'))
                                @php
                                    $logoUrl = ($wlBranding ?? false)
                                        ? ($siteLogoUrl ?? asset('images/serv.x logo.png'))
                                        : (app('company')->getLogoUrl() ?? $siteLogoUrl ?? asset('images/serv.x logo.png'));
                                @endphp
                                <img src="{{ $logoUrl }}" alt="{{ app('company')->company_name }}" class="w-9 h-9 rounded-xl object-contain shrink-0 bg-white/10">
                            @else
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black shrink-0 {{ auth('company')->check() ? 'bg-sky-500 text-white' : 'bg-slate-700 text-white' }}">
                                    {{ $avatarLetter }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-bold text-sm truncate text-slate-900 dark:text-white">{{ $displayName }}</p>
                                @if($displayEmail)
                                    <p class="text-xs truncate text-slate-500 dark:text-slate-400">{{ Str::limit($displayEmail, 20) }}</p>
                                @endif
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-500 dark:text-slate-400 shrink-0 ms-auto"></i>
                        </button>
                        <div x-show="companyMenuOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute start-0 top-full mt-2 min-w-[200px] rounded-xl shadow-lg py-1 z-50 bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700"
                             style="display: none;">
                            <a href="{{ route('index') }}"
                               class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors first:rounded-t-xl">
                                <i class="fa-solid fa-house text-slate-500 dark:text-slate-400 w-4"></i>
                                {{ __('dashboard.main_page') }}
                            </a>
                            @if(auth('company')->check())
                                <form method="POST" action="{{ route('company.logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2 px-4 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-left last:rounded-b-xl">
                                        <i class="fa-solid fa-right-from-bracket text-slate-500 dark:text-slate-400 w-4"></i>
                                        {{ __('dashboard.logout') }}
                                    </button>
                                </form>
                            @elseif(auth('maintenance_center')->check())
                                <form method="POST" action="{{ route('maintenance-center.logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2 px-4 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-left last:rounded-b-xl">
                                        <i class="fa-solid fa-right-from-bracket text-slate-500 dark:text-slate-400 w-4"></i>
                                        {{ __('dashboard.logout') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            
            {{-- Center: Search (company) or Page title --}}
            <div class="flex-1 flex justify-center min-w-0 {{ $isCompanyApp ? 'order-2 sm:order-2 min-w-0' : '' }}">
                @if($isCompanyApp)
                <div class="w-full max-w-md mx-auto [&>div]:!block [&>div]:!w-full">
                    <livewire:dashboard.global-search />
                </div>
                @else
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm truncate text-slate-500 dark:text-slate-400">@yield('subtitle', __('dashboard.subtitle_default'))</p>
                    <h1 class="text-base sm:text-xl md:text-2xl font-black tracking-tight truncate text-slate-900 dark:text-white">@yield('page_title', __('dashboard.page_title_default'))</h1>
                </div>
                @endif
            </div>

            {{-- End (RTL: left): Preferences, Notifications, User (for WL company show user here) --}}
            <div class="flex items-center gap-1 sm:gap-2 shrink-0 {{ $isCompanyApp ? 'order-1 sm:order-3' : '' }}">
                @if($isWlTopbar && $displayName)
                    <div class="hidden sm:block relative" x-data="{ companyMenuOpen: false }" @click.outside="companyMenuOpen = false">
                        <button type="button"
                                @click="companyMenuOpen = !companyMenuOpen"
                                class="flex items-center gap-2 min-w-0 rounded-xl px-2 py-1.5 hover:bg-slate-100 transition-colors text-left">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold shrink-0 text-white text-sm" style="background: var(--wl-primary);">
                                {{ $avatarLetter }}
                            </div>
                            <div class="min-w-0 hidden md:block">
                                <p class="font-bold text-sm truncate text-slate-900">{{ $displayName }}</p>
                                @if($displayEmail)
                                    <p class="text-xs truncate text-slate-500">{{ Str::limit($displayEmail, 18) }}</p>
                                @endif
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-500 shrink-0 ms-auto"></i>
                        </button>
                        <div x-show="companyMenuOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute end-0 top-full mt-2 min-w-[200px] rounded-xl shadow-lg py-1 z-50 bg-white border border-slate-200/70 rtl:end-0 rtl:start-auto"
                             style="display: none;">
                            <a href="{{ route('index') }}"
                               class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 hover:bg-slate-100 transition-colors first:rounded-t-xl">
                                <i class="fa-solid fa-house text-slate-500 w-4"></i>
                                {{ __('dashboard.main_page') }}
                            </a>
                            @if(auth('company')->check())
                                <form method="POST" action="{{ route('company.logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2 px-4 py-3 text-sm text-slate-700 hover:bg-slate-100 transition-colors text-left last:rounded-b-xl">
                                        <i class="fa-solid fa-right-from-bracket text-slate-500 w-4"></i>
                                        {{ __('dashboard.logout') }}
                                    </button>
                                </form>
                            @elseif(auth('maintenance_center')->check())
                                <form method="POST" action="{{ route('maintenance-center.logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2 px-4 py-3 text-sm text-slate-700 hover:bg-slate-100 transition-colors text-left last:rounded-b-xl">
                                        <i class="fa-solid fa-right-from-bracket text-slate-500 w-4"></i>
                                        {{ __('dashboard.logout') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
                <livewire:dashboard.ui-preferences />
                <livewire:dashboard.notifications-bell />
            </div>
        </div>
    </div>
    @if($isCompanyApp)
        <div class="px-4 sm:px-6 pb-2 sm:hidden">
            <p class="text-xs truncate text-slate-500 dark:text-slate-400">@yield('subtitle', __('dashboard.subtitle_default'))</p>
            <h1 class="text-base font-black tracking-tight truncate text-slate-900 dark:text-white">@yield('page_title', __('dashboard.page_title_default'))</h1>
        </div>
    @endif
</header>
