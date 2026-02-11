<aside id="sidebar"
    class="fixed inset-y-0 z-50 w-80 max-w-[90vw]
    left-0
    bg-white/80 dark:bg-slate-900/70 backdrop-blur border-slate-200/70 dark:border-slate-800
    border-e lg:border-e shadow-soft lg:shadow-none
    translate-x-full lg:translate-x-0
    transition-transform duration-300 ease-out
    flex flex-col h-dvh overflow-hidden">
    @php
        //  Source of truth: guards
        $isCompany = auth('company')->check();
        $companyUser = auth('company')->user();

        $webUser = auth('web')->user();
        $webRole = $webUser->role ?? null;

        // final role (web guard FIRST)
        $role = match (true) {
            auth('web')->check() && $webRole === 'admin' => 'admin',
            auth('web')->check() && $webRole === 'technician' => 'technician',
            auth('company')->check() => 'company',
            default => 'guest',
        };
        $is = fn($name) => request()->routeIs($name);

        $link = 'group flex items-center gap-3 px-3 py-3 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800';
        $active =
            'group flex items-center gap-3 px-3 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900';
        $iconWrap = 'w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center';
        $iconWrapActive = 'w-9 h-9 rounded-xl bg-white/15 dark:bg-slate-900/10 flex items-center justify-center';

        //  Overview link per actor
        $overviewHref = match ($role) {
            'admin' => route('admin.dashboard'),
            'technician' => route('tech.dashboard'),
            'company' => route('company.dashboard'),
            default => url('/'),
        };

        $overviewActive = match ($role) {
            'admin' => $is('admin.dashboard'),
            'technician' => $is('tech.dashboard'),
            'company' => $is('company.dashboard'),
            default => false,
        };
    @endphp

    {{-- Brand (Fixed Top) --}}
    <div class="px-6 py-6 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-3">
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
                    @else
                        {{ __('dashboard.guest') }}
                    @endif
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.dashboard_v1') }}</p>
            </div>
        </div>

        <button id="closeSidebar"
            class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl
            border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{--  Scrollable Content (Admin quick actions + nav) --}}
    <div class="flex-1 overflow-y-auto overscroll-contain">
        {{-- Quick actions (Admin only) --}}
        @if ($role === 'admin')
            <div class="p-6">
                <div
                    class="rounded-2xl p-4 bg-gradient-to-br from-emerald-500/10 to-sky-500/10 border border-emerald-500/10 dark:border-slate-800">
                    <p class="font-bold">{{ __('dashboard.quick_action') }}</p>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">{{ __('dashboard.quick_action_desc') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('admin.services.index') }}"
                            class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                            <i class="fa-solid fa-plus me-2"></i> {{ __('dashboard.add_service') }}
                        </a>
                        <a href="{{ route('admin.orders.index') }}"
                            class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold">
                            <i class="fa-solid fa-receipt me-2"></i> {{ __('dashboard.orders') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Nav --}}
        <nav class="px-4 pb-6">
            <p class="px-3 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">{{ __('dashboard.menu') }}</p>

            {{-- Overview --}}
            <a href="{{ $overviewHref }}" class="{{ $overviewActive ? $active : $link }}">
                <span class="{{ $overviewActive ? $iconWrapActive : $iconWrap }}">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <div class="flex-1">
                    <p class="font-bold leading-5">{{ __('dashboard.overview') }}</p>
                    <p class="text-xs opacity-80">{{ __('dashboard.overview_desc') }}</p>
                </div>
            </a>

            {{-- Admin menu --}}
            @if ($role === 'admin')
                <a href="{{ route('admin.orders.index') }}" class="mt-2 {{ $is('admin.orders.index') || $is('admin.orders.show') ? $active : $link }}">
                    <span class="{{ $is('admin.orders.index') || $is('admin.orders.show') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-receipt"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.orders') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.orders_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('admin.bank-transfers.index') }}" class="mt-2 {{ $is('admin.bank-transfers.*') ? $active : $link }}">
                    <span class="{{ $is('admin.bank-transfers.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-landmark"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.bank_transfers') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.bank_transfers_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('admin.services.index') }}"
                    class="mt-2 {{ $is('admin.services.*') ? $active : $link }}">
                    <span class="{{ $is('admin.services.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-screwdriver-wrench"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.services') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.services_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('admin.technicians.index') }}"
                    class="mt-2 {{ $is('admin.technicians.*') ? $active : $link }}">
                    <span class="{{ $is('admin.technicians.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-user-gear"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.technicians') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.technicians_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('admin.maps.technicians') }}"
                    class="mt-2 {{ $is('admin.maps.technicians') ? $active : $link }}">
                    <span class="{{ $is('admin.maps.technicians') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-map-location-dot"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.technicians_map') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.technicians_map_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('admin.customers.index') }}"
                    class="mt-2 {{ $is('admin.customers.*') ? $active : $link }}">
                    <span class="{{ $is('admin.customers.*') ? $iconWrapActive : $iconWrap }}">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.customers') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.customers_desc') }}</p>
                    </div>
                </a>


                <a href="{{ route('admin.inventory.index') }}"
                    class="mt-2 {{ $is('admin.inventory.index') ? $active : $link }}">
                    <span class="{{ $is('admin.inventory.index') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-boxes-stacked"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.inventory') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.inventory_desc') }}</p>
                    </div>
                </a>
                <a href="{{ route('admin.inventory.movements') }}"
                   class="mt-1 {{ $is('admin.inventory.movements') ? $active : $link }}">
                    <span class="{{ $is('admin.inventory.movements') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-arrows-left-right"></i></span>
                    <div class="flex-1">
                        <p class="font-semibold leading-5 text-sm">{{ __('dashboard.movements') }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('dashboard.movements_desc') }}</p>
                    </div>
                </a>
                <a href="{{ route('admin.activities.index') }}"
                    class="mt-2 {{ $is('admin.activities.*') ? $active : $link }}">
                    <span class="{{ $is('admin.activities.*') ? $iconWrapActive : $iconWrap }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.activity_log') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.activity_log_desc') }}</p>
                    </div>
                </a>
                <a href="{{ route('admin.settings') }}" class="mt-2 {{ $is('admin.settings') ? $active : $link }}">
                    <span class="{{ $is('admin.settings') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-gear"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.settings') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.settings_desc') }}</p>
                    </div>
                </a>
            @endif

            {{-- Technician menu --}}
            @if ($role === 'technician')
                <a href="{{ route('tech.tasks.index') }}" class="mt-2 {{ $is('tech.tasks.*') ? $active : $link }}">
                    <span class="{{ $is('tech.tasks.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-list-check"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.tasks') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.tasks_desc') }}</p>
                    </div>
                </a>


                <a href="{{ route('tech.settings') }}" class="mt-2 {{ $is('tech.settings') ? $active : $link }}">
                    <span class="{{ $is('tech.settings') ? $iconWrapActive : $iconWrap }}">
                        <i class="fa-solid fa-gear"></i>
                    </span>

                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.settings') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.settings_desc') }}</p>
                    </div>
                </a>
            @endif

            {{-- Company menu --}}
            @if ($role === 'company')
                <a href="{{ route('company.orders.index') }}"
                    class="mt-2 {{ $is('company.orders.*') ? $active : $link }}">
                    <span class="{{ $is('company.orders.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-receipt"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.orders') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.company_orders_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('company.invoices.index') }}"
                    class="mt-2 {{ $is('company.invoices.*') ? $active : $link }}">
                    <span class="{{ $is('company.invoices.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-file-invoice"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.invoices') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.invoices_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('company.payments.index') }}"
                    class="mt-2 {{ $is('company.payments.*') ? $active : $link }}">
                    <span class="{{ $is('company.payments.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-credit-card"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.payments') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.payments_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('company.services.index') }}"
                    class="mt-2 {{ $is('company.services.*') ? $active : $link }}">
                    <span class="{{ $is('company.services.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-screwdriver-wrench"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.services') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.company_services_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('company.vehicles.index') }}"
                    class="mt-2 {{ $is('company.vehicles.*') ? $active : $link }}">
                    <span class="{{ $is('company.vehicles.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-car"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.vehicles') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.vehicles_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('company.branches.index') }}"
                    class="mt-2 {{ $is('company.branches.*') ? $active : $link }}">
                    <span class="{{ $is('company.branches.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-code-branch"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.branches') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.branches_desc') }}</p>
                    </div>
                </a>

                <a href="{{ route('company.settings') }}"
                    class="mt-2 {{ $is('company.settings') ? $active : $link }}">
                    <span class="{{ $is('company.settings') ? $iconWrapActive : $iconWrap }}">
                        <i class="fa-solid fa-gear"></i>
                    </span>

                    <div class="flex-1">
                        <p class="font-bold leading-5">{{ __('dashboard.settings') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.company_settings_desc') }}</p>
                    </div>
                </a>
            @endif
        </nav>
    </div>

    {{-- Footer (Fixed Bottom) --}}
    @php
        $displayName = $isCompany ? $companyUser->company_name ?? 'Company' : $webUser->name ?? 'User';
        
        $displayEmail = $isCompany ? $companyUser->email ?? '' : $webUser->email ?? '';
        $avatarLetter = strtoupper(substr($displayName, 0, 1));
    @endphp

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

            
            <form method="POST" action="{{ request()->is('company/*') ? route('company.logout') : route('logout') }}">
                @csrf
                <button type="submit"
                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> {{ __('dashboard.logout') }}
                </button>
            </form>
        </div>
    </div>
</aside>
