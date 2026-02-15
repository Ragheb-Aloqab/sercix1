<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ session('ui.dir', 'rtl') }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ $siteName ?? 'SERV.X' }} — {{ __('index.pageTitle') }}</title>
    @if ($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div
                    class="flex items-center justify-center h-12 w-12 rounded-full shadow-soft overflow-hidden shrink-0 border border-slate-100">
                    @if ($siteLogoUrl ?? null)
                        <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'SERV.X' }}"
                            class="h-full w-full object-cover" />
                    @else
                        <div
                            class="h-full w-full bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center text-white font-black text-lg">
                            {{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-extrabold leading-5 truncate group-hover:text-slate-900" id="brandName">
                        {{ $siteName ?? 'SERV.X' }}</div>
                    <div class="text-xs text-slate-500 truncate" id="brandTag">{{ __('index.brandTag') }}</div>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="#solutions" class="text-slate-700 hover:text-slate-900"
                    id="navServices">{{ __('index.navServices') }}</a>
                <a href="#how" class="text-slate-700 hover:text-slate-900"
                    id="navHow">{{ __('index.navHow') }}</a>
                <a href="#plans" class="text-slate-700 hover:text-slate-900"
                    id="navPricing">{{ __('index.navPricing') }}</a>
                <a href="#faq" class="text-slate-700 hover:text-slate-900"
                    id="navFaq">{{ __('index.navFaq') }}</a>

                <!-- Language Menu -->
                @php $currentLocale = app()->getLocale(); @endphp
                <div class="relative" id="langMenuWrap">
                    <button type="button" id="langMenuBtn" aria-expanded="false" aria-haspopup="true"
                        class="inline-flex items-center gap-2 text-slate-700 hover:text-slate-900">
                        <i class="fa-solid fa-globe"></i>
                        <span>{{ $currentLocale === 'ar' ? __('index.langAr') : __('index.langEn') }}</span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>
                    <div id="langDropdown"
                        class="hidden absolute end-0 mt-2 w-40 bg-white rounded-xl shadow-lg py-2 text-sm z-50 border border-slate-200">
                        <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
                            class="block px-4 py-2 hover:bg-slate-100 {{ $currentLocale === 'ar' ? 'bg-slate-50 font-semibold' : '' }}">
                            {{ __('index.langAr') }}
                        </a>
                        <a href="{{ route('set-locale', ['lang' => 'en']) }}"
                            class="block px-4 py-2 hover:bg-slate-100 {{ $currentLocale === 'en' ? 'bg-slate-50 font-semibold' : '' }}">
                            {{ __('index.langEn') }}
                        </a>
                    </div>
                </div>

                <!-- User Authentication -->
                @php
                    $user = null;
                    $dashboardRoute = '#';
                    $logoutRoute = route('logout');
                    if (Auth::guard('company')->check()) {
                        $user = Auth::guard('company')->user();
                        $dashboardRoute = route('company.dashboard');
                    } elseif (session()->has('driver_phone')) {
                        $user = (object) ['name' => __('driver.driver'), 'is_driver' => true];
                        $dashboardRoute = route('driver.dashboard');
                        $logoutRoute = route('driver.logout');
                    } elseif (Auth::guard('web')->check()) {
                        $user = Auth::guard('web')->user();
                        $dashboardRoute =
                            ($user->role ?? null) === 'technician' ? route('tech.dashboard') : route('admin.dashboard');
                    }
                @endphp

                @if ($user)
                    <!-- User Menu -->
                    <div class="relative" id="userMenuWrap">
                        <button type="button" id="userMenuBtn" aria-expanded="false" aria-haspopup="true"
                            class="inline-flex items-center gap-2 text-slate-700 hover:text-slate-900 font-extrabold">
                            <i class="fa-solid fa-user"></i>
                            <span>{{ $user->name ?? $user->company_name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>

                        <div id="userDropdown"
                            class="hidden absolute end-0 mt-2 w-48 bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-50 rounded-xl shadow-lg py-2 text-sm z-50 border border-slate-200 dark:border-slate-700">
                            <a href="{{ $dashboardRoute }}" data-i18n="navDashboard"
                                class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700">{{ __('index.navDashboard') }}</a>
                            <form method="POST" action="{{ $logoutRoute }}">
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
                        {{ __('index.langAr') }}
                    </a>
                    <a href="{{ route('set-locale', ['lang' => 'en']) }}"
                        class="py-1 px-3 rounded-lg {{ $currentLocale === 'en' ? 'bg-slate-900 text-white font-semibold' : 'bg-slate-100 hover:bg-slate-200' }}">
                        {{ __('index.langEn') }}
                    </a>
                </div>

                @if ($user)
                    <a href="{{ $dashboardRoute }}" class="py-2 font-extrabold text-slate-900"
                        data-i18n="navDashboard">{{ __('index.navDashboard') }}</a>
                    <form method="POST" action="{{ $logoutRoute }}">
                        @csrf
                        <button type="submit" class="w-full text-start py-2 font-extrabold text-slate-900"
                            data-i18n="navLogout">{{ __('index.navLogout') }}</button>
                    </form>
                @else
                    <a href="{{ route('sign-in.index') }}"
                        class="py-2 font-extrabold text-slate-900">{{ __('index.navLogin') }}</a>
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
                        <span class="text-slate-700" id="heroBadge">{{ __('index.heroBadge') }}</span>
                    </div>

                    <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        <span id="heroTitle1">{{ __('index.heroTitle1') }}</span>
                        <span class="block bg-gradient-to-l from-emerald-600 to-sky-600 bg-clip-text text-transparent"
                            id="heroTitle2">
                            {{ __('index.heroTitle2') }}
                        </span>
                    </h1>

                    <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed" id="heroDesc">
                        {{ __('index.heroDesc') }}
                    </p>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="#request"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-white font-bold hover:bg-slate-800 transition shadow-soft"
                            id="heroCtaPrimary">
                            <i class="fa-solid fa-file-signature me-2"></i>
                            {{ __('index.heroCtaPrimary') }}
                        </a>
                        <a href="#how"
                            class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3 font-bold text-slate-900 border border-slate-200 hover:bg-slate-50 transition"
                            id="heroCtaSecondary">
                            <i class="fa-solid fa-circle-play me-2"></i>
                            {{ __('index.heroCtaSecondary') }}
                        </a>
                    </div>

                    <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-soft">
                            <div class="text-sm text-slate-500" id="stat1Label">{{ __('index.stat1Label') }}</div>
                            <div class="text-xl font-extrabold" id="stat1Value">{{ __('index.stat1Value') }}</div>
                        </div>
                        <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-soft">
                            <div class="text-sm text-slate-500" id="stat2Label">{{ __('index.stat2Label') }}</div>
                            <div class="text-xl font-extrabold" id="stat2Value">{{ __('index.stat2Value') }}</div>
                        </div>
                        <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-soft">
                            <div class="text-sm text-slate-500" id="stat3Label">{{ __('index.stat3Label') }}</div>
                            <div class="text-xl font-extrabold" id="stat3Value">{{ __('index.stat3Value') }}</div>
                        </div>
                        <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-soft">
                            <div class="text-sm text-slate-500" id="stat4Label">{{ __('index.stat4Label') }}</div>
                            <div class="text-xl font-extrabold" id="stat4Value">{{ __('index.stat4Value') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Hero Card (B2B Quote Builder) -->
                <div class="lg:col-span-6">
                    <div class="rounded-3xl bg-white border border-slate-200 shadow-soft overflow-hidden">
                        <div class="p-6 sm:p-8">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-lg font-extrabold" id="quickCardTitle">{{ __('index.quickCardTitle') }}</div>
                                    <div class="text-sm text-slate-500" id="quickCardSubtitle">{{ __('index.quickCardSubtitle') }}</div>
                                </div>
                                <span
                                    class="rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs font-bold border border-emerald-100"
                                    id="quickCardChip">
                                    B2B
                                </span>
                            </div>

                            <div class="mt-6 grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-bold text-slate-700" id="lblFleet">{{ __('index.lblFleet') }}</label>
                                    <select id="fleetSize"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-sky-100">
                                        <option value="5">{{ __('index.fleet1_5') }}</option>
                                        <option value="15">{{ __('index.fleet6_15') }}</option>
                                        <option value="40">{{ __('index.fleet16_40') }}</option>
                                        <option value="80">{{ __('index.fleet41_plus') }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-slate-700" id="lblContract">{{ __('index.lblContract') }}</label>
                                    <select id="contractType"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">
                                        <option value="oneoff">{{ __('index.contractOneoff') }}</option>
                                        <option value="monthly">{{ __('index.contractMonthly') }}</option>
                                        <option value="quarterly">{{ __('index.contractQuarterly') }}</option>
                                        <option value="annual">{{ __('index.contractAnnual') }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-slate-700" id="lblPackage">{{ __('index.lblPackage') }}</label>
                                    <select id="packageType"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-indigo-100">
                                        <option value="basic" data-price="129">{{ __('index.pkgBasic') }}</option>
                                        <option value="plus" data-price="169">{{ __('index.pkgPlus') }}</option>
                                        <option value="pro" data-price="219">{{ __('index.pkgPro') }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-slate-700" id="lblWindow">{{ __('index.lblWindow') }}</label>
                                    <select id="visitWindow"
                                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-slate-100">
                                        <option value="am">{{ __('index.visitAm') }}</option>
                                        <option value="pm">{{ __('index.visitPm') }}</option>
                                        <option value="night">{{ __('index.visitNight') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-bold text-slate-700" id="lblCompanyLocation">{{ __('index.lblCompanyLocation') }}</label>
                                <div class="mt-2 flex flex-col sm:flex-row gap-3">
                                    <input id="city" placeholder="{{ __('index.cityPlaceholderHero') }}"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100" />
                                    <button id="btnDetect"
                                        class="sm:w-48 rounded-2xl bg-emerald-600 px-4 py-3 text-white font-bold hover:bg-emerald-700 transition shadow-soft">
                                        <i class="fa-solid fa-location-crosshairs me-2"></i>
                                        {{ __('index.btnDetect') }}
                                    </button>
                                </div>
                                <div
                                    class="mt-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                                    <span class="font-bold" id="mapNoteTitle">{{ __('index.mapNoteTitle') }}</span>
                                    <span id="mapNoteText">{{ __('index.mapNoteText') }}</span>
                                </div>
                            </div>

                            <div class="mt-6 rounded-2xl bg-slate-900 text-white p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-sm text-white/70" id="summaryTitle">{{ __('index.summaryTitle') }}</div>
                                        <div class="mt-1 font-extrabold text-lg" id="summaryLine">—</div>
                                        <div class="mt-1 text-sm text-white/70" id="summaryMeta">—</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-sm text-white/70" id="priceLabel">{{ __('index.priceLabel') }}</div>
                                        <div class="text-2xl font-extrabold">
                                            <span id="totalPrice">0</span>
                                            <span class="text-sm font-bold" id="currency">SAR</span>
                                        </div>
                                        <div class="mt-1 text-xs text-white/60" id="priceNote">{{ __('index.priceNote') }}</div>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <a href="#request" id="goRequest"
                                        class="rounded-2xl bg-white text-slate-900 px-4 py-3 font-extrabold hover:bg-slate-100 transition text-center">
                                        <i class="fa-solid fa-file-signature me-2"></i>
                                        {{ __('index.goRequest') }}
                                    </a>
                                    <button id="toggleBilling"
                                        class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3 font-extrabold hover:bg-white/15 transition">
                                        {{ __('index.toggleBilling') }}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 text-xs text-slate-500" id="note">{{ __('index.note') }}</div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="rounded-3xl bg-white border border-slate-200 p-5 shadow-soft">
                            <div class="font-extrabold" id="trust1Title"><i
                                    class="fa-solid fa-people-group me-2 text-emerald-700"></i>{{ __('index.trust1Title') }}</div>
                            <div class="text-sm text-slate-600 mt-1" id="trust1Desc">{{ __('index.trust1Desc') }}</div>
                        </div>
                        <div class="rounded-3xl bg-white border border-slate-200 p-5 shadow-soft">
                            <div class="font-extrabold" id="trust2Title"><i
                                    class="fa-solid fa-chart-simple me-2 text-sky-700"></i>{{ __('index.trust2Title') }}</div>
                            <div class="text-sm text-slate-600 mt-1" id="trust2Desc">{{ __('index.trust2Desc') }}</div>
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
                <h2 class="text-3xl font-extrabold" id="servicesTitle">{{ __('index.servicesTitle') }}</h2>
                <p class="mt-2 text-slate-600" id="servicesDesc">{{ __('index.servicesDesc') }}</p>
            </div>
            <a href="#request" class="text-sm font-bold text-emerald-700 hover:text-emerald-800"
                id="servicesLink">{{ __('index.servicesLink') }}</a>
        </div>

        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <div class="h-12 w-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i class="fa-solid fa-calendar-check text-xl"></i>
                </div>
                <div class="mt-4 text-lg font-extrabold" id="srv1Title">{{ __('index.srv1Title') }}</div>
                <p class="mt-2 text-slate-600 text-sm" id="srv1Desc">{{ __('index.srv1Desc') }}</p>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <div class="h-12 w-12 rounded-2xl bg-sky-100 flex items-center justify-center text-sky-700">
                    <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                </div>
                <div class="mt-4 text-lg font-extrabold" id="srv2Title">{{ __('index.srv2Title') }}</div>
                <p class="mt-2 text-slate-600 text-sm" id="srv2Desc">{{ __('index.srv2Desc') }}</p>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <div class="h-12 w-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-700">
                    <i class="fa-solid fa-diagram-project text-xl"></i>
                </div>
                <div class="mt-4 text-lg font-extrabold" id="srv3Title">{{ __('index.srv3Title') }}</div>
                <p class="mt-2 text-slate-600 text-sm" id="srv3Desc">{{ __('index.srv3Desc') }}</p>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <div class="h-12 w-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-700">
                    <i class="fa-solid fa-truck-fast text-xl"></i>
                </div>
                <div class="mt-4 text-lg font-extrabold" id="srv4Title">{{ __('index.srv4Title') }}</div>
                <p class="mt-2 text-slate-600 text-sm" id="srv4Desc">{{ __('index.srv4Desc') }}</p>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <div class="h-12 w-12 rounded-2xl bg-fuchsia-100 flex items-center justify-center text-fuchsia-700">
                    <i class="fa-solid fa-bell text-xl"></i>
                </div>
                <div class="mt-4 text-lg font-extrabold" id="srv5Title">{{ __('index.srv5Title') }}</div>
                <p class="mt-2 text-slate-600 text-sm" id="srv5Desc">{{ __('index.srv5Desc') }}</p>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-700">
                    <i class="fa-solid fa-plug-circle-bolt text-xl"></i>
                </div>
                <div class="mt-4 text-lg font-extrabold" id="srv6Title">{{ __('index.srv6Title') }}</div>
                <p class="mt-2 text-slate-600 text-sm" id="srv6Desc">{{ __('index.srv6Desc') }}</p>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section id="how" class="bg-white border-y border-slate-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
            <h2 class="text-3xl font-extrabold" id="howTitle">{{ __('index.howTitle') }}</h2>
            <p class="mt-2 text-slate-600" id="howDesc">{{ __('index.howDesc') }}</p>

            <div class="mt-10 grid lg:grid-cols-3 gap-6">
                <div class="rounded-3xl border border-slate-200 p-6 shadow-soft bg-slate-50">
                    <div class="text-sm font-extrabold text-emerald-700" id="step1No">{{ __('index.step1No') }}</div>
                    <div class="mt-2 text-xl font-extrabold" id="step1Title">{{ __('index.step1Title') }}</div>
                    <p class="mt-2 text-slate-600 text-sm" id="step1Desc">{{ __('index.step1Desc') }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 p-6 shadow-soft bg-slate-50">
                    <div class="text-sm font-extrabold text-sky-700" id="step2No">{{ __('index.step2No') }}</div>
                    <div class="mt-2 text-xl font-extrabold" id="step2Title">{{ __('index.step2Title') }}</div>
                    <p class="mt-2 text-slate-600 text-sm" id="step2Desc">{{ __('index.step2Desc') }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 p-6 shadow-soft bg-slate-50">
                    <div class="text-sm font-extrabold text-indigo-700" id="step3No">{{ __('index.step3No') }}</div>
                    <div class="mt-2 text-xl font-extrabold" id="step3Title">{{ __('index.step3Title') }}</div>
                    <p class="mt-2 text-slate-600 text-sm" id="step3Desc">{{ __('index.step3Desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Plans -->
    <section id="plans" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex items-end justify-between gap-6 flex-wrap">
            <div>
                <h2 class="text-3xl font-extrabold" id="pricingTitle">{{ __('index.pricingTitle') }}</h2>
                <p class="mt-2 text-slate-600" id="pricingDesc">{{ __('index.pricingDesc') }}</p>
            </div>
        </div>

        <div class="mt-8 grid lg:grid-cols-3 gap-6">
            <div class="rounded-3xl bg-white border border-slate-200 p-7 shadow-soft">
                <div class="text-sm text-slate-500" id="plan1Tag">{{ __('index.plan1Tag') }}</div>
                <div class="mt-2 text-2xl font-extrabold" id="plan1Title">{{ __('index.plan1Title') }}</div>
                <div class="mt-4 text-4xl font-extrabold">129 <span class="text-base font-bold text-slate-500">/ {{ __('index.perVehicle') }}</span></div>
                <ul class="mt-6 space-y-3 text-sm text-slate-700">
                    <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>{{ __('index.plan1Item1') }}</li>
                    <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>{{ __('index.plan1Item2') }}</li>
                    <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>{{ __('index.plan1Item3') }}</li>
                </ul>
            </div>

            <div
                class="rounded-3xl bg-slate-900 text-white border border-slate-800 p-7 shadow-soft relative overflow-hidden">
                <div class="absolute -top-12 -end-12 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="text-sm text-white/70" id="plan2Tag">{{ __('index.plan2Tag') }}</div>
                <div class="mt-2 text-2xl font-extrabold" id="plan2Title">{{ __('index.plan2Title') }}</div>
                <div class="mt-4 text-4xl font-extrabold">169 <span class="text-base font-bold text-white/70">/ {{ __('index.perVehicle') }}</span></div>
                <ul class="mt-6 space-y-3 text-sm text-white/90">
                    <li><i class="fa-solid fa-check text-emerald-300 me-2"></i>{{ __('index.plan2Item1') }}</li>
                    <li><i class="fa-solid fa-check text-emerald-300 me-2"></i>{{ __('index.plan2Item2') }}</li>
                    <li><i class="fa-solid fa-check text-emerald-300 me-2"></i>{{ __('index.plan2Item3') }}</li>
                </ul>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-7 shadow-soft">
                <div class="text-sm text-slate-500" id="plan3Tag">{{ __('index.plan3Tag') }}</div>
                <div class="mt-2 text-2xl font-extrabold" id="plan3Title">{{ __('index.plan3Title') }}</div>
                <div class="mt-4 text-4xl font-extrabold">{{ __('index.contactUs') }}</div>
                <ul class="mt-6 space-y-3 text-sm text-slate-700">
                    <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>{{ __('index.plan3Item1') }}</li>
                    <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>{{ __('index.plan3Item2') }}</li>
                    <li><i class="fa-solid fa-check text-emerald-700 me-2"></i>{{ __('index.plan3Item3') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Request Quote / Onboarding -->
    <section id="request" class="bg-white border-y border-slate-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid lg:grid-cols-12 gap-10 items-start">
                <div class="lg:col-span-5">
                    <h2 class="text-3xl font-extrabold" id="bookingTitle">{{ __('index.bookingTitle') }}</h2>
                    <p class="mt-2 text-slate-600" id="bookingDesc">{{ __('index.bookingDesc') }}</p>

                    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-6">
                        <div class="text-sm font-extrabold text-slate-900" id="apiHintTitle">{{ __('index.apiHintTitle') }}</div>
                        <p class="mt-2 text-sm text-slate-600" id="apiHintDesc">
                            {{ __('index.apiHintDesc') }}
                            
                        </p>
                    </div>

                    <div class="mt-6 rounded-3xl bg-slate-900 text-white p-6 shadow-soft">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm text-white/70" id="bookingSummaryTitle">{{ __('index.bookingSummaryTitle') }}</div>
                                <div class="mt-1 font-extrabold text-lg" id="bookingSummaryLine">—</div>
                                <div class="mt-1 text-sm text-white/70" id="bookingSummaryAddress">—</div>
                            </div>
                            <div class="text-end">
                                <div class="text-sm text-white/70" id="bookingTotalLabel">{{ __('index.bookingTotalLabel') }}</div>
                                <div class="text-2xl font-extrabold">
                                    <span id="bookingTotal">0</span> <span class="text-sm font-bold">SAR</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-white/80" id="bookingPayMode">{{ __('index.billingMonthly') }}</div>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <form id="requestForm"
                        class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 sm:p-8">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblCompany">{{ __('index.lblCompany') }}</label>
                                <input id="company"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"
                                    placeholder="{{ __('index.placeholderCompany') }}"/>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblManager">{{ __('index.lblManager') }}</label>
                                <input id="manager"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-sky-100"
                                    placeholder="{{ __('index.placeholderManager') }}"/>
                            </div>
                        </div>

                        <div class="mt-4 grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblEmail">{{ __('index.lblEmail') }}</label>
                                <input id="email"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-slate-100"
                                    placeholder="{{ __('index.placeholderEmail') }}"/>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblPhone">رقم الجوال</label>
                                <input id="phone"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-slate-100"
                                    placeholder="05xxxxxxxx" />
                            </div>
                        </div>

                        <div class="mt-4 grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblCR">{{ __('index.lblCR') }}</label>
                                <input id="cr"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"
                                    placeholder="{{ __('index.placeholderCR') }}" />
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblVAT">{{ __('index.lblVAT') }}</label>
                                <input id="vat"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"
                                    placeholder="VAT Number" />
                            </div>
                        </div>

                        <div class="mt-4 grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblFleet2">{{ __('index.lblFleet2') }}</label>
                                <select id="fleetSize2"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-sky-100">
                                    <option value="5">{{ __('index.fleet1_5_short') }}</option>
                                    <option value="15">{{ __('index.fleet6_15_short') }}</option>
                                    <option value="40">{{ __('index.fleet16_40_short') }}</option>
                                    <option value="80">{{ __('index.fleet41_plus_short') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblBranchCount">{{ __('index.lblBranchCount') }}</label>
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
                                <label class="text-sm font-bold text-slate-700" id="lblPackage2">{{ __('index.lblPackage2') }}</label>
                                <select id="packageType2"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-indigo-100">
                                    <option value="basic" data-price="129">{{ __('index.pkgBasic2') }}</option>
                                    <option value="plus" data-price="169">{{ __('index.pkgPlus2') }}</option>
                                    <option value="pro" data-price="219">{{ __('index.pkgPro2') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700" id="lblContract2">{{ __('index.lblContract2') }}</label>
                                <select id="contractType2"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">
                                    <option value="oneoff">{{ __('index.contractOneoff2') }}</option>
                                    <option value="monthly">{{ __('index.contractMonthly2') }}</option>
                                    <option value="quarterly">{{ __('index.contractQuarterly2') }}</option>
                                    <option value="annual">{{ __('index.contractAnnual2') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
<label class="text-sm font-bold text-slate-700" id="lblAddress2">{{ __('index.lblAddress2') }}</label>
                            <textarea id="address2" rows="3"
                                class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100"
                                placeholder="{{ __('index.placeholderAddress') }}"></textarea>
                        </div>

                        <div class="mt-4 grid sm:grid-cols-2 gap-4">
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="text-sm font-extrabold" id="billingTitle">{{ __('index.billingTitle') }}</div>
                                <div class="mt-3 flex flex-col gap-2 text-sm">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="billing" value="monthly" checked
                                            class="h-4 w-4">
                                        <span id="billMonthly">{{ __('index.billMonthly') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="billing" value="po" class="h-4 w-4">
                                        <span id="billPO">{{ __('index.billPO') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="billing" value="cod" class="h-4 w-4">
                                        <span id="billCOD">{{ __('index.billCOD') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                                <div class="text-sm font-extrabold" id="policyTitle">{{ __('index.policyTitle') }}</div>
                                <p class="mt-2 text-sm text-slate-600" id="policyText">{{ __('index.policyText') }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <button type="submit"
                                class="w-full rounded-2xl bg-emerald-600 px-6 py-3 text-white font-extrabold hover:bg-emerald-700 transition shadow-soft"
                                id="btnSubmit">
                                <i class="fa-solid fa-paper-plane me-2"></i>
                                {{ __('index.btnSubmit') }}
                            </button>
                            <button type="button" id="btnPreview"
                                class="w-full rounded-2xl bg-white border border-slate-200 px-6 py-3 font-extrabold hover:bg-slate-50 transition">
                                <i class="fa-regular fa-eye me-2"></i>
                                {{ __('index.btnPreview') }}
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
        <h2 class="text-3xl font-extrabold" id="faqTitle">{{ __('index.faqTitle') }}</h2>
        <div class="mt-8 grid lg:grid-cols-2 gap-6">
            <details class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <summary class="cursor-pointer font-extrabold" id="q1">{{ __('index.q1') }}</summary>
                <p class="mt-3 text-slate-600 text-sm" id="a1">{{ __('index.a1') }}</p>
            </details>
            <details class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <summary class="cursor-pointer font-extrabold" id="q2">{{ __('index.q2') }}</summary>
                <p class="mt-3 text-slate-600 text-sm" id="a2">{{ __('index.a2') }}</p>
            </details>
            <details class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <summary class="cursor-pointer font-extrabold" id="q3">{{ __('index.q3') }}</summary>
                <p class="mt-3 text-slate-600 text-sm" id="a3">{{ __('index.a3') }}</p>
            </details>
            <details class="rounded-3xl bg-white border border-slate-200 p-6 shadow-soft">
                <summary class="cursor-pointer font-extrabold" id="q4">{{ __('index.q4') }}</summary>
                <p class="mt-3 text-slate-600 text-sm" id="a4">{{ __('index.a4') }}</p>
            </details>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3">
                        @if ($siteLogoUrl ?? null)
                            <img src="{{ $siteLogoUrl }}" alt=""
                                class="h-10 w-10 rounded-full object-cover shadow-soft">
                        @else
                            <div
                                class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-sky-500 shadow-soft">
                            </div>
                        @endif
                        <div>
                            <div class="text-lg font-extrabold">{{ $siteName ?? 'SERV.X' }}</div>
                            <div class="text-xs text-white/60" id="footerTag">{{ __('index.footerTag') }}</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-white/70" id="footerDesc">{{ __('index.footerDesc') }}</p>
                </div>

            <div>
                <div class="font-extrabold mb-3" id="footerLinks">{{ __('index.footerLinks') }}</div>
                <ul class="space-y-2 text-sm text-white/70">
                    <li><a class="hover:text-white" href="#solutions" id="fServices">{{ __('index.fServices') }}</a></li>
                    <li><a class="hover:text-white" href="#how" id="fHow">{{ __('index.fHow') }}</a></li>
                    <li><a class="hover:text-white" href="#request" id="fBooking">{{ __('index.fBooking') }}</a></li>
                    <li><a class="hover:text-white" href="{{ route('company.register') }}">{{ __('index.create_company_account') }}</a></li>
                </ul>
            </div>

                <div>
                    <div class="font-extrabold mb-3" id="footerContact">{{ __('index.footerContact') }}</div>
                    <div class="text-sm text-white/70 space-y-2">
                        @php
                            $waNumber = preg_replace('/[^0-9]/', '', $contactWhatsapp ?? '');
                            if (str_starts_with($waNumber, '0')) {
                                $waNumber = '966' . substr($waNumber, 1);
                            } elseif (!str_starts_with($waNumber, '966') && strlen($waNumber) <= 10) {
                                $waNumber = '966' . ltrim($waNumber, '0');
                            }
                        @endphp
                        <div><i class="fa-brands fa-whatsapp me-2"></i>WhatsApp: <a
                                href="https://wa.me/{{ $waNumber ?: '966512345678' }}" target="_blank"
                                rel="noopener"
                                class="font-bold hover:text-white transition">{{ $contactWhatsapp ?? '05xxxxxxxx' }}</a>
                        </div>
                        <div><i class="fa-regular fa-envelope me-2"></i>Email: <a
                                href="mailto:{{ $contactEmail ?? 'b2b@oilgo.com' }}"
                                class="font-bold hover:text-white transition">{{ $contactEmail ?? 'b2b@oilgo.com' }}</a>
                        </div>
                        <div class="text-xs text-white/50" id="footerNote">{{ __('index.footerNote') }}</div>
                    </div>
                </div>
            </div>

            <div
                class="mt-10 pt-6 border-t border-white/10 text-xs text-white/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div>© <span id="year"></span> {{ $siteName ?? 'SERV.X' }}</div>

            </div>
            <div class="mt-4 text-center text-xs text-white/60" id="footerCredits">
                <span id="footerCreditsText">{{ __('index.footerCreditsText') }}</span>
                <a href="mailto:raghebammar201@gmail.com"
                    class="text-white/80 hover:text-white underline font-semibold transition">Ragheb Aloqab</a>
                <span id="footerCreditsAnd">{{ __('index.footerCreditsAnd') }}</span>
                <a href="mailto:abdullahskander8@gmail.com"
                    class="text-white/80 hover:text-white underline font-semibold transition">Abdullah Eskander</a>
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
                document.querySelectorAll("[data-i18n=\"" + key + "\"]").forEach(function(node) {
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
        (function() {
            const btn = document.getElementById("userMenuBtn");
            const dropdown = document.getElementById("userDropdown");
            if (!btn || !dropdown) return;
            btn.addEventListener("click", function(e) {
                e.stopPropagation();
                dropdown.classList.toggle("hidden");
                btn.setAttribute("aria-expanded", dropdown.classList.contains("hidden") ? "false" : "true");
            });
            document.addEventListener("click", function() {
                dropdown.classList.add("hidden");
                btn.setAttribute("aria-expanded", "false");
            });
        })();

        // Language menu dropdown
        (function() {
            const btn = document.getElementById("langMenuBtn");
            const dropdown = document.getElementById("langDropdown");
            if (!btn || !dropdown) return;
            btn.addEventListener("click", function(e) {
                e.stopPropagation();
                dropdown.classList.toggle("hidden");
                btn.setAttribute("aria-expanded", dropdown.classList.contains("hidden") ? "false" : "true");
            });
            document.addEventListener("click", function() {
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
            fetch("{{ route('set-locale') }}?lang=" + next, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json"
                    }
                })
                .then(r => r.json().catch(() => ({})))
                .then(() => {
                    applyLang(next);
                    applyDir(nextDir);
                });
        });

        $("btnDir").addEventListener("click", () => {
            const next = state.dir === "rtl" ? "ltr" : "rtl";
            fetch("{{ route('set-locale') }}?dir=" + next, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json"
                    }
                })
                .then(r => r.json().catch(() => ({})))
                .then(() => {
                    applyDir(next);
                });
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
