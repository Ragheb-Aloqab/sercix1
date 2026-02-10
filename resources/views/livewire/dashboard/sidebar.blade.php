<aside id="sidebar"
    class="fixed lg:fixed inset-y-0 z-50 w-80 max-w-[90vw]
    bg-white/80 dark:bg-slate-900/70 backdrop-blur border-slate-200/70 dark:border-slate-800
    border-e lg:border-e shadow-soft lg:shadow-none
    translate-x-full lg:translate-x-0 transition-transform duration-300
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
            <div
                class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center text-white font-black">
                S
            </div>
            <div>
                <p class="font-extrabold leading-5">
                    SERV.X
                    @if ($role === 'admin')
                        Admin
                    @elseif($role === 'technician')
                        Technician
                    @elseif($role === 'company')
                        Company
                    @else
                        Guest
                    @endif
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Dashboard v1</p>
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
                    <p class="font-bold">إجراء سريع</p>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">أضف خدمة أو راجع الطلبات.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('admin.services.index') }}"
                            class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                            <i class="fa-solid fa-plus me-2"></i> إضافة خدمة
                        </a>
                        <a href="{{ route('admin.orders.index') }}"
                            class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold">
                            <i class="fa-solid fa-receipt me-2"></i> الطلبات
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Nav --}}
        <nav class="px-4 pb-6">
            <p class="px-3 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">القائمة</p>

            {{-- Overview --}}
            <a href="{{ $overviewHref }}" class="{{ $overviewActive ? $active : $link }}">
                <span class="{{ $overviewActive ? $iconWrapActive : $iconWrap }}">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <div class="flex-1">
                    <p class="font-bold leading-5">نظرة عامة</p>
                    <p class="text-xs opacity-80">إحصائيات وتشغيل</p>
                </div>
            </a>

            {{-- Admin menu --}}
            @if ($role === 'admin')
                <a href="{{ route('admin.orders.index') }}" class="mt-2 {{ $is('admin.orders.index') || $is('admin.orders.show') ? $active : $link }}">
                    <span class="{{ $is('admin.orders.index') || $is('admin.orders.show') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-receipt"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">الطلبات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">متابعة وإسناد</p>
                    </div>
                </a>

                <a href="{{ route('admin.bank-transfers.index') }}" class="mt-2 {{ $is('admin.bank-transfers.*') ? $active : $link }}">
                    <span class="{{ $is('admin.bank-transfers.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-landmark"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">التحويلات البنكية</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">مراجعة إيصالات</p>
                    </div>
                </a>

                <a href="{{ route('admin.services.index') }}"
                    class="mt-2 {{ $is('admin.services.*') ? $active : $link }}">
                    <span class="{{ $is('admin.services.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-screwdriver-wrench"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">الخدمات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">إدارة الخدمات</p>
                    </div>
                </a>

                <a href="{{ route('admin.technicians.index') }}"
                    class="mt-2 {{ $is('admin.technicians.*') ? $active : $link }}">
                    <span class="{{ $is('admin.technicians.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-user-gear"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">الفنيين</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">إدارة الفنيين</p>
                    </div>
                </a>

                <a href="{{ route('admin.maps.technicians') }}"
                    class="mt-2 {{ $is('admin.maps.technicians') ? $active : $link }}">
                    <span class="{{ $is('admin.maps.technicians') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-map-location-dot"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">خريطة الفنيين</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">آخر مواقعهم</p>
                    </div>
                </a>

                <a href="{{ route('admin.customers.index') }}"
                    class="mt-2 {{ $is('admin.customers.*') ? $active : $link }}">
                    <span class="{{ $is('admin.customers.*') ? $iconWrapActive : $iconWrap }}">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">العملاء</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">إدارة العملاء</p>
                    </div>
                </a>


                <a href="{{ route('admin.inventory.index') }}"
                    class="mt-2 {{ $is('admin.inventory.index') ? $active : $link }}">
                    <span class="{{ $is('admin.inventory.index') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-boxes-stacked"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">المخزون</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">قطع/زيوت/فلاتر</p>
                    </div>
                </a>
                <a href="{{ route('admin.inventory.movements') }}"
                   class="mt-1 flex items-center gap-3 pr-6 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 {{ $is('admin.inventory.movements') ? 'bg-slate-100 dark:bg-slate-800' : '' }}">
                    <div class="flex-1">
                        <p class="font-semibold leading-5 text-sm">حركة المخزون</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">إضافة / خصم / تعديل</p>
                    </div>
                </a>
                <a href="{{ route('admin.activities.index') }}"
                    class="mt-2 {{ $is('admin.activities.*') ? $active : $link }}">
                    <span class="{{ $is('admin.activities.*') ? $iconWrapActive : $iconWrap }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">سجل الأنشطة</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">متابعة كل العمليات</p>
                    </div>
                </a>
                <a href="{{ route('admin.settings') }}" class="mt-2 {{ $is('admin.settings') ? $active : $link }}">
                    <span class="{{ $is('admin.settings') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-gear"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">الإعدادات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">الحساب/النظام</p>
                    </div>
                </a>
            @endif

            {{-- Technician menu --}}
            @if ($role === 'technician')
                <a href="{{ route('tech.tasks.index') }}" class="mt-2 {{ $is('tech.tasks.*') ? $active : $link }}">
                    <span class="{{ $is('tech.tasks.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-list-check"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">المهام</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">الطلبات الموكلة</p>
                    </div>
                </a>


                <a href="{{ route('tech.settings') }}" class="mt-2 {{ $is('tech.settings') ? $active : $link }}">
                    <span class="{{ $is('tech.settings') ? $iconWrapActive : $iconWrap }}">
                        <i class="fa-solid fa-gear"></i>
                    </span>

                    <div class="flex-1">
                        <p class="font-bold leading-5">الإعدادات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">الحساب/النظام</p>
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
                        <p class="font-bold leading-5">الطلبات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">متابعة الطلبات</p>
                    </div>
                </a>

                <a href="{{ route('company.invoices.index') }}"
                    class="mt-2 {{ $is('company.invoices.*') ? $active : $link }}">
                    <span class="{{ $is('company.invoices.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-file-invoice"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">الفواتير</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">عرض/تحميل PDF</p>
                    </div>
                </a>

                <a href="{{ route('company.payments.index') }}"
                    class="mt-2 {{ $is('company.payments.*') ? $active : $link }}">
                    <span class="{{ $is('company.payments.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-credit-card"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">المدفوعات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tap / كاش</p>
                    </div>
                </a>

                <a href="{{ route('company.services.index') }}"
                    class="mt-2 {{ $is('company.services.*') ? $active : $link }}">
                    <span class="{{ $is('company.services.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-screwdriver-wrench"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">الخدمات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">الخدمات المتاحة</p>
                    </div>
                </a>

                <a href="{{ route('company.vehicles.index') }}"
                    class="mt-2 {{ $is('company.vehicles.*') ? $active : $link }}">
                    <span class="{{ $is('company.vehicles.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-car"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">المركبات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">إدارة المركبات</p>
                    </div>
                </a>

                <a href="{{ route('company.branches.index') }}"
                    class="mt-2 {{ $is('company.branches.*') ? $active : $link }}">
                    <span class="{{ $is('company.branches.*') ? $iconWrapActive : $iconWrap }}"><i
                            class="fa-solid fa-code-branch"></i></span>
                    <div class="flex-1">
                        <p class="font-bold leading-5">الفروع</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">إدارة الفروع</p>
                    </div>
                </a>

                <a href="{{ route('company.settings') }}"
                    class="mt-2 {{ $is('company.settings') ? $active : $link }}">
                    <span class="{{ $is('company.settings') ? $iconWrapActive : $iconWrap }}">
                        <i class="fa-solid fa-gear"></i>
                    </span>

                    <div class="flex-1">
                        <p class="font-bold leading-5">الإعدادات</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">الحساب / الأمان</p>
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
                    <i class="fa-solid fa-right-from-bracket me-2"></i> خروج
                </button>
            </form>
        </div>
    </div>
</aside>
