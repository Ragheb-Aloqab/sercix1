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
    <!-- Skip to main content (accessibility) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:start-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-servx-red focus:text-white focus:rounded-lg focus:outline-none focus:ring-2 focus:ring-white">{{ __('index.skipToContent') }}</a>

    <!-- ========== HEADER / NAVBAR ========== -->
    <header role="banner" class="sticky top-0 z-40 bg-servx-black backdrop-blur-sm border-b border-servx-red/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4 flex flex-row items-center gap-4">
            <!-- Logo / Brand -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black shrink-0">
                <div class="flex items-center justify-center h-12 w-12 rounded-full overflow-hidden shrink-0 border-2 border-servx-red/50">
                    <img src="{{ $siteLogoUrl ?? asset('images/serv.x logo.png') }}" alt="{{ $siteName ?? 'Servx Motors' }} — {{ __('index.brandTag') }}" width="48" height="48" class="h-full w-full object-cover" loading="eager" fetchpriority="high" decoding="async" />
                </div>
                <div class="min-w-0">
                    <span class="text-lg font-bold leading-5 truncate text-servx-silver-light group-hover:text-white transition-colors block" id="brandName">{{ $siteName ?? 'Servx Motors' }}</span>
                    <span class="text-xs text-servx-silver truncate block" id="brandTag">{{ __('index.brandTag') }}</span>
                </div>
            </a>

            <!-- Spacer: limits gap between logo and nav -->
            <div class="hidden md:block flex-1 min-w-0 max-w-md" aria-hidden="true"></div>

            <!-- Desktop navigation -->
            <nav aria-label="{{ __('index.navMain') }}" class="hidden md:flex flex-row items-center gap-6 text-sm font-semibold shrink-0 ms-auto">
                <a href="#offers" class="text-servx-silver hover:text-servx-red transition-colors rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black py-1 px-1" id="navOffers">{{ __('index.navOffers') }}</a>
                <a href="#why" class="text-servx-silver hover:text-servx-red transition-colors rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black py-1 px-1" id="navWhy">{{ __('index.navWhy') }}</a>
                <a href="#workflow" class="text-servx-silver hover:text-servx-red transition-colors rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black py-1 px-1" id="navHow">{{ __('index.navHow') }}</a>
                <a href="#faq" class="text-servx-silver hover:text-servx-red transition-colors rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black py-1 px-1" id="navFaq">{{ __('index.navFaq') }}</a>

                <!-- Language switcher -->
                <div class="relative" id="langMenuWrap">
                    <button type="button" id="langMenuBtn" aria-expanded="false" aria-haspopup="true" aria-label="{{ __('index.language') }}"
                        class="inline-flex items-center gap-2 text-servx-silver hover:text-white transition-colors rounded-lg py-1.5 px-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black">
                        <i class="fa-solid fa-globe" aria-hidden="true"></i>
                        <span>{{ $currentLocale === 'ar' ? __('index.langAr') : __('index.langEn') }}</span>
                        <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                    </button>
                    <div id="langDropdown" role="menu"
                        class="hidden absolute end-0 mt-2 w-40 bg-servx-black-card rounded-xl shadow-servx-card py-2 text-sm z-50 border border-servx-red/20">
                        <a href="{{ route('set-locale', ['lang' => 'ar']) }}" role="menuitem"
                            class="block px-4 py-2.5 text-servx-silver-light hover:bg-servx-red/20 hover:text-white transition-colors rounded-lg mx-2 {{ $currentLocale === 'ar' ? 'text-servx-red font-bold' : '' }}">
                            {{ __('index.langAr') }}
                        </a>
                        <a href="{{ route('set-locale', ['lang' => 'en']) }}" role="menuitem"
                            class="block px-4 py-2.5 text-servx-silver-light hover:bg-servx-red/20 hover:text-white transition-colors rounded-lg mx-2 {{ $currentLocale === 'en' ? 'text-servx-red font-bold' : '' }}">
                            {{ __('index.langEn') }}
                        </a>
                    </div>
                </div>

                @if ($user)
                    <!-- User menu (Admin / Company profile) -->
                    <div class="relative" id="userMenuWrap">
                        <button type="button" id="userMenuBtn" aria-expanded="false" aria-haspopup="true" aria-label="{{ __('index.navDashboard') }}"
                            class="inline-flex items-center gap-2 text-servx-silver-light hover:text-white font-bold transition-colors rounded-lg py-1.5 px-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                            <span id="userName">{{ $user->name ?? $user->company_name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                        </button>
                        <div id="userDropdown" role="menu"
                            class="hidden absolute end-0 mt-2 w-48 bg-servx-black-card text-servx-silver-light rounded-xl shadow-servx-card py-2 text-sm z-50 border border-servx-red/20">
                            <a href="{{ $dashboardRoute }}" role="menuitem" data-i18n="navDashboard"
                                class="block px-4 py-2.5 hover:bg-servx-red/20 hover:text-white transition-colors rounded-lg mx-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-inset">{{ __('index.navDashboard') }}</a>
                            <form method="POST" action="{{ $logoutRoute }}" class="block">
                                @csrf
                                <button type="submit" role="menuitem" data-i18n="navLogout"
                                    class="w-full text-start px-4 py-2.5 hover:bg-servx-red/20 hover:text-white transition-colors rounded-lg mx-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-inset">
                                    {{ __('index.navLogout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" id="navLoginBtn"
                        class="inline-flex items-center gap-2 bg-servx-red hover:bg-servx-red-hover text-white font-bold px-5 py-2.5 rounded-xl transition-all duration-200 hover:scale-[1.02] focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black">
                        <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                        <span>{{ __('index.navLogin') }}</span>
                    </a>
                @endif
            </nav>

            <!-- Mobile menu trigger -->
            <button id="btnMobile" type="button" aria-label="{{ __('common.menu') }}" aria-expanded="false" aria-controls="mobileMenu"
                class="md:hidden inline-flex items-center justify-center rounded-xl border border-servx-red/50 bg-servx-black-card min-w-[44px] min-h-[44px] px-3 py-2 text-sm font-semibold text-servx-silver-light hover:text-servx-red hover:border-servx-red transition-all active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Mobile menu panel -->
        <div id="mobileMenu" class="md:hidden hidden border-t border-servx-red/20 bg-servx-black overflow-x-hidden" role="navigation" aria-label="{{ __('index.navMain') }}">
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

    <!-- ========== MAIN CONTENT ========== -->
    <main id="main-content" role="main">

    <!-- ---------- Hero: Fleet management features ---------- -->
    <section id="home" class="hero-servx relative min-h-[75vh] flex items-center overflow-hidden">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-24 w-full">
            <div class="flex flex-col gap-12 lg:gap-14">
                <!-- Headline & subline -->
                <div class="text-center max-w-4xl mx-auto">
                    <h1 class="hero-fleet-title text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold tracking-tight text-white" id="heroHeadline">
                        {{ __('index.heroHeadline') }}
                    </h1>
                    <p class="hero-fleet-subline mt-4 text-lg sm:text-xl text-servx-silver-light/90 leading-relaxed">
                        {{ __('index.heroSubline') }}
                    </p>
                    <div class="mt-6 flex items-center justify-center gap-2">
                        <span class="text-xl sm:text-2xl font-bold tracking-[0.2em] text-servx-red">M<span class="inline-block w-1.5 h-1.5 rounded-full bg-servx-red align-middle"></span>T<span class="inline-block w-1.5 h-1.5 rounded-full bg-servx-red align-middle"></span>RS</span>
                        <img src="{{ asset('images/serv.x logo icon-03.png') }}" alt="" width="48" height="48" class="h-10 w-auto object-contain" aria-hidden="true" loading="lazy" decoding="async" role="presentation" />
                    </div>
                </div>

                <!-- Feature cards: scroll-in + hover + expand -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-5" id="heroFeatureGrid">
                    @foreach([
                        'invoicing' => ['icon' => 'fa-file-invoice', 'title' => 'heroFeatureInvoicing', 'desc' => 'heroFeatureInvoicingDesc', 'expand' => 'heroFeatureInvoicingExpand'],
                        'tracking' => ['icon' => 'fa-location-dot', 'title' => 'heroFeatureTracking', 'desc' => 'heroFeatureTrackingDesc', 'expand' => 'heroFeatureTrackingExpand'],
                        'reports' => ['icon' => 'fa-chart-line', 'title' => 'heroFeatureReports', 'desc' => 'heroFeatureReportsDesc', 'expand' => 'heroFeatureReportsExpand'],
                        'vehicles' => ['icon' => 'fa-car', 'title' => 'heroFeatureVehicles', 'desc' => 'heroFeatureVehiclesDesc', 'expand' => 'heroFeatureVehiclesExpand'],
                        'operations' => ['icon' => 'fa-gears', 'title' => 'heroFeatureOperations', 'desc' => 'heroFeatureOperationsDesc', 'expand' => 'heroFeatureOperationsExpand'],
                    ] as $key => $item)
                    <details class="hero-feature-card group/details rounded-xl border border-servx-red/20 bg-servx-black-card/80 backdrop-blur-sm p-5 text-start transition-all duration-300 hover:border-servx-red/40 hover:shadow-[0_0_24px_rgba(239,68,68,0.12)] open:border-servx-red/50 open:shadow-[0_0_28px_rgba(239,68,68,0.15)]" data-hero-feature>
                        <summary class="list-none cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black rounded-lg">
                            <span class="flex items-center gap-3">
                                <span class="hero-feature-icon flex items-center justify-center w-12 h-12 rounded-xl bg-servx-red/15 text-servx-red group-hover/details:bg-servx-red/25 group-hover/details:scale-110 transition-all duration-300">
                                    <i class="fa-solid {{ $item['icon'] }} text-xl" aria-hidden="true"></i>
                                </span>
                                <span class="font-bold text-white text-lg">{{ __("index.{$item['title']}") }}</span>
                                <i class="fa-solid fa-chevron-down text-servx-silver text-sm ms-auto transition-transform duration-200 group-open/details:rotate-180" aria-hidden="true"></i>
                            </span>
                            <p class="mt-3 text-sm text-servx-silver leading-relaxed">{{ __("index.{$item['desc']}") }}</p>
                            <span class="mt-2 inline-block text-xs text-servx-red font-semibold">{{ __('index.heroClickExpand') }}</span>
                        </summary>
                        <div class="mt-4 pt-4 border-t border-servx-red/20">
                            <p class="text-sm text-servx-silver-light leading-relaxed">{{ __("index.{$item['expand']}") }}</p>
                        </div>
                    </details>
                    @endforeach
                </div>

                <!-- CTA -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="try-now-text inline-block text-2xl sm:text-3xl font-bold text-white no-underline" dir="rtl" id="tryNowText">
                        <span class="try-now-inner">{{ __('index.heroTryNow') }}<span class="try-now-cursor" aria-hidden="true">|</span></span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ---------- Offers: What we offer ---------- -->
    <section id="offers" class="relative py-24 bg-servx-black-soft overflow-hidden">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="flex justify-center order-2 lg:order-1 rtl:lg:order-2">
                    <div class="servx-card servx-card-accent w-56 h-56 sm:w-64 sm:h-64 flex items-center justify-center p-6">
                        <img src="{{ asset('images/serv.x logo icon-03.png') }}" alt="{{ $siteName ?? 'Servx Motors' }} — {{ __('index.offersTitle') }}" width="128" height="128" class="max-w-[80%] max-h-[80%] w-auto h-24 sm:h-32 object-contain" loading="lazy" decoding="async" />
                    </div>
                </div>
                <div class="order-1 lg:order-2 rtl:lg:order-1 text-start">
                    <p class="text-xl sm:text-2xl font-bold text-servx-silver" id="offersTitle">{{ __('index.offersTitle') }}</p>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-2" id="offersBrand">{{ $siteName ?? 'Servx Motors' }}</h2>
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

    <!-- ---------- Why Servx Motors ---------- -->
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

    <!-- ---------- Workflow: How it works ---------- -->
    <section id="workflow" class="relative py-24 bg-servx-black-soft overflow-hidden">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center lg:text-start mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold text-white" id="workflowTitle">{{ __('index.workflowTitle') }}</h2>
                <p class="text-xl sm:text-2xl font-bold text-servx-red mt-2" id="workflowBrand">{{ $siteName ?? 'Servx Motors' }}</p>
                <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach(['workflowStep1' => 'workflowDesc1', 'workflowStep2' => 'workflowDesc2', 'workflowStep3' => 'workflowDesc3', 'workflowStep4' => 'workflowDesc4', 'workflowStep5' => 'workflowDesc5'] as $step => $desc)
                <div class="servx-card servx-card-accent p-6 text-center hover:border-servx-red/40 transition-all duration-200">
                    <div class="w-12 h-12 rounded-lg border-2 border-servx-red flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-check text-servx-red text-lg" aria-hidden="true"></i></div>
                    <h3 class="font-bold text-white" id="{{ $step }}">{{ __("index.$step") }}</h3>
                    <div class="w-0 h-0 mx-auto mt-2 border-l-[6px] border-r-[6px] border-t-[8px] border-l-transparent border-r-transparent border-t-servx-red"></div>
                    <p class="mt-2 text-sm text-servx-silver" id="{{ $desc }}">{{ __("index.$desc") }}</p>
                </div>
                @endforeach
                </div>
            </div>
            <div class="mt-10 servx-card servx-card-accent p-6 max-w-xl mx-auto text-start">
                <h4 class="font-bold text-white" id="workflowConclusion">{{ __('index.workflowConclusion') }}</h4>
                <p class="mt-2 text-sm text-servx-silver" id="workflowConclusionDesc">{{ __('index.workflowConclusionDesc') }}</p>
            </div>
        </div>
    </section>

    <!-- ---------- Plans / Pricing ---------- -->
    <section id="plans" class="relative py-24 bg-servx-black overflow-hidden" aria-labelledby="pricingTitle">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-white" id="pricingTitle">{{ __('index.pricingTitle') }}</h2>
                <p class="mt-2 text-servx-silver max-w-2xl mx-auto" id="pricingDesc">{{ __('index.pricingDesc') }}</p>
            </div>
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Basic Plan -->
                <div class="servx-card servx-card-accent p-6 sm:p-7 rounded-2xl border border-servx-red/20 hover:border-servx-red/40 transition-all duration-200 text-start">
                    <p class="text-sm text-servx-silver" id="plan1Tag">{{ __('index.plan1Tag') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-white" id="plan1Title">{{ __('index.plan1Title') }}</h3>
                    <p class="mt-4 text-3xl font-bold text-servx-silver-light"><span id="plan1Price" class="inline-flex items-center gap-1.5 flex-wrap">@if(trim(__('index.plan1Price')) !== ''){{ __('index.plan1Price') }}<x-riyal-icon />{{ __('index.pricePerVehicle') }} / {{ __('index.perVehicle') }}@else{{ __('index.contactUs') }}@endif</span></p>
                    <ul class="mt-6 space-y-3 text-sm text-servx-silver-light">
                        @foreach(['plan1Item1','plan1Item2','plan1Item3','plan1Item4','plan1Item5','plan1Item6','plan1Item7'] as $key)
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-servx-red shrink-0" aria-hidden="true"></i>{{ __("index.$key") }}</li>
                        @endforeach
                    </ul>
                </div>
                <!-- Standard Plan -->
                <div class="servx-card servx-card-accent p-6 sm:p-7 rounded-2xl border-2 border-servx-red/50 bg-servx-red/5 hover:border-servx-red/70 transition-all duration-200 text-start relative overflow-hidden">
                    <div class="absolute -top-12 -end-12 h-32 w-32 rounded-full bg-servx-red/10 blur-2xl" aria-hidden="true"></div>
                    <p class="text-sm text-servx-silver" id="plan2Tag">{{ __('index.plan2Tag') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-white" id="plan2Title">{{ __('index.plan2Title') }}</h3>
                    <p class="mt-4 text-3xl font-bold text-white"><span id="plan2Price" class="inline-flex items-center gap-1.5 flex-wrap">@if(trim(__('index.plan2Price')) !== ''){{ __('index.plan2Price') }}<x-riyal-icon />{{ __('index.pricePerVehicle') }} / {{ __('index.perVehicle') }}@else{{ __('index.contactUs') }}@endif</span></p>
                    <p class="mt-3 text-xs font-semibold text-servx-red/90">{{ __('index.plan2Includes') }}</p>
                    <ul class="mt-4 space-y-3 text-sm text-servx-silver-light">
                        @foreach(['plan2Item1','plan2Item2','plan2Item3','plan2Item4','plan2Item5','plan2Item6','plan2Item7'] as $key)
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-servx-red shrink-0" aria-hidden="true"></i>{{ __("index.$key") }}</li>
                        @endforeach
                    </ul>
                </div>
                <!-- Pro Plan -->
                <div class="servx-card servx-card-accent p-6 sm:p-7 rounded-2xl border border-servx-red/20 hover:border-servx-red/40 transition-all duration-200 text-start">
                    <p class="text-sm text-servx-silver" id="plan3Tag">{{ __('index.plan3Tag') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-white" id="plan3Title">{{ __('index.plan3Title') }}</h3>
                    <p class="mt-4 text-3xl font-bold text-servx-silver-light" id="plan3Price"><span class="inline-flex items-center gap-1.5 flex-wrap">@if(trim(__('index.plan3Price')) !== ''){{ __('index.plan3Price') }}<x-riyal-icon />{{ __('index.pricePerVehicle') }} / {{ __('index.perVehicle') }}@else{{ __('index.contactUs') }}@endif</span></p>
                    <p class="mt-3 text-xs font-semibold text-servx-red/90">{{ __('index.plan3Includes') }}</p>
                    <ul class="mt-4 space-y-3 text-sm text-servx-silver-light">
                        @foreach(['plan3Item1','plan3Item2','plan3Item3','plan3Item4','plan3Item5'] as $key)
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-servx-red shrink-0" aria-hidden="true"></i>{{ __("index.$key") }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ---------- FAQ ---------- -->
    <section id="faq" class="bg-servx-black py-24" aria-labelledby="faqTitle">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-white text-start" id="faqTitle">{{ __('index.faqTitle') }}</h2>
            <div class="mt-10 grid lg:grid-cols-2 gap-6">
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black rounded" id="q1">{{ __('index.q1') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a1">{{ __('index.a1') }}</p>
                </details>
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black rounded" id="q2">{{ __('index.q2') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a2">{{ __('index.a2') }}</p>
                </details>
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black rounded" id="q3">{{ __('index.q3') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a3">{{ __('index.a3') }}</p>
                </details>
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black rounded" id="q4">{{ __('index.q4') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a4">{{ __('index.a4') }}</p>
                </details>
                <details class="servx-card servx-card-accent p-6 group text-start hover:border-servx-red/40 transition-all duration-200 lg:col-span-2">
                    <summary class="cursor-pointer font-bold text-servx-silver-light group-hover:text-servx-red transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black rounded" id="q5">{{ __('index.q5') }}</summary>
                    <p class="mt-3 text-servx-silver text-sm" id="a5">{{ __('index.a5') }}</p>
                </details>
            </div>
        </div>
    </section>

    </main>

    <!-- ========== FOOTER ========== -->
    <footer role="contentinfo" class="bg-servx-black border-t border-servx-red/30 text-servx-silver-light">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-start order-1 rtl:md:order-3">
                    <div class="flex flex-row items-center gap-3">
                        <img src="{{ $siteLogoUrl ?? asset('images/serv.x logo.png') }}" alt="{{ $siteName ?? 'Servx Motors' }} — {{ __('index.footerTag') }}" width="40" height="40" class="h-10 w-10 rounded-full object-cover border-2 border-servx-red/50" loading="lazy" decoding="async" />
                        <div>
                            <span class="text-lg font-bold text-white block" id="footerBrandName">{{ $siteName ?? 'Servx Motors' }}</span>
                            <span class="text-xs text-servx-silver block" id="footerTag">{{ __('index.footerTag') }}</span>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-servx-silver" id="footerDesc">{{ __('index.footerDesc') }}</p>
                </div>

                <div class="text-start order-2">
                    <span class="font-bold text-white mb-3 block" id="footerLinks">{{ __('index.footerLinks') }}</span>
                    <ul class="space-y-2 text-sm text-servx-silver">
                        <li><a class="hover:text-servx-red transition-colors rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black" href="#offers" id="fServices">{{ __('index.fServices') }}</a></li>
                        <li><a class="hover:text-servx-red transition-colors rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black" href="#workflow" id="fHow">{{ __('index.fHow') }}</a></li>
                        <li><a class="hover:text-servx-red transition-colors rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black" href="#faq" id="fFaq">{{ __('index.faqTitle') }}</a></li>
                    </ul>
                </div>

                <div class="text-start order-3 rtl:md:order-1">
                    @if($footerContactVisible ?? true)
                    <span class="font-bold text-white mb-3 block" id="footerContact">{{ __('index.footerContact') }}</span>
                    <div class="text-sm text-servx-silver space-y-2">
                        @if($contactPhone ?? '')
                        <div class="flex items-center gap-2"><i class="fa-brands fa-whatsapp shrink-0 text-servx-red" aria-hidden="true"></i>WhatsApp: <a
                                href="https://wa.me/{{ $waNumber ?? '966512345678' }}" target="_blank"
                                rel="noopener noreferrer"
                                class="font-bold hover:text-servx-red transition focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black rounded">{{ $contactPhone }}</a>
                        </div>
                        @endif
                        @if($contactEmail ?? '')
                        <div class="flex items-center gap-2"><i class="fa-regular fa-envelope shrink-0 text-servx-red" aria-hidden="true"></i>Email: <a
                                href="mailto:{{ $contactEmail }}"
                                class="font-bold hover:text-servx-red transition focus:outline-none focus-visible:ring-2 focus-visible:ring-servx-red focus-visible:ring-offset-2 focus-visible:ring-offset-servx-black rounded">{{ $contactEmail }}</a>
                        </div>
                        @endif
                        @if(__('index.footerNote'))
                        <p class="text-xs text-servx-silver/60" id="footerNote">{{ __('index.footerNote') }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-servx-red/20 text-xs text-servx-silver flex flex-col sm:flex-row items-center justify-between gap-3">
                <span id="footerCopyright">© {{ date('Y') }} <span id="footerCopyrightBrand">{{ $siteName ?? 'Servx Motors' }}</span>. {{ __('index.footerAllRightsReserved') }}</span>
            </div>
            <p class="mt-4 text-center text-xs text-servx-silver" id="footerCredits">
                <span id="footerCreditsText">{{ __('index.footerCreditsText') }}</span>
                <span class="font-semibold" id="footerCreditsBrand">{{ $siteName ?? 'Servx Motors' }}</span>
            </p>
        </div>
    </footer>

    <script>
        (function() {
            const $ = id => document.getElementById(id);
            const btnMobile = $("btnMobile");
            const mobileMenu = $("mobileMenu");
            if (btnMobile && mobileMenu) {
                btnMobile.addEventListener("click", () => {
                    const open = mobileMenu.classList.toggle("hidden");
                    btnMobile.setAttribute("aria-expanded", open ? "false" : "true");
                });
                mobileMenu.querySelectorAll("a").forEach(a => a.addEventListener("click", () => {
                    mobileMenu.classList.add("hidden");
                    btnMobile.setAttribute("aria-expanded", "false");
                }));
            }
            const userBtn = $("userMenuBtn");
            const userDropdown = $("userDropdown");
            if (userBtn && userDropdown) {
                userBtn.addEventListener("click", e => {
                    e.stopPropagation();
                    const open = userDropdown.classList.toggle("hidden");
                    userBtn.setAttribute("aria-expanded", open ? "false" : "true");
                });
                document.addEventListener("click", () => {
                    userDropdown.classList.add("hidden");
                    userBtn.setAttribute("aria-expanded", "false");
                });
            }
            const langBtn = $("langMenuBtn");
            const langDropdown = $("langDropdown");
            if (langBtn && langDropdown) {
                langBtn.addEventListener("click", e => {
                    e.stopPropagation();
                    const open = langDropdown.classList.toggle("hidden");
                    langBtn.setAttribute("aria-expanded", open ? "false" : "true");
                });
                document.addEventListener("click", () => {
                    langDropdown.classList.add("hidden");
                    langBtn.setAttribute("aria-expanded", "false");
                });
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

            // Hero fleet section: scroll-in animations
            const hero = $("home");
            const featureCards = document.querySelectorAll(".hero-feature-card");
            if (hero) {
                const heroObserver = new IntersectionObserver((entries) => {
                    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add("hero-visible"); });
                }, { threshold: 0.15 });
                heroObserver.observe(hero);
            }
            if (featureCards.length) {
                const cardObserver = new IntersectionObserver((entries) => {
                    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add("hero-card-visible"); });
                }, { threshold: 0.2, rootMargin: "0px 0px -20px 0px" });
                featureCards.forEach(el => cardObserver.observe(el));
            }
        })();
    </script>
</body>

</html>