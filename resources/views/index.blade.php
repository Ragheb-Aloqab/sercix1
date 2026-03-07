<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    @include('components.seo-meta', [
        'title' => ($siteName ?? 'Servx Motors') . ' — ' . __('index.pageTitle'),
        'description' => __('index.pageTitle') . ' — ' . __('index.brandTag'),
        'image' => $siteLogoUrl ?? null,
        'noindex' => false,
        'breadcrumbs' => [['name' => __('index.pageTitle'), 'url' => '/']],
    ])
    @if ($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
        <link rel="preload" href="{{ $siteLogoUrl }}" as="image" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
        <link rel="preload" href="{{ asset('images/serv.x logo.png') }}" as="image" />
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/style.css'])
    <x-vite-cdn-fallback />

    @include('components.structured-data', [
        'type' => 'all',
        'breadcrumbs' => [['name' => $siteName ?? 'Servx Motors', 'url' => '/']],
    ])
</head>

<body class="page-index bg-servx-black text-servx-silver-light antialiased overflow-x-hidden min-h-screen font-servx">
    <a href="#home" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:start-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-servx-red focus:text-white focus:rounded-lg">{{ __('index.skipToContent') }}</a>

    <!-- Navbar: Black + subtle red bottom border -->
    <header class="sticky top-0 z-40 bg-servx-black backdrop-blur-sm border-b border-servx-red/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4 flex flex-row items-center justify-between gap-4">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="flex items-center justify-center h-12 w-12 rounded-full overflow-hidden shrink-0 border-2 border-servx-red/50">
                    <img src="{{ $siteLogoUrl ?? asset('images/serv.x logo.png') }}" alt="{{ $siteName ?? 'Servx Motors' }}" width="48" height="48" class="h-full w-full object-cover" loading="eager" fetchpriority="high" decoding="async" />
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-bold leading-5 truncate text-servx-silver-light group-hover:text-white transition-colors" id="brandName">{{ $siteName ?? 'Servx Motors' }}</div>
                    <div class="text-xs text-servx-silver truncate" id="brandTag">{{ __('index.brandTag') }}</div>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex flex-row items-center gap-6 text-sm font-semibold">
                <a href="#offers" class="text-servx-silver hover:text-servx-red transition-colors nav-link" id="navOffers">{{ __('index.navOffers') }}</a>
                <a href="#why" class="text-servx-silver hover:text-servx-red transition-colors nav-link" id="navWhy">{{ __('index.navWhy') }}</a>
                <a href="#workflow" class="text-servx-silver hover:text-servx-red transition-colors nav-link" id="navHow">{{ __('index.navHow') }}</a>
                <a href="#faq" class="text-servx-silver hover:text-servx-red transition-colors nav-link" id="navFaq">{{ __('index.navFaq') }}</a>

                <!-- Language Menu -->
                <div class="relative" id="langMenuWrap">
                    <button type="button" id="langMenuBtn" aria-expanded="false" aria-haspopup="true"
                        class="inline-flex items-center gap-2 text-servx-silver hover:text-white transition-colors">
                        <i class="fa-solid fa-globe"></i>
                        <span>{{ $currentLocale === 'ar' ? __('index.langAr') : __('index.langEn') }}</span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>
                    <div id="langDropdown"
                        class="hidden absolute end-0 mt-2 w-40 bg-servx-black-card rounded-lg shadow-servx-card py-2 text-sm z-50 border border-white/10">
                        <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
                            class="block px-4 py-2 text-servx-silver-light hover:bg-servx-red/20 hover:text-white transition-colors {{ $currentLocale === 'ar' ? 'text-servx-red font-bold' : '' }}">
                            {{ __('index.langAr') }}
                        </a>
                        <a href="{{ route('set-locale', ['lang' => 'en']) }}"
                            class="block px-4 py-2 text-servx-silver-light hover:bg-servx-red/20 hover:text-white transition-colors {{ $currentLocale === 'en' ? 'text-servx-red font-bold' : '' }}">
                            {{ __('index.langEn') }}
                        </a>
                    </div>
                </div>

                @if ($user)
                    <div class="relative" id="userMenuWrap">
                        <button type="button" id="userMenuBtn" aria-expanded="false" aria-haspopup="true"
                            class="inline-flex items-center gap-2 text-servx-silver-light hover:text-white font-bold transition-colors">
                            <i class="fa-solid fa-user"></i>
                            <span>{{ $user->name ?? $user->company_name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                        <div id="userDropdown"
                            class="hidden absolute end-0 mt-2 w-48 bg-servx-black-card text-servx-silver-light rounded-lg shadow-servx-card py-2 text-sm z-50 border border-white/10">
                            <a href="{{ $dashboardRoute }}" data-i18n="navDashboard"
                                class="block px-4 py-2 hover:bg-servx-red/20 hover:text-white transition-colors">{{ __('index.navDashboard') }}</a>
                            <form method="POST" action="{{ $logoutRoute }}">
                                @csrf
                                <button type="submit" data-i18n="navLogout"
                                    class="w-full text-start px-4 py-2 hover:bg-servx-red/20 hover:text-white transition-colors">
                                    {{ __('index.navLogout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 bg-servx-red hover:bg-servx-red-hover text-white font-bold px-5 py-2.5 rounded-lg transition-all duration-200 hover:scale-[1.02]">
                        <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                        <span>{{ __('index.navLogin') }}</span>
                    </a>
                @endif
            </nav>

            <div class="flex items-center gap-2">
                <button id="btnMobile" type="button" aria-label="{{ __('common.menu') }}"
                    class="md:hidden inline-flex items-center justify-center rounded-lg border border-servx-red/50 bg-servx-black-card min-w-[44px] min-h-[44px] px-3 py-2 text-sm font-semibold text-servx-silver-light hover:text-servx-red hover:border-servx-red transition-all active:scale-95">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden hidden border-t border-servx-red/20 bg-servx-black overflow-x-hidden">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex flex-col gap-1 text-sm font-medium text-servx-silver-light">
                <a href="#offers" class="py-3 min-h-[44px] flex items-center rounded-lg px-2 -mx-2 hover:bg-servx-red/10 hover:text-servx-red transition-colors" id="mNavOffers">{{ __('index.navOffers') }}</a>
                <a href="#why" class="py-3 min-h-[44px] flex items-center rounded-lg px-2 -mx-2 hover:bg-servx-red/10 hover:text-servx-red transition-colors" id="mNavWhy">{{ __('index.navWhy') }}</a>
                <a href="#workflow" class="py-3 min-h-[44px] flex items-center rounded-lg px-2 -mx-2 hover:bg-servx-red/10 hover:text-servx-red transition-colors" id="mNavHow">{{ __('index.navHow') }}</a>
                <a href="#faq" class="py-3 min-h-[44px] flex items-center rounded-lg px-2 -mx-2 hover:bg-servx-red/10 hover:text-servx-red transition-colors" id="mNavFaq">{{ __('index.mNavFaq') }}</a>

                <div class="flex items-center gap-3 py-2 border-t border-servx-red/20 mt-2 pt-3">
                    <span class="text-servx-silver text-xs">{{ __('index.language') }}:</span>
                    <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
                        class="py-1 px-3 rounded-lg font-semibold transition-colors {{ $currentLocale === 'ar' ? 'bg-servx-red text-white' : 'bg-servx-black-card hover:bg-servx-red/20 text-servx-silver-light' }}">
                        {{ __('index.langAr') }}
                    </a>
                    <a href="{{ route('set-locale', ['lang' => 'en']) }}"
                        class="py-1 px-3 rounded-lg font-semibold transition-colors {{ $currentLocale === 'en' ? 'bg-servx-red text-white' : 'bg-servx-black-card hover:bg-servx-red/20 text-servx-silver-light' }}">
                        {{ __('index.langEn') }}
                    </a>
                </div>

                @if ($user)
                    <a href="{{ $dashboardRoute }}" class="py-3 min-h-[44px] flex items-center font-bold rounded-lg px-2 -mx-2 hover:bg-servx-red/10 hover:text-servx-red transition-colors" data-i18n="navDashboard">{{ __('index.navDashboard') }}</a>
                    <form method="POST" action="{{ $logoutRoute }}">
                        @csrf
                        <button type="submit" class="w-full text-start py-3 min-h-[44px] flex items-center font-bold rounded-lg px-2 -mx-2 hover:bg-servx-red/10 hover:text-servx-red transition-colors" data-i18n="navLogout">{{ __('index.navLogout') }}</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="py-3 min-h-[44px] flex items-center font-bold rounded-lg px-2 -mx-2 hover:bg-servx-red/10 hover:text-servx-red transition-colors">{{ __('index.navLogin') }}</a>
                @endif
            </div>
        </div>
    </header>



    <!-- Hero - Numbers -->
    <section id="home" class="hero-servx relative min-h-[75vh] flex items-center overflow-hidden">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-24 w-full">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-16">
                <div class="text-servx-silver-light text-center lg:text-start">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-servx-silver-light" id="numbersTitle">{{ __('index.numbersTitle') }}</h1>
                    <div class="mt-4 flex items-center justify-center lg:justify-start gap-2">
                        <span class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">Servex Motors</span>
                        <img src="{{ asset('images/serv.x logo icon-03.png') }}" alt="" width="56" height="56" class="h-10 sm:h-12 lg:h-14 w-auto object-contain" aria-hidden="true" loading="lazy" decoding="async" />
                    </div>
                    <p class="mt-2 text-lg sm:text-xl font-bold tracking-[0.25em] text-servx-red flex items-center justify-center lg:justify-start gap-1 flex-wrap">M<span class="inline-block w-2 h-2 rounded-full bg-servx-red"></span>T<span class="inline-block w-2 h-2 rounded-full bg-servx-red"></span>RS</p>
                </div>
                <div class="flex flex-col gap-8 w-full max-w-sm">
                    <div class="flex flex-row items-center gap-4">
                        <span class="text-servx-silver text-base font-medium shrink-0 text-start" id="statCompanies">{{ __('index.statCompanies') }}</span>
                        <div class="h-1 flex-1 stat-bar-servx min-w-[40px]"></div>
                        <span class="count-up text-4xl sm:text-5xl font-bold text-white tabular-nums shrink-0" id="statCompaniesValue" data-count="16">0</span>
                    </div>
                    <div class="flex flex-row items-center gap-4">
                        <span class="text-servx-silver text-base font-medium shrink-0 text-start" id="statVehicles">{{ __('index.statVehicles') }}</span>
                        <div class="h-1 flex-1 stat-bar-servx min-w-[40px]"></div>
                        <span class="count-up text-4xl sm:text-5xl font-bold text-white tabular-nums shrink-0" id="statVehiclesValue" data-count="360">0</span>
                    </div>
                    <div class="flex flex-row items-center gap-4">
                        <span class="text-servx-silver text-base font-medium shrink-0 text-start" id="statSavings">{{ __('index.statSavings') }}</span>
                        <div class="h-1 flex-1 stat-bar-servx min-w-[40px]"></div>
                        <span class="count-up text-4xl sm:text-5xl font-bold text-white tabular-nums shrink-0" id="statSavingsValue" data-count="100000">0</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Offers: Dark cards, red accent on hover -->
    <section id="offers" class="relative py-24 bg-servx-black-soft overflow-hidden">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="flex justify-center order-2 lg:order-1 rtl:lg:order-2">
                    <div class="servx-card servx-card-accent w-56 h-56 sm:w-64 sm:h-64 flex items-center justify-center p-6">
                        <img src="{{ asset('images/serv.x logo icon-03.png') }}" alt="{{ $siteName ?? 'Servx Motors' }}" width="128" height="128" class="max-w-[80%] max-h-[80%] w-auto h-24 sm:h-32 object-contain" loading="lazy" decoding="async" />
                    </div>
                </div>
                <div class="order-1 lg:order-2 rtl:lg:order-1 text-start">
                    <p class="text-xl sm:text-2xl font-bold text-servx-silver" id="offersTitle">{{ __('index.offersTitle') }}</p>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-2">{{ $siteName ?? 'Servx Motors' }}</h2>
                    <div class="mt-10 space-y-5">
                        @foreach(['offersDesc1'=>'offersFeature1','offersDesc2'=>'offersFeature2','offersDesc3'=>'offersFeature3','offersDesc4'=>'offersFeature4','offersDesc5'=>'offersFeature5'] as $desc => $feat)
                        <div class="flex flex-row gap-4 items-start p-4 rounded-lg servx-card servx-card-accent transition-all duration-200">
                            <div class="flex-1 text-servx-silver-light text-sm leading-relaxed" id="{{ $desc }}">{{ __("index.$desc") }}</div>
                            <span class="shrink-0 px-4 py-2 bg-servx-red text-white font-bold text-sm rounded-lg" id="{{ $feat }}">{{ __("index.$feat") }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why SERVX: Dark section, red accent lines -->
    <section id="why" class="py-20 bg-servx-black">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-start">
                <div class="text-start">
                    <div class="flex flex-row items-center gap-3">
                        <div class="h-1 w-16 bg-servx-red rounded-none shrink-0"></div>
                        <h2 class="text-3xl font-bold text-servx-silver-light" id="whyTitle">{{ __('index.whyTitle') }}</h2>
                    </div>
                    <h3 class="text-4xl font-bold text-white mt-2" id="whyBrand">{{ __('index.whyBrand') }}</h3>
                    <div class="h-1 w-24 bg-servx-red rounded-none mt-2 ms-auto"></div>
                    <p class="mt-6 text-servx-silver leading-relaxed" id="whyProblem">{{ __('index.whyProblem') }}</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="servx-card servx-card-accent p-5 text-servx-silver-light font-medium text-start" id="whyPoint1">1- {{ __('index.whyPoint1') }}</div>
                    <div class="servx-card servx-card-accent p-5 text-servx-silver-light font-medium text-start" id="whyPoint2">2- {{ __('index.whyPoint2') }}</div>
                    <div class="servx-card servx-card-accent p-5 text-servx-silver-light font-medium text-start" id="whyPoint3">3- {{ __('index.whyPoint3') }}</div>
                    <div class="servx-card servx-card-accent p-5 text-servx-silver-light font-medium text-start" id="whyPoint4">4- {{ __('index.whyPoint4') }}</div>
                    <div class="servx-card servx-card-accent p-5 text-servx-silver-light font-medium text-start sm:col-span-2" id="whyPoint5">5- {{ __('index.whyPoint5') }}</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Workflow: Dark cards, red accents -->
    <section id="workflow" class="relative py-24 bg-servx-black-soft overflow-hidden">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center lg:text-start mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold text-white" id="workflowTitle">{{ __('index.workflowTitle') }}</h2>
                <p class="text-xl sm:text-2xl font-bold text-servx-red mt-2" id="workflowBrand">{{ $siteName ?? 'Servx Motors' }}</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach(['workflowStep1'=>'workflowDesc1','workflowStep2'=>'workflowDesc2','workflowStep3'=>'workflowDesc3','workflowStep4'=>'workflowDesc4','workflowStep5'=>'workflowDesc5'] as $step => $desc)
                <div class="servx-card servx-card-accent p-6 text-center hover:border-servx-red/40 transition-all duration-200">
                    <div class="w-12 h-12 rounded-lg border-2 border-servx-red flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-check text-servx-red text-lg" aria-hidden="true"></i></div>
                    <h3 class="font-bold text-white" id="{{ $step }}">{{ __("index.$step") }}</h3>
                    <div class="w-0 h-0 mx-auto mt-2 border-l-[6px] border-r-[6px] border-t-[8px] border-l-transparent border-r-transparent border-t-servx-red"></div>
                    <p class="mt-2 text-sm text-servx-silver" id="{{ $desc }}">{{ __("index.$desc") }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-10 servx-card servx-card-accent p-6 max-w-xl mx-auto text-start">
                <h4 class="font-bold text-white" id="workflowConclusion">{{ __('index.workflowConclusion') }}</h4>
                <p class="mt-2 text-sm text-servx-silver" id="workflowConclusionDesc">{{ __('index.workflowConclusionDesc') }}</p>
            </div>
        </div>
    </section>

    <!-- Plans (kept for compatibility, hidden or minimal) -->
    <section id="plans" class="hidden mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex flex-row items-end justify-between gap-6 flex-wrap">
            <div class="text-start">
                <h2 class="text-3xl font-extrabold" id="pricingTitle">{{ __('index.pricingTitle') }}</h2>
                <p class="mt-2 text-slate-600" id="pricingDesc">{{ __('index.pricingDesc') }}</p>
            </div>
        </div>

        <div class="mt-8 grid lg:grid-cols-3 gap-6">
            <div class="rounded-3xl bg-white border border-slate-200 p-7 shadow-soft text-start">
                <div class="text-sm text-slate-500" id="plan1Tag">{{ __('index.plan1Tag') }}</div>
                <div class="mt-2 text-2xl font-extrabold" id="plan1Title">{{ __('index.plan1Title') }}</div>
                <div class="mt-4 text-4xl font-extrabold">129 <span class="text-base font-bold text-slate-500">/ {{ __('index.perVehicle') }}</span></div>
                <ul class="mt-6 space-y-3 text-sm text-slate-700">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan1Item1') }}</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan1Item2') }}</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan1Item3') }}</li>
                </ul>
            </div>

            <div
                class="rounded-3xl bg-slate-900 text-white border border-slate-800 p-7 shadow-soft relative overflow-hidden text-start">
                <div class="absolute -top-12 -end-12 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="text-sm text-white/70" id="plan2Tag">{{ __('index.plan2Tag') }}</div>
                <div class="mt-2 text-2xl font-extrabold" id="plan2Title">{{ __('index.plan2Title') }}</div>
                <div class="mt-4 text-4xl font-extrabold">169 <span class="text-base font-bold text-white/70">/ {{ __('index.perVehicle') }}</span></div>
                <ul class="mt-6 space-y-3 text-sm text-white/90">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-300 shrink-0"></i>{{ __('index.plan2Item1') }}</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-300 shrink-0"></i>{{ __('index.plan2Item2') }}</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-300 shrink-0"></i>{{ __('index.plan2Item3') }}</li>
                </ul>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-7 shadow-soft text-start">
                <div class="text-sm text-slate-500" id="plan3Tag">{{ __('index.plan3Tag') }}</div>
                <div class="mt-2 text-2xl font-extrabold" id="plan3Title">{{ __('index.plan3Title') }}</div>
                <div class="mt-4 text-4xl font-extrabold">{{ __('index.contactUs') }}</div>
                <ul class="mt-6 space-y-3 text-sm text-slate-700">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan3Item1') }}</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan3Item2') }}</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan3Item3') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- FAQ: Dark cards, red accent on hover -->
    <section id="faq" class="bg-servx-black py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-white text-start" id="faqTitle">{{ __('index.faqTitle') }}</h2>
            <div class="mt-10 grid lg:grid-cols-2 gap-6">
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors" id="q1">{{ __('index.q1') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a1">{{ __('index.a1') }}</p>
                </details>
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors" id="q2">{{ __('index.q2') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a2">{{ __('index.a2') }}</p>
                </details>
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors" id="q3">{{ __('index.q3') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a3">{{ __('index.a3') }}</p>
                </details>
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors" id="q4">{{ __('index.q4') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a4">{{ __('index.a4') }}</p>
                </details>
            </div>
        </div>
    </section>

    <!-- Footer: Black + red border -->
    <footer class="bg-servx-black border-t border-servx-red/30 text-servx-silver-light">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-start order-1 rtl:md:order-3">
                    <div class="flex flex-row items-center gap-3">
                        <img src="{{ $siteLogoUrl ?? asset('images/serv.x logo.png') }}" alt="{{ $siteName ?? 'Servx Motors' }}" width="40" height="40" class="h-10 w-10 rounded-full object-cover border-2 border-servx-red/50" loading="lazy" decoding="async" />
                        <div>
                            <div class="text-lg font-bold text-white">{{ $siteName ?? 'Servx Motors' }}</div>
                            <div class="text-xs text-servx-silver" id="footerTag">{{ __('index.footerTag') }}</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-servx-silver" id="footerDesc">{{ __('index.footerDesc') }}</p>
                </div>

                <div class="text-start order-2">
                    <div class="font-bold text-white mb-3" id="footerLinks">{{ __('index.footerLinks') }}</div>
                    <ul class="space-y-2 text-sm text-servx-silver">
                        <li><a class="hover:text-servx-red transition-colors" href="#offers" id="fServices">{{ __('index.fServices') }}</a></li>
                        <li><a class="hover:text-servx-red transition-colors" href="#workflow" id="fHow">{{ __('index.fHow') }}</a></li>
                        <li><a class="hover:text-servx-red transition-colors" href="#faq" id="fFaq">{{ __('index.faqTitle') }}</a></li>
                    </ul>
                </div>

                <div class="text-start order-3 rtl:md:order-1">
                    <div class="font-bold text-white mb-3" id="footerContact">{{ __('index.footerContact') }}</div>
                    <div class="text-sm text-servx-silver space-y-2">
                        <div class="flex items-center gap-2"><i class="fa-brands fa-whatsapp shrink-0 text-servx-red"></i>WhatsApp: <a
                                href="https://wa.me/{{ $waNumber ?? '966512345678' }}" target="_blank"
                                rel="noopener"
                                class="font-bold hover:text-servx-red transition">{{ $contactWhatsapp ?? '05xxxxxxxx' }}</a>
                        </div>
                        <div class="flex items-center gap-2"><i class="fa-regular fa-envelope shrink-0 text-servx-red"></i>Email: <a
                                href="mailto:{{ $contactEmail ?? 'b2b@oilgo.com' }}"
                                class="font-bold hover:text-servx-red transition">{{ $contactEmail ?? 'b2b@oilgo.com' }}</a>
                        </div>
                        <div class="text-xs text-servx-silver/60" id="footerNote">{{ __('index.footerNote') }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-servx-red/20 text-xs text-servx-silver flex flex-col sm:flex-row items-center justify-between gap-3">
                <div>© All Rights Reserved – Servix Motors</div>
            </div>
            <div class="mt-4 text-center text-xs text-servx-silver" id="footerCredits">
                <span id="footerCreditsText">{{ __('index.footerCreditsText') }}</span>
                <span class="font-semibold">Servix Motors</span>
            </div>
        </div>
    </footer>

    <script>
        (function() {
            const $ = id => document.getElementById(id);
            const btnMobile = $("btnMobile");
            const mobileMenu = $("mobileMenu");
            if (btnMobile && mobileMenu) {
                btnMobile.addEventListener("click", () => mobileMenu.classList.toggle("hidden"));
                mobileMenu.querySelectorAll("a").forEach(a => a.addEventListener("click", () => mobileMenu.classList.add("hidden")));
            }
            const userBtn = $("userMenuBtn");
            const userDropdown = $("userDropdown");
            if (userBtn && userDropdown) {
                userBtn.addEventListener("click", e => { e.stopPropagation(); userDropdown.classList.toggle("hidden"); });
                document.addEventListener("click", () => userDropdown.classList.add("hidden"));
            }
            const langBtn = $("langMenuBtn");
            const langDropdown = $("langDropdown");
            if (langBtn && langDropdown) {
                langBtn.addEventListener("click", e => { e.stopPropagation(); langDropdown.classList.toggle("hidden"); });
                document.addEventListener("click", () => langDropdown.classList.add("hidden"));
            }
            const yearEl = $("year");
            if (yearEl) yearEl.textContent = new Date().getFullYear();

            // Scroll spy: active nav link in red
            const sectionToNav = { home: ["navOffers","mNavOffers"], offers: ["navOffers","mNavOffers"], why: ["navWhy","mNavWhy"], workflow: ["navHow","mNavHow"], faq: ["navFaq","mNavFaq"] };
            const visible = new Set();
            const spyObserver = new IntersectionObserver((entries) => {
                entries.forEach(e => { e.isIntersecting ? visible.add(e.target.id) : visible.delete(e.target.id); });
                const active = ["home","offers","why","workflow","faq"].find(id => visible.has(id)) || "home";
                ["navOffers","navWhy","navHow","navFaq","mNavOffers","mNavWhy","mNavHow","mNavFaq"].forEach(id => {
                    const el = $(id);
                    if (el) el.classList.toggle("text-servx-red", sectionToNav[active]?.includes(id));
                });
            }, { threshold: 0.2 });
            ["home","offers","why","workflow","faq"].forEach(id => { const el = $(id); if (el) spyObserver.observe(el); });

            // Animated count-up when hero enters viewport
            const hero = $("home");
            const countUps = document.querySelectorAll(".count-up");
            if (hero && countUps.length) {
                const locale = document.documentElement.lang === "ar" ? "ar-SA" : "en-US";
                const format = n => n >= 1000 ? n.toLocaleString(locale) : String(n);
                const easeOutExpo = t => t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
                const DURATION = 1800;

                const animate = (el, target) => {
                    const start = performance.now();
                    const run = now => {
                        const elapsed = now - start;
                        const progress = Math.min(elapsed / DURATION, 1);
                        const eased = easeOutExpo(progress);
                        const value = Math.round(target * eased);
                        el.textContent = format(value);
                        if (progress < 1) requestAnimationFrame(run);
                        else el.textContent = format(target);
                    };
                    requestAnimationFrame(run);
                };

                const observer = new IntersectionObserver((entries) => {
                    for (const e of entries) {
                        if (!e.isIntersecting) continue;
                        observer.disconnect();
                        countUps.forEach(el => {
                            const target = parseInt(el.dataset.count || "0", 10);
                            if (!isNaN(target)) animate(el, target);
                        });
                        break;
                    }
                }, { threshold: 0.2, rootMargin: "0px" });
                observer.observe(hero);
            }
        })();
    </script>
</body>

</html>