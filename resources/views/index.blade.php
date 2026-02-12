<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ session('ui.dir', 'rtl') }}">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>{{ $siteName ?? 'SERV.X' }} — إدارة أساطيل الشركات (زيوت وفلاتر)</title>
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <!-- (اختياري) Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --shadow: 0 18px 60px rgba(0, 0, 0, .12);
        }

        body {
            font-family: "Tajawal", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, .15);
            border-radius: 999px;
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    boxShadow: {
                        soft: "var(--shadow)"
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-900">

<!-- Header -->
<header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
        <!-- Logo -->
        <a href="#home" class="flex items-center gap-3 group">
            <div class="flex items-center justify-center h-12 w-12 rounded-full shadow-soft overflow-hidden shrink-0 border border-slate-100">
                @if($siteLogoUrl ?? null)
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'SERV.X' }}" class="h-full w-full object-cover"/>
                @else
                    <div class="h-full w-full bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center text-white font-black text-lg">
                        {{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="min-w-0">
                <div class="text-lg font-extrabold leading-5 truncate group-hover:text-slate-900" id="brandName">{{ $siteName ?? 'SERV.X' }}</div>
                <div class="text-xs text-slate-500 truncate" id="brandTag">حلول صيانة الأساطيل للشركات</div>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="#solutions" class="text-slate-700 hover:text-slate-900" id="navServices">{{ __('index.navServices') }}</a>
            <a href="#how" class="text-slate-700 hover:text-slate-900" id="navHow">{{ __('index.navHow') }}</a>
            <a href="#plans" class="text-slate-700 hover:text-slate-900" id="navPricing">{{ __('index.navPricing') }}</a>
            <a href="#faq" class="text-slate-700 hover:text-slate-900" id="navFaq">{{ __('index.navFaq') }}</a>

            <!-- Language Menu -->
            @php $currentLocale = app()->getLocale(); @endphp
            <div class="relative" id="langMenuWrap">
                <button type="button" id="langMenuBtn" aria-expanded="false" aria-haspopup="true"
                        class="inline-flex items-center gap-2 text-slate-700 hover:text-slate-900">
                    <i class="fa-solid fa-globe"></i>
                    <span>{{ $currentLocale === 'ar' ? 'العربية' : 'English' }}</span>
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>
                <div id="langDropdown" class="hidden absolute end-0 mt-2 w-40 bg-white rounded-xl shadow-lg py-2 text-sm z-50 border border-slate-200">
                    <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
                       class="block px-4 py-2 hover:bg-slate-100 {{ $currentLocale === 'ar' ? 'bg-slate-50 font-semibold' : '' }}">
                        العربية
                    </a>
                    <a href="{{ route('set-locale', ['lang' => 'en']) }}"
                       class="block px-4 py-2 hover:bg-slate-100 {{ $currentLocale === 'en' ? 'bg-slate-50 font-semibold' : '' }}">
                        English
                    </a>
                </div>
            </div>

            <!-- User Authentication -->
            @php
                $user = null;
                $dashboardRoute = '#';
                if (Auth::guard('company')->check()) {
                    $user = Auth::guard('company')->user();
                    $dashboardRoute = route('company.dashboard');
                } elseif (Auth::guard('web')->check()) {
                    $user = Auth::guard('web')->user();
                    $dashboardRoute = ($user->role ?? null) === 'technician' ? route('tech.dashboard') : route('admin.dashboard');
                }
            @endphp

            @if($user)
                <!-- User Menu -->
                <div class="relative" id="userMenuWrap">
                    <button type="button" id="userMenuBtn" aria-expanded="false" aria-haspopup="true"
                            class="inline-flex items-center gap-2 text-slate-700 hover:text-slate-900 font-extrabold">
                        <i class="fa-solid fa-user"></i>
                        <span>{{ $user->name ?? $user->company_name }}</span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>

                    <div id="userDropdown" class="hidden absolute end-0 mt-2 w-48 bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-50 rounded-xl shadow-lg py-2 text-sm z-50 border border-slate-200 dark:border-slate-700">
                        <a href="{{ $dashboardRoute }}" data-i18n="navDashboard"
                           class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700">{{ __('index.navDashboard') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" data-i18n="navLogout"
                                    class="w-full rtl:text-right ltr:text-left px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700">
                                {{ __('index.navLogout') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('sign-in.index') }}"
                   class="inline-flex items-center gap-2 text-slate-700 hover:text-slate-900 font-extrabold">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    {{ __('index.navLogin') }}
                </a>
            @endif
        </nav>

        <!-- Mobile Menu Button -->
        <div class="flex items-center gap-2">
            <a href="#request" id="ctaBookTop"
               class="hidden sm:inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-white text-sm font-semibold hover:bg-slate-800 transition">
                <i class="fa-solid fa-file-signature me-2"></i>
                {{ __('index.ctaBookTop') }}
            </a>

            <button id="btnMobile"
                    class="md:hidden inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50 transition">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="md:hidden hidden border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex flex-col gap-2 text-sm font-medium">
            <a href="#solutions" class="py-2" id="mNavServices">{{ __('index.mNavServices') }}</a>
            <a href="#how" class="py-2" id="mNavHow">{{ __('index.mNavHow') }}</a>
            <a href="#plans" class="py-2" id="mNavPricing">{{ __('index.mNavPricing') }}</a>
            <a href="#faq" class="py-2" id="mNavFaq">{{ __('index.mNavFaq') }}</a>

            <div class="flex items-center gap-3 py-2 border-t border-slate-100 mt-2 pt-3">
                <span class="text-slate-500 text-xs">{{ __('index.language') }}:</span>
                <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
                   class="py-1 px-3 rounded-lg {{ $currentLocale === 'ar' ? 'bg-slate-900 text-white font-semibold' : 'bg-slate-100 hover:bg-slate-200' }}">
                    العربية
                </a>
                <a href="{{ route('set-locale', ['lang' => 'en']) }}"
                   class="py-1 px-3 rounded-lg {{ $currentLocale === 'en' ? 'bg-slate-900 text-white font-semibold' : 'bg-slate-100 hover:bg-slate-200' }}">
                    English
                </a>
            </div>

            @if($user)
                <a href="{{ $dashboardRoute }}" class="py-2 font-extrabold text-slate-900" data-i18n="navDashboard">{{ __('index.navDashboard') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-start py-2 font-extrabold text-slate-900" data-i18n="navLogout">{{ __('index.navLogout') }}</button>
                </form>
            @else
                <a href="{{ route('sign-in.index') }}" class="py-2 font-extrabold text-slate-900">{{ __('index.navLogin') }}</a>
            @endif
        </div>
    </div>
</header>



<!-- Hero -->
<section id="home" class="relative overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute -top-24 -start-20 h-72 w-72 rounded-full bg-emerald-200 blur-3xl opacity-70"></div>
        <div class="absolute top-10 -end-24 h-80 w-80 rounded-full bg-sky-200 blur-3xl opacity-70"></div>
        <div
                class="absolute -bottom-24 start-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-indigo-200 blur-3xl opacity-60">
        </div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
        <div class="grid lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-6">
                <div
                        class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm shadow-soft border border-slate-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-700" id="heroBadge">B2B • أسطول • جدولة • فواتير • SLA</span>
                </div>

                <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                    <span id="heroTitle1">إدارة صيانة</span>
                    <span class="block bg-gradient-to-l from-emerald-600 to-sky-600 bg-clip-text text-transparent"
                          id="heroTitle2">
                            أسطول شركتك بسهولة
                        </span>
                </h1>

                <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed" id="heroDesc">
                    خدمة مخصصة للشركات: تغيير زيت وفلاتر في موقعكم (مقر/فروع)، جدولة دورية، فريق متنقل، تقارير،
                    وفواتير شهرية مجمعة — مع API جاهزة للدمج.
                </p>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a href="#request"
                       class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-white font-bold hover:bg-slate-800 transition shadow-soft"
                       id="heroCtaPrimary">
                        <i class="fa-solid fa-file-signature me-2"></i>
                        اطلب عرض سعر
                    </a>
                    <a href="#how"
                       class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3 font-bold text-slate-900 border border-slate-200 hover:bg-slate-50 transition"
                       id="heroCtaSecondary">
                        <i class="fa-solid fa-circle-play me-2"></i>
                        كيف تعمل الخدمة؟
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-soft">
                        <div class="text-sm text-slate-500" id="stat1Label">SLA</div>
                        <div class="text-xl font-extrabold" id="stat1Value">حسب العقد</div>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-soft">
                        <div class="text-sm text-slate-500" id="stat2Label">الفوترة</div>
                        <div class="text-xl font-extrabold" id="stat2Value">شهرية</div>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-soft">
                        <div class="text-sm text-slate-500" id="stat3Label">التقارير</div>
                        <div class="text-xl font-extrabold" id="stat3Value">PDF/CSV</div>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-soft">
                        <div class="text-sm text-slate-500" id="stat4Label">الدفع</div>
                        <div class="text-xl font-extrabold" id="stat4Value">عقد/تحويل</div>
                    </div>
                </div>
            </div>

            <!-- Hero Card (B2B Quote Builder) -->
            <div class="lg:col-span-6">
                <div class="rounded-3xl bg-white border border-slate-200 shadow-soft overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-lg font-extrabold" id="quickCardTitle">بناء عرض سعر للشركة</div>
                                <div class="text-sm text-slate-500" id="quickCardSubtitle">احسب تقديرًا سريعًا
                                    للأسطول خلال دقيقة
                                </div>
                            </div>
                            <span
                                    class="rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs font-bold border border-emerald-100"
                                    id="quickCardChip">
                                    B2B
                                </span>
                        </div>

                        <div class="mt-6 grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblFleet">حجم الأسطول</label>
                                <select id="fleetSize"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-sky-100">
                                    <option value="5">1–5 مركبات</option>
                                    <option value="15">6–15 مركبة</option>
                                    <option value="40">16–40 مركبة</option>
                                    <option value="80">41+ مركبة</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblContract">نوع
                                    التعاقد</label>
                                <select id="contractType"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">
                                    <option value="oneoff">طلب واحد (عند الحاجة)</option>
                                    <option value="monthly">عقد شهري (جدولة دورية)</option>
                                    <option value="quarterly">عقد ربع سنوي</option>
                                    <option value="annual">عقد سنوي</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblPackage">الخطة</label>
                                <select id="packageType"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-indigo-100">
                                    <option value="basic" data-price="129">Basic (زيت + فلتر)</option>
                                    <option value="plus" data-price="169">Plus (Premium + فحص سريع)</option>
                                    <option value="pro" data-price="219">Pro (تقارير + تتبع + SLA)</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblWindow">نافذة
                                    الزيارة</label>
                                <select id="visitWindow"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-slate-100">
                                    <option value="am">صباحًا (9–12)</option>
                                    <option value="pm">مساءً (1–5)</option>
                                    <option value="night">بعد الدوام (6–10)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="text-sm font-bold text-slate-700" id="lblCompanyLocation">موقع/مدينة
                                الخدمة</label>
                            <div class="mt-2 flex flex-col sm:flex-row gap-3">
                                <input id="city" placeholder="مثال: الرياض / جدة / الدمام ..."
                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"/>
                                <button id="btnDetect"
                                        class="sm:w-48 rounded-2xl bg-emerald-600 px-4 py-3 text-white font-bold hover:bg-emerald-700 transition shadow-soft">
                                    <i class="fa-solid fa-location-crosshairs me-2"></i>
                                    تحديد
                                </button>
                            </div>
                            <div
                                    class="mt-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                                <span class="font-bold" id="mapNoteTitle">خريطة (Placeholder):</span>
                                <span id="mapNoteText">لاحقًا نربط خرائط لاختيار مواقع الفروع + حساب رسوم التنقل
                                        حسب المسافة.</span>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl bg-slate-900 text-white p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm text-white/70" id="summaryTitle">ملخص العرض</div>
                                    <div class="mt-1 font-extrabold text-lg" id="summaryLine">—</div>
                                    <div class="mt-1 text-sm text-white/70" id="summaryMeta">—</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-sm text-white/70" id="priceLabel">تقدير شهري</div>
                                    <div class="text-2xl font-extrabold">
                                        <span id="totalPrice">0</span>
                                        <span class="text-sm font-bold" id="currency">SAR</span>
                                    </div>
                                    <div class="mt-1 text-xs text-white/60" id="priceNote">* تقدير تقريبي للعرض
                                        فقط
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <a href="#request" id="goRequest"
                                   class="rounded-2xl bg-white text-slate-900 px-4 py-3 font-extrabold hover:bg-slate-100 transition text-center">
                                    <i class="fa-solid fa-file-signature me-2"></i>
                                    إرسال طلب عرض سعر
                                </a>
                                <button id="toggleBilling"
                                        class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3 font-extrabold hover:bg-white/15 transition">
                                    الفوترة: شهرية (Net 30)
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-slate-500" id="note">
                            * هذه واجهة B2B تجريبية. لاحقًا: حسابات شركات، موافقات داخلية، فواتير ضريبية، وربط REST
                            API.
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-200 p-5 shadow-soft">
                        <div class="font-extrabold" id="trust1Title"><i
                                    class="fa-solid fa-people-group me-2 text-emerald-700"></i>فرق متعددة
                        </div>
                        <div class="text-sm text-slate-600 mt-1" id="trust1Desc">تغطية للفروع + جدولة لمجموعات
                            مركبات.
                        </div>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-200 p-5 shadow-soft">
                        <div class="font-extrabold" id="trust2Title"><i
                                    class="fa-solid fa-chart-simple me-2 text-sky-700"></i>تقارير أسطول
                        </div>
                        <div class="text-sm text-slate-600 mt-1" id="trust2Desc">تاريخ صيانة، تكاليف، مركبات
                            متأخرة، وتصدير.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Solutions (B2B) -->
<section id="solutions" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-end justify-between gap-6 flex-wrap">
        <div>
            <h2 class="text-3xl font-extrabold" id="servicesTitle">حلول للشركات</h2>
            <p class="mt-2 text-slate-600" id="servicesDesc">كل ما تحتاجه لإدارة صيانة الأسطول بشكل منظم وقابل
                للتوسع.</p>
        </div>
        <a href="#request" class="text-sm font-bold text-emerald-700 hover:text-emerald-800"
           id="servicesLink">اطلب عرض سعر →</a>
    </div>

    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <div class="h-12 w-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                <i class="fa-solid fa-calendar-check text-xl"></i>
            </div>
            <div class="mt-4 text-lg font-extrabold" id="srv1Title">جدولة دورية</div>
            <p class="mt-2 text-slate-600 text-sm" id="srv1Desc">صيانة حسب الكيلومترات/الوقت، مع تذكيرات
                للمركبات.</p>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <div class="h-12 w-12 rounded-2xl bg-sky-100 flex items-center justify-center text-sky-700">
                <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
            </div>
            <div class="mt-4 text-lg font-extrabold" id="srv2Title">فواتير شهرية مجمعة</div>
            <p class="mt-2 text-slate-600 text-sm" id="srv2Desc">فاتورة واحدة لكل فرع/شركة + ضريبة + تقارير
                PDF/CSV.</p>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <div class="h-12 w-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-700">
                <i class="fa-solid fa-diagram-project text-xl"></i>
            </div>
            <div class="mt-4 text-lg font-extrabold" id="srv3Title">Workflow موافقات</div>
            <p class="mt-2 text-slate-600 text-sm" id="srv3Desc">طلب → اعتماد مدير الأسطول → إسناد فريق → تنفيذ.
            </p>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <div class="h-12 w-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-700">
                <i class="fa-solid fa-truck-fast text-xl"></i>
            </div>
            <div class="mt-4 text-lg font-extrabold" id="srv4Title">فرق متنقلة للفروع</div>
            <p class="mt-2 text-slate-600 text-sm" id="srv4Desc">تنفيذ في مقر الشركة/المواقف/الورش المتعاقدة.</p>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <div class="h-12 w-12 rounded-2xl bg-fuchsia-100 flex items-center justify-center text-fuchsia-700">
                <i class="fa-solid fa-bell text-xl"></i>
            </div>
            <div class="mt-4 text-lg font-extrabold" id="srv5Title">إشعارات وتشغيل</div>
            <p class="mt-2 text-slate-600 text-sm" id="srv5Desc">تنبيهات للحالة + تذكير صيانة + سجل مركبة.</p>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-700">
                <i class="fa-solid fa-plug-circle-bolt text-xl"></i>
            </div>
            <div class="mt-4 text-lg font-extrabold" id="srv6Title">REST API للشركات</div>
            <p class="mt-2 text-slate-600 text-sm" id="srv6Desc">دمج مع ERP/CRM: أوامر، فواتير، حالات، وتتبّع.
            </p>
        </div>
    </div>
</section>

<!-- How it works -->
<section id="how" class="bg-white border-y border-slate-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-3xl font-extrabold" id="howTitle">كيف تعمل خدمة الشركات؟</h2>
        <p class="mt-2 text-slate-600" id="howDesc">3 خطوات: بيانات الشركة → تهيئة الأسطول → تشغيل وجدولة.</p>

        <div class="mt-10 grid lg:grid-cols-3 gap-6">
            <div class="rounded-3xl border border-slate-200 p-6 shadow-soft bg-slate-50">
                <div class="text-sm font-extrabold text-emerald-700" id="step1No">الخطوة 1</div>
                <div class="mt-2 text-xl font-extrabold" id="step1Title">حساب شركة + بيانات فوترة</div>
                <p class="mt-2 text-slate-600 text-sm" id="step1Desc">السجل التجاري/الضريبي، فروع، جهات اتصال،
                    وشروط الدفع.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 p-6 shadow-soft bg-slate-50">
                <div class="text-sm font-extrabold text-sky-700" id="step2No">الخطوة 2</div>
                <div class="mt-2 text-xl font-extrabold" id="step2Title">إضافة المركبات والجدولة</div>
                <p class="mt-2 text-slate-600 text-sm" id="step2Desc">VIN/لوحات، أنواع الزيوت، فترات صيانة،
                    وموافقات داخلية.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 p-6 shadow-soft bg-slate-50">
                <div class="text-sm font-extrabold text-indigo-700" id="step3No">الخطوة 3</div>
                <div class="mt-2 text-xl font-extrabold" id="step3Title">إسناد الفرق + تقارير</div>
                <p class="mt-2 text-slate-600 text-sm" id="step3Desc">إسناد تلقائي حسب المنطقة، تحديث الحالة،
                    وفاتورة مجمعة.</p>
            </div>
        </div>
    </div>
</section>

<!-- Plans -->
<section id="plans" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-end justify-between gap-6 flex-wrap">
        <div>
            <h2 class="text-3xl font-extrabold" id="pricingTitle">خطط مخصصة للشركات</h2>
            <p class="mt-2 text-slate-600" id="pricingDesc">خطط تشغيل + فوترة + تقارير (أسعار تجريبية للعرض).</p>
        </div>
    </div>

    <div class="mt-8 grid lg:grid-cols-3 gap-6">
        <div class="rounded-3xl bg-white border border-slate-200 p-7 shadow-soft">
            <div class="text-sm text-slate-500" id="plan1Tag">للشركات الصغيرة</div>
            <div class="mt-2 text-2xl font-extrabold" id="plan1Title">Starter</div>
            <div class="mt-4 text-4xl font-extrabold">129 <span class="text-base font-bold text-slate-500">/
                        مركبة</span></div>
            <ul class="mt-6 space-y-3 text-sm text-slate-700">
                <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>زيت + فلتر</li>
                <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>جدولة بسيطة</li>
                <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>فاتورة شهرية</li>
            </ul>
        </div>

        <div
                class="rounded-3xl bg-slate-900 text-white border border-slate-800 p-7 shadow-soft relative overflow-hidden">
            <div class="absolute -top-12 -end-12 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="text-sm text-white/70" id="plan2Tag">الأكثر شيوعًا</div>
            <div class="mt-2 text-2xl font-extrabold" id="plan2Title">Growth</div>
            <div class="mt-4 text-4xl font-extrabold">169 <span class="text-base font-bold text-white/70">/
                        مركبة</span></div>
            <ul class="mt-6 space-y-3 text-sm text-white/90">
                <li><i class="fa-solid fa-check text-emerald-300 me-2"></i>Premium + فحص سريع</li>
                <li><i class="fa-solid fa-check text-emerald-300 me-2"></i>موافقات + أدوار</li>
                <li><i class="fa-solid fa-check text-emerald-300 me-2"></i>تقارير PDF/CSV</li>
            </ul>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 p-7 shadow-soft">
            <div class="text-sm text-slate-500" id="plan3Tag">مؤسسي</div>
            <div class="mt-2 text-2xl font-extrabold" id="plan3Title">Enterprise</div>
            <div class="mt-4 text-4xl font-extrabold">اتصل بنا</div>
            <ul class="mt-6 space-y-3 text-sm text-slate-700">
                <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>SLA مخصص + فرق متعددة</li>
                <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>REST API/Webhooks</li>
                <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>فواتير حسب الفروع + PO</li>
            </ul>
        </div>
    </div>
</section>

<!-- Request Quote / Onboarding -->
<section id="request" class="bg-white border-y border-slate-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid lg:grid-cols-12 gap-10 items-start">
            <div class="lg:col-span-5">
                <h2 class="text-3xl font-extrabold" id="bookingTitle">طلب عرض سعر للشركات</h2>
                <p class="mt-2 text-slate-600" id="bookingDesc">نموذج B2B جاهز للربط مع REST API + لوحة تحكم
                    لاحقًا.</p>

                <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <div class="text-sm font-extrabold text-slate-900" id="apiHintTitle">ملاحظة تقنية</div>
                    <p class="mt-2 text-sm text-slate-600" id="apiHintDesc">
                        لاحقًا نربط النموذج مع API مثل:
                        <span class="font-bold">/api/companies</span> و <span class="font-bold">/api/fleets</span>
                        و
                        <span class="font-bold">/api/work-orders</span> و <span
                                class="font-bold">/api/invoices</span>.
                    </p>
                </div>

                <div class="mt-6 rounded-3xl bg-slate-900 text-white p-6 shadow-soft">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm text-white/70" id="bookingSummaryTitle">ملخص الطلب</div>
                            <div class="mt-1 font-extrabold text-lg" id="bookingSummaryLine">—</div>
                            <div class="mt-1 text-sm text-white/70" id="bookingSummaryAddress">—</div>
                        </div>
                        <div class="text-end">
                            <div class="text-sm text-white/70" id="bookingTotalLabel">تقدير</div>
                            <div class="text-2xl font-extrabold">
                                <span id="bookingTotal">0</span> <span class="text-sm font-bold">SAR</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-white/80" id="bookingPayMode">الفوترة: شهرية (Net 30)</div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <form id="requestForm"
                      class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 sm:p-8">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblCompany">اسم الشركة</label>
                            <input id="company"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"
                                   placeholder="مثال: شركة النقل السريع"/>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblManager">اسم المسؤول</label>
                            <input id="manager"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-sky-100"
                                   placeholder="مدير الأسطول / المشتريات"/>
                        </div>
                    </div>

                    <div class="mt-4 grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblEmail">البريد
                                الإلكتروني</label>
                            <input id="email"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-slate-100"
                                   placeholder="name@company.com"/>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblPhone">رقم الجوال</label>
                            <input id="phone"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-slate-100"
                                   placeholder="05xxxxxxxx"/>
                        </div>
                    </div>

                    <div class="mt-4 grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblCR">السجل التجاري
                                (اختياري)</label>
                            <input id="cr"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"
                                   placeholder="CR / رقم المنشأة"/>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblVAT">الرقم الضريبي
                                (اختياري)</label>
                            <input id="vat"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"
                                   placeholder="VAT Number"/>
                        </div>
                    </div>

                    <div class="mt-4 grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblFleet2">عدد المركبات</label>
                            <select id="fleetSize2"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-sky-100">
                                <option value="5">1–5</option>
                                <option value="15">6–15</option>
                                <option value="40">16–40</option>
                                <option value="80">41+</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblBranchCount">عدد الفروع</label>
                            <select id="branches"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-slate-100">
                                <option value="1">فرع واحد</option>
                                <option value="2">2–3 فروع</option>
                                <option value="4">4+ فروع</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblPackage2">الخطة
                                المطلوبة</label>
                            <select id="packageType2"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-indigo-100">
                                <option value="basic" data-price="129">Starter (Basic)</option>
                                <option value="plus" data-price="169">Growth (Plus)</option>
                                <option value="pro" data-price="219">Enterprise (Pro)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-700" id="lblContract2">التعاقد</label>
                            <select id="contractType2"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">
                                <option value="oneoff">طلب واحد</option>
                                <option value="monthly">عقد شهري</option>
                                <option value="quarterly">ربع سنوي</option>
                                <option value="annual">سنوي</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-bold text-slate-700" id="lblAddress2">المدينة/العنوان
                            الرئيسي</label>
                        <textarea id="address2" rows="3"
                                  class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"
                                  placeholder="المدينة + الحي + الشارع + ملاحظات للوصول / أو قائمة فروع مختصرة"></textarea>
                    </div>

                    <div class="mt-4 grid sm:grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="text-sm font-extrabold" id="billingTitle">الفوترة والدفع</div>
                            <div class="mt-3 flex flex-col gap-2 text-sm">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="billing" value="monthly" checked
                                           class="h-4 w-4">
                                    <span id="billMonthly">فاتورة شهرية (Net 30)</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="billing" value="po" class="h-4 w-4">
                                    <span id="billPO">أوامر شراء (PO) + تحويل</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="billing" value="cod" class="h-4 w-4">
                                    <span id="billCOD">دفع ميداني (لطلبات طارئة)</span>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                            <div class="text-sm font-extrabold" id="policyTitle">ملاحظات تشغيل</div>
                            <p class="mt-2 text-sm text-slate-600" id="policyText">
                                يمكن إضافة: رسوم تنقل حسب المسافة، SLA حسب المناطق، حدود موافقات، ومستخدمين متعددين
                                للشركة.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button type="submit"
                                class="w-full rounded-2xl bg-emerald-600 px-6 py-3 text-white font-extrabold hover:bg-emerald-700 transition shadow-soft"
                                id="btnSubmit">
                            <i class="fa-solid fa-paper-plane me-2"></i>
                            إرسال الطلب (Demo)
                        </button>
                        <button type="button" id="btnPreview"
                                class="w-full rounded-2xl bg-white border border-slate-200 px-6 py-3 font-extrabold hover:bg-slate-50 transition">
                            <i class="fa-regular fa-eye me-2"></i>
                            معاينة الملخص
                        </button>
                    </div>

                    <div id="toast"
                         class="hidden mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        <span id="toastSuccess"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section id="faq" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
    <h2 class="text-3xl font-extrabold" id="faqTitle">أسئلة شائعة (شركات)</h2>
    <div class="mt-8 grid lg:grid-cols-2 gap-6">
        <details class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <summary class="cursor-pointer font-extrabold" id="q1">هل توجد فواتير ضريبية وفواتير مجمعة
                للفروع؟
            </summary>
            <p class="mt-3 text-slate-600 text-sm" id="a1">نعم، يمكن إصدار فواتير حسب الفرع أو فاتورة مجمعة
                + تقارير شهرية.</p>
        </details>
        <details class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <summary class="cursor-pointer font-extrabold" id="q2">هل تدعمون أوامر شراء PO وشروط Net 30؟
            </summary>
            <p class="mt-3 text-slate-600 text-sm" id="a2">نعم، يمكن ضبط PO/تحويل بنكي وشروط دفع حسب العقد.
            </p>
        </details>
        <details class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <summary class="cursor-pointer font-extrabold" id="q3">هل يوجد نظام موافقات ومدراء متعددين؟
            </summary>
            <p class="mt-3 text-slate-600 text-sm" id="a3">نعم، يمكن إضافة أدوار وصلاحيات (مدير
                أسطول/مشتريات/محاسب) مع موافقات.</p>
        </details>
        <details class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
            <summary class="cursor-pointer font-extrabold" id="q4">هل يوجد API للربط مع ERP/CRM؟</summary>
            <p class="mt-3 text-slate-600 text-sm" id="a4">نعم، REST API + Webhooks لتحديث الحالات وإرسال
                الفواتير والتقارير.</p>
        </details>
    </div>
</section>

<!-- Footer -->
<footer class="bg-slate-900 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-3">
                    @if($siteLogoUrl ?? null)
                        <img src="{{ $siteLogoUrl }}" alt="" class="h-10 w-10 rounded-full object-cover shadow-soft">
                    @else
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-sky-500 shadow-soft"></div>
                    @endif
                    <div>
                        <div class="text-lg font-extrabold">{{ $siteName ?? 'SERV.X' }}</div>
                        <div class="text-xs text-white/60" id="footerTag">حلول صيانة أساطيل للشركات</div>
                    </div>
                </div>
                <p class="mt-4 text-sm text-white/70" id="footerDesc">
                    واجهة B2B تجريبية — جاهزة للربط مع Backend + REST API + لوحة تحكم.
                </p>
            </div>

            <div>
                <div class="font-extrabold mb-3" id="footerLinks">روابط</div>
                <ul class="space-y-2 text-sm text-white/70">
                    <li><a class="hover:text-white" href="#solutions" id="fServices">الحلول</a></li>
                    <li><a class="hover:text-white" href="#how" id="fHow">كيف تعمل</a></li>
                    <li><a class="hover:text-white" href="#request" id="fBooking">طلب عرض سعر</a></li>
                </ul>
            </div>

            <div>
                <div class="font-extrabold mb-3" id="footerContact">تواصل</div>
                <div class="text-sm text-white/70 space-y-2">
                    @php
    $waNumber = preg_replace('/[^0-9]/', '', $contactWhatsapp ?? '');
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '966' . substr($waNumber, 1);
    } elseif (!str_starts_with($waNumber, '966') && strlen($waNumber) <= 10) {
        $waNumber = '966' . ltrim($waNumber, '0');
    }
@endphp
                    <div><i class="fa-brands fa-whatsapp me-2"></i>WhatsApp: <a href="https://wa.me/{{ $waNumber ?: '966512345678' }}" target="_blank" rel="noopener" class="font-bold hover:text-white transition">{{ $contactWhatsapp ?? '05xxxxxxxx' }}</a></div>
                    <div><i class="fa-regular fa-envelope me-2"></i>Email: <a href="mailto:{{ $contactEmail ?? 'b2b@oilgo.com' }}" class="font-bold hover:text-white transition">{{ $contactEmail ?? 'b2b@oilgo.com' }}</a></div>
                    <div class="text-xs text-white/50" id="footerNote">* بيانات تجريبية للعرض</div>
                </div>
            </div>
        </div>

        <div
                class="mt-10 pt-6 border-t border-white/10 text-xs text-white/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>© <span id="year"></span> {{ $siteName ?? 'SERV.X' }}</div>
            
        </div>
        <div class="mt-4 text-center text-xs text-white/60" id="footerCredits">
            <span id="footerCreditsText">Designed and developed by</span>
            <a href="mailto:raghebammar201@gmail.com" class="text-white/80 hover:text-white underline font-semibold transition">Ragheb Aloqab</a>
            <span id="footerCreditsAnd"> and </span>
            <a href="mailto:abdullahskander8@gmail.com" class="text-white/80 hover:text-white underline font-semibold transition">Abdullah Eskander</a>
        </div>
    </div>
</footer>

<!-- JS: translations from lang files (ar/en) -->
@php
    $currentLocale = app()->getLocale();
    app()->setLocale('ar');
    $translationsAr = trans('index');
    app()->setLocale('en');
    $translationsEn = trans('index');
    app()->setLocale($currentLocale);
@endphp
<script>
    window.translations = {
        ar: @json($translationsAr),
        en: @json($translationsEn)
    };

    // ------- Helpers -------
    const $ = (id) => document.getElementById(id);
    const getOptPrice = (selectEl) => {
        const opt = selectEl.options[selectEl.selectedIndex];
        return Number(opt.dataset.price || 0);
    };

    const state = {
        lang: "{{ app()->getLocale() }}",
        dir: "{{ session('ui.dir', 'rtl') }}",
        billing: "monthly" // monthly | po | cod
    };

    // خصم بسيط حسب حجم الأسطول (Demo)
    function fleetDiscountFactor(fleet) {
        const n = Number(fleet || 0);
        if (n >= 80) return 0.82; // 18%
        if (n >= 40) return 0.88; // 12%
        if (n >= 15) return 0.93; // 7%
        return 1.0; // no discount
    }

    // معامل حسب نوع العقد (Demo)
    function contractFactor(contractType) {
        switch (contractType) {
            case "annual":
                return 0.90; // 10%
            case "quarterly":
                return 0.95; // 5%
            case "monthly":
                return 0.98; // 2%
            default:
                return 1.0; // oneoff
        }
    }

    function calcEstimateMonthly() {
        const fleet = Number($("fleetSize").value);
        const perVehicle = getOptPrice($("packageType"));
        const discount = fleetDiscountFactor(fleet);
        const cf = contractFactor($("contractType").value);

        // Demo: تقدير شهري = عدد مركبات * سعر/مركبة * خصم حجم الأسطول * خصم العقد
        const est = Math.round(fleet * perVehicle * discount * cf);
        return est;
    }

    function billingText() {
        const t = window.translations[state.lang] || window.translations.ar;
        if (state.billing === "monthly") return t.billingMonthly || t.toggleBilling;
        if (state.billing === "po") return t.billingPo;
        return t.billingCod;
    }

    function updateHeroSummary() {
        const fleetText = $("fleetSize").options[$("fleetSize").selectedIndex].text;
        const contractText = $("contractType").options[$("contractType").selectedIndex].text;
        const pkgText = $("packageType").options[$("packageType").selectedIndex].text;

        const t = window.translations[state.lang] || window.translations.ar;
        const city = $("city").value?.trim() ? $("city").value.trim() : (t.cityPlaceholder || "—");
        const windowText = $("visitWindow").options[$("visitWindow").selectedIndex].text;

        $("summaryLine").textContent = `${pkgText} • ${fleetText}`;
        $("summaryMeta").textContent = `${contractText} • ${windowText} • ${city}`;
        $("totalPrice").textContent = calcEstimateMonthly();

        // left panel summary sync
        updateRequestSummary();
    }

    function syncRequestFromHero() {
        $("fleetSize2").value = $("fleetSize").value;
        $("contractType2").value = $("contractType").value;
        $("packageType2").value = $("packageType").value;

        if ($("city").value.trim() && !$("address2").value.trim()) {
            $("address2").value = $("city").value.trim();
        }
        updateRequestSummary();
    }

    function calcRequestEstimate() {
        const fleet = Number($("fleetSize2").value);
        const perVehicle = getOptPrice($("packageType2"));
        const discount = fleetDiscountFactor(fleet);
        const cf = contractFactor($("contractType2").value);
        return Math.round(fleet * perVehicle * discount * cf);
    }

    function updateRequestSummary() {
        const company = $("company").value?.trim() ? $("company").value.trim() : (state.lang === "ar" ? "—" : "—");
        const fleetText = $("fleetSize2").options[$("fleetSize2").selectedIndex].text;
        const pkgText = $("packageType2").options[$("packageType2").selectedIndex].text;

        $("bookingTotal").textContent = calcRequestEstimate();
        $("bookingSummaryLine").textContent = `${company} • ${fleetText} • ${pkgText}`;
        $("bookingSummaryAddress").textContent = $("address2").value?.trim() ? $("address2").value.trim() : "—";
        $("bookingPayMode").textContent = billingText();

        $("toggleBilling").textContent = billingText();
    }

    function applyLang(lang) {
        const dict = window.translations && window.translations[lang] ? window.translations[lang] : {};
        Object.keys(dict).forEach((key) => {
            const el = document.getElementById(key);
            if (el) el.textContent = dict[key];
            document.querySelectorAll("[data-i18n=\"" + key + "\"]").forEach(function (node) {
                node.textContent = dict[key];
            });
        });
        state.lang = lang;
        if ($("btnLang")) $("btnLang").textContent = lang === "ar" ? "EN" : "AR";
        updateHeroSummary();
        updateRequestSummary();
    }

    function applyDir(dir) {
        document.documentElement.setAttribute("dir", dir);
        document.documentElement.setAttribute("lang", state.lang === "ar" ? "ar" : "en");
        state.dir = dir;
        $("btnDir").textContent = dir === "rtl" ? "LTR" : "RTL";
    }

    // ------- Events -------
    $("btnMobile").addEventListener("click", () => $("mobileMenu").classList.toggle("hidden"));

    document.querySelectorAll("#mobileMenu a").forEach(a => {
        a.addEventListener("click", () => $("mobileMenu").classList.add("hidden"));
    });

    // User menu dropdown (hidden by default, toggle on click, close on outside click)
    (function () {
        const btn = document.getElementById("userMenuBtn");
        const dropdown = document.getElementById("userDropdown");
        if (!btn || !dropdown) return;
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdown.classList.toggle("hidden");
            btn.setAttribute("aria-expanded", dropdown.classList.contains("hidden") ? "false" : "true");
        });
        document.addEventListener("click", function () {
            dropdown.classList.add("hidden");
            btn.setAttribute("aria-expanded", "false");
        });
    })();

    // Language menu dropdown
    (function () {
        const btn = document.getElementById("langMenuBtn");
        const dropdown = document.getElementById("langDropdown");
        if (!btn || !dropdown) return;
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdown.classList.toggle("hidden");
            btn.setAttribute("aria-expanded", dropdown.classList.contains("hidden") ? "false" : "true");
        });
        document.addEventListener("click", function () {
            dropdown.classList.add("hidden");
            btn.setAttribute("aria-expanded", "false");
        });
    })();

    ["fleetSize", "contractType", "packageType", "visitWindow"].forEach(id => {
        $(id).addEventListener("change", () => {
            updateHeroSummary();
            syncRequestFromHero();
        });
    });

    $("city").addEventListener("input", updateHeroSummary);

    $("btnDetect").addEventListener("click", () => {
        const sample = state.lang === "ar" ? "الرياض — مقر الشركة" : "Riyadh — HQ";
        $("city").value = sample;
        if (!$("address2").value.trim()) $("address2").value = sample;
        updateHeroSummary();
        updateRequestSummary();
    });

    $("toggleBilling").addEventListener("click", () => {
        state.billing = (state.billing === "monthly") ? "po" : (state.billing === "po" ? "cod" : "monthly");

        const radios = document.querySelectorAll("input[name='billing']");
        radios.forEach(r => r.checked = (r.value === state.billing));

        updateRequestSummary();
    });

    // Request form interactions
    ["company", "fleetSize2", "packageType2", "contractType2", "address2", "branches"].forEach(id => {
        $(id).addEventListener("change", updateRequestSummary);
        $(id).addEventListener("input", updateRequestSummary);
    });

    document.querySelectorAll("input[name='billing']").forEach(r => {
        r.addEventListener("change", (e) => {
            state.billing = e.target.value;
            updateRequestSummary();
        });
    });

    $("btnPreview").addEventListener("click", () => {
        updateRequestSummary();
        $("toast").classList.remove("hidden");
        setTimeout(() => $("toast").classList.add("hidden"), 2200);
    });

    $("requestForm").addEventListener("submit", (e) => {
        e.preventDefault();
        updateRequestSummary();
        $("toast").classList.remove("hidden");
        setTimeout(() => $("toast").classList.add("hidden"), 2500);
    });

    $("btnLang").addEventListener("click", () => {
        const next = state.lang === "ar" ? "en" : "ar";
        const nextDir = next === "ar" ? "rtl" : "ltr";
        fetch("{{ route('set-locale') }}?lang=" + next, { headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" } })
            .then(r => r.json().catch(() => ({})))
            .then(() => { applyLang(next); applyDir(nextDir); });
    });

    $("btnDir").addEventListener("click", () => {
        const next = state.dir === "rtl" ? "ltr" : "rtl";
        fetch("{{ route('set-locale') }}?dir=" + next, { headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" } })
            .then(r => r.json().catch(() => ({})))
            .then(() => { applyDir(next); });
    });

    // init from session (lang/dir already on <html>; sync UI and translations)
    $("year").textContent = new Date().getFullYear();
    applyLang(state.lang);
    applyDir(state.dir);
    updateHeroSummary();
    syncRequestFromHero();
    updateRequestSummary();
</script>

</body>

</html>
