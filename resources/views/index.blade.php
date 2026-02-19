<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ session('ui.dir', app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="description" content="{{ __('index.pageTitle') }} — {{ __('index.brandTag') }}" />
    <title>{{ $siteName ?? 'SERV.X' }} — {{ __('index.pageTitle') }}</title>
    @if ($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

    <style>
        :root {
            --shadow: 0 18px 60px rgba(0, 0, 0, .12);
            --transition-fast: 150ms;
            --transition-base: 200ms;
        }
        body { font-family: "Tajawal", system-ui, -apple-system, sans-serif; }
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, .2); border-radius: 999px; }
        .focus-ring:focus-visible { outline: 2px solid rgb(239 68 68); outline-offset: 2px; }
        .hero-gradient { background: linear-gradient(to left, rgb(185 28 28 / .5), rgb(127 29 29 / .2), transparent); }
        [dir="rtl"] .hero-gradient { background: linear-gradient(to right, rgb(185 28 28 / .5), rgb(127 29 29 / .2), transparent); }
        .stat-bar { background: linear-gradient(to right, rgb(255 255 255 / .8), rgb(239 68 68)); }
        [dir="rtl"] .stat-bar { background: linear-gradient(to left, rgb(255 255 255 / .8), rgb(239 68 68)); }
        @media (max-width: 639px) {
            html { font-size: 15px; }
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

<body class="bg-slate-50 text-slate-900 antialiased overflow-x-hidden min-h-screen">
    <a href="#home" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus-start-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-red-600 focus:text-white focus:rounded-xl">{{ __('index.skipToContent') }}</a>

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur border-b border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4 flex flex-row rtl:flex-row-reverse items-center justify-between gap-4">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="flex items-center justify-center h-12 w-12 rounded-full shadow-soft overflow-hidden shrink-0 border border-white/20">
                    @if ($siteLogoUrl ?? null)
                        <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'SERV.X' }}" class="h-full w-full object-cover" loading="eager" />
                    @else
                        <div class="h-full w-full bg-red-600 flex items-center justify-center text-white font-black text-lg">
                            {{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-extrabold leading-5 truncate text-white group-hover:text-white/90" id="brandName">
                        {{ $siteName ?? 'SERV.X' }}</div>
                    <div class="text-xs text-white/60 truncate" id="brandTag">{{ __('index.brandTag') }}</div>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex flex-row rtl:flex-row-reverse items-center gap-6 text-sm font-medium">
                <a href="#offers" class="text-white/80 hover:text-white" id="navOffers">{{ __('index.navOffers') }}</a>
                <a href="#why" class="text-white/80 hover:text-white" id="navWhy">{{ __('index.navWhy') }}</a>
                <a href="#workflow" class="text-white/80 hover:text-white" id="navHow">{{ __('index.navHow') }}</a>
                <a href="#faq" class="text-white/80 hover:text-white" id="navFaq">{{ __('index.navFaq') }}</a>

                <!-- Language Menu -->
                <div class="relative" id="langMenuWrap">
                    <button type="button" id="langMenuBtn" aria-expanded="false" aria-haspopup="true"
                        class="inline-flex items-center gap-2 text-white/80 hover:text-white">
                        <i class="fa-solid fa-globe"></i>
                        <span>{{ $currentLocale === 'ar' ? __('index.langAr') : __('index.langEn') }}</span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>
                    <div id="langDropdown"
                        class="hidden absolute end-0 mt-2 w-40 bg-slate-800 rounded-xl shadow-lg py-2 text-sm z-50 border border-white/10">
                        <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
                            class="block px-4 py-2 text-white hover:bg-white/10 {{ $currentLocale === 'ar' ? 'bg-white/10 font-semibold' : '' }}">
                            {{ __('index.langAr') }}
                        </a>
                        <a href="{{ route('set-locale', ['lang' => 'en']) }}"
                            class="block px-4 py-2 text-white hover:bg-white/10 {{ $currentLocale === 'en' ? 'bg-white/10 font-semibold' : '' }}">
                            {{ __('index.langEn') }}
                        </a>
                    </div>
                </div>

                <!-- User Authentication (data from IndexController) -->
                @if ($user)
                    <!-- User Menu -->
                    <div class="relative" id="userMenuWrap">
                        <button type="button" id="userMenuBtn" aria-expanded="false" aria-haspopup="true"
                            class="inline-flex items-center gap-2 text-white hover:text-white/90 font-extrabold">
                            <i class="fa-solid fa-user"></i>
                            <span>{{ $user->name ?? $user->company_name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>

                        <div id="userDropdown"
                            class="hidden absolute end-0 mt-2 w-48 bg-slate-800 text-white rounded-xl shadow-xl py-2 text-sm z-50 border border-white/10">
                            <a href="{{ $dashboardRoute }}" data-i18n="navDashboard"
                                class="block px-4 py-2 hover:bg-white/10">{{ __('index.navDashboard') }}</a>
                            <form method="POST" action="{{ $logoutRoute }}">
                                @csrf
                                <button type="submit" data-i18n="navLogout"
                                    class="w-full rtl:text-right ltr:text-left px-4 py-2 hover:bg-white/10">
                                    {{ __('index.navLogout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('sign-in.index') }}"
                        class="inline-flex items-center gap-2 text-white hover:text-white/90 font-extrabold">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        {{ __('index.navLogin') }}
                    </a>
                @endif
            </nav>

            <!-- Mobile Menu Button -->
            <div class="flex items-center gap-2">
                <a href="{{ route('company.register') }}" id="ctaBookTop"
                    class="hidden sm:inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-white text-sm font-semibold hover:bg-red-500 transition">
                    <i class="fa-solid fa-file-signature me-2 rtl:me-0 rtl:ms-2"></i>
                    {{ __('index.ctaBookTop') }}
                </a>

                <button id="btnMobile" type="button" aria-label="{{ __('common.menu') }}"
                    class="md:hidden inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 min-w-[44px] min-h-[44px] px-3 py-2 text-sm font-semibold text-white hover:bg-white/20 transition active:scale-95">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden hidden border-t border-white/10 bg-slate-900 overflow-x-hidden">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex flex-col gap-1 text-sm font-medium text-white">
                <a href="#offers" class="py-3 min-h-[44px] flex items-center active:bg-white/5 rounded-lg px-2 -mx-2" id="mNavOffers">{{ __('index.navOffers') }}</a>
                <a href="#why" class="py-3 min-h-[44px] flex items-center active:bg-white/5 rounded-lg px-2 -mx-2" id="mNavWhy">{{ __('index.navWhy') }}</a>
                <a href="#workflow" class="py-3 min-h-[44px] flex items-center active:bg-white/5 rounded-lg px-2 -mx-2" id="mNavHow">{{ __('index.navHow') }}</a>
                <a href="#faq" class="py-3 min-h-[44px] flex items-center active:bg-white/5 rounded-lg px-2 -mx-2" id="mNavFaq">{{ __('index.mNavFaq') }}</a>

                <div class="flex items-center gap-3 py-2 border-t border-white/10 mt-2 pt-3">
                    <span class="text-white/60 text-xs">{{ __('index.language') }}:</span>
                    <a href="{{ route('set-locale', ['lang' => 'ar']) }}"
                        class="py-1 px-3 rounded-lg {{ $currentLocale === 'ar' ? 'bg-red-600 text-white font-semibold' : 'bg-white/10 hover:bg-white/20' }}">
                        {{ __('index.langAr') }}
                    </a>
                    <a href="{{ route('set-locale', ['lang' => 'en']) }}"
                        class="py-1 px-3 rounded-lg {{ $currentLocale === 'en' ? 'bg-red-600 text-white font-semibold' : 'bg-white/10 hover:bg-white/20' }}">
                        {{ __('index.langEn') }}
                    </a>
                </div>

                @if ($user)
                    <a href="{{ $dashboardRoute }}" class="py-3 min-h-[44px] flex items-center font-extrabold active:bg-white/5 rounded-lg px-2 -mx-2"
                        data-i18n="navDashboard">{{ __('index.navDashboard') }}</a>
                    <form method="POST" action="{{ $logoutRoute }}">
                        @csrf
                        <button type="submit" class="w-full text-start py-3 min-h-[44px] flex items-center font-extrabold active:bg-white/5 rounded-lg px-2 -mx-2"
                            data-i18n="navLogout">{{ __('index.navLogout') }}</button>
                    </form>
                @else
                    <a href="{{ route('sign-in.index') }}"
                        class="py-3 min-h-[44px] flex items-center font-extrabold active:bg-white/5 rounded-lg px-2 -mx-2">{{ __('index.navLogin') }}</a>
                @endif
            </div>
        </div>
    </header>



    <!-- Hero - Numbers -->
    <section id="home" class="relative min-h-[75vh] flex items-center overflow-hidden bg-slate-900">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1920')] bg-cover bg-center opacity-25 blur-[2px]"></div>
        <div class="absolute inset-0 hero-gradient"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-24 w-full">
            <div class="flex flex-col lg:flex-row rtl:lg:flex-row-reverse items-center justify-between gap-16">
                <div class="text-white text-center lg:text-start">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white" id="numbersTitle">{{ __('index.numbersTitle') }}</h1>
                    <div class="mt-4">
                        <span class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white">SERV.X</span>
                        <svg class="inline-block w-10 h-6 sm:w-12 sm:h-7 ms-2 -mb-1 text-red-500" viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14h40v4H4z"/><path d="M8 10l4-6h24l4 6"/><circle cx="12" cy="18" r="3"/><circle cx="36" cy="18" r="3"/></svg>
                    </div>
                    <p class="mt-2 text-lg sm:text-xl font-bold tracking-[0.25em] text-red-500 flex items-center gap-0.5 flex-wrap">M<span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>T<span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>RS</p>
                </div>
                <div class="flex flex-col gap-8 w-full max-w-sm">
                    <div class="flex flex-row rtl:flex-row-reverse items-center gap-4">
                        <span class="text-white text-base font-medium shrink-0 text-start" id="statCompanies">{{ __('index.statCompanies') }}</span>
                        <div class="h-1 flex-1 stat-bar rounded-full min-w-[40px]"></div>
                        <span class="count-up text-4xl sm:text-5xl font-black text-white tabular-nums shrink-0" id="statCompaniesValue" data-count="16">0</span>
                    </div>
                    <div class="flex flex-row rtl:flex-row-reverse items-center gap-4">
                        <span class="text-white text-base font-medium shrink-0 text-start" id="statVehicles">{{ __('index.statVehicles') }}</span>
                        <div class="h-1 flex-1 stat-bar rounded-full min-w-[40px]"></div>
                        <span class="count-up text-4xl sm:text-5xl font-black text-white tabular-nums shrink-0" id="statVehiclesValue" data-count="360">0</span>
                    </div>
                    <div class="flex flex-row rtl:flex-row-reverse items-center gap-4">
                        <span class="text-white text-base font-medium shrink-0 text-start" id="statSavings">{{ __('index.statSavings') }}</span>
                        <div class="h-1 flex-1 stat-bar rounded-full min-w-[40px]"></div>
                        <span class="count-up text-4xl sm:text-5xl font-black text-white tabular-nums shrink-0" id="statSavingsValue" data-count="100000">0</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVX offers you -->
    <section id="offers" class="relative py-24 bg-slate-900 overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=1920')] bg-cover bg-center opacity-15"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="flex justify-center order-2 lg:order-1 rtl:lg:order-2">
                    <div class="relative w-56 h-56 sm:w-64 sm:h-64 rounded-2xl border border-white/20 flex items-center justify-center bg-slate-800/60 backdrop-blur-sm transition-transform duration-300 hover:scale-[1.02]">
                        <svg class="w-32 h-16 sm:w-40 sm:h-20 text-white" viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14h40v4H4z"/><path d="M8 10l4-6h24l4 6"/><circle cx="12" cy="18" r="3"/><circle cx="36" cy="18" r="3"/></svg>
                    </div>
                </div>
                <div class="order-1 lg:order-2 rtl:lg:order-1 text-start">
                    <p class="text-xl sm:text-2xl font-bold text-white/90" id="offersTitle">{{ __('index.offersTitle') }}</p>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mt-2">{{ $siteName ?? 'SERVX' }}</h2>
                    <div class="mt-10 space-y-5">
                        <div class="flex flex-row rtl:flex-row-reverse gap-4 items-start p-3 rounded-xl transition-colors duration-200 hover:bg-white/5">
                            <div class="flex-1 text-white/90 text-sm leading-relaxed" id="offersDesc1">{{ __('index.offersDesc1') }}</div>
                            <span class="shrink-0 px-4 py-2 bg-red-600 text-white font-bold text-sm rounded-none" id="offersFeature1">{{ __('index.offersFeature1') }}</span>
                        </div>
                        <div class="flex flex-row rtl:flex-row-reverse gap-4 items-start p-3 rounded-xl transition-colors duration-200 hover:bg-white/5">
                            <div class="flex-1 text-white/90 text-sm leading-relaxed" id="offersDesc2">{{ __('index.offersDesc2') }}</div>
                            <span class="shrink-0 px-4 py-2 bg-red-600 text-white font-bold text-sm rounded-none" id="offersFeature2">{{ __('index.offersFeature2') }}</span>
                        </div>
                        <div class="flex flex-row rtl:flex-row-reverse gap-4 items-start p-3 rounded-xl transition-colors duration-200 hover:bg-white/5">
                            <div class="flex-1 text-white/90 text-sm leading-relaxed" id="offersDesc3">{{ __('index.offersDesc3') }}</div>
                            <span class="shrink-0 px-4 py-2 bg-red-600 text-white font-bold text-sm rounded-none" id="offersFeature3">{{ __('index.offersFeature3') }}</span>
                        </div>
                        <div class="flex flex-row rtl:flex-row-reverse gap-4 items-start p-3 rounded-xl transition-colors duration-200 hover:bg-white/5">
                            <div class="flex-1 text-white/90 text-sm leading-relaxed" id="offersDesc4">{{ __('index.offersDesc4') }}</div>
                            <span class="shrink-0 px-4 py-2 bg-red-600 text-white font-bold text-sm rounded-none" id="offersFeature4">{{ __('index.offersFeature4') }}</span>
                        </div>
                        <div class="flex flex-row rtl:flex-row-reverse gap-4 items-start p-3 rounded-xl transition-colors duration-200 hover:bg-white/5">
                            <div class="flex-1 text-white/90 text-sm leading-relaxed" id="offersDesc5">{{ __('index.offersDesc5') }}</div>
                            <span class="shrink-0 px-4 py-2 bg-red-600 text-white font-bold text-sm rounded-none" id="offersFeature5">{{ __('index.offersFeature5') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why SERVX -->
    <section id="why" class="py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-start">
                <div class="text-start">
                    <div class="flex flex-row rtl:flex-row-reverse items-center gap-3">
                        <div class="h-1 w-16 bg-red-600 rounded-none shrink-0"></div>
                        <h2 class="text-3xl font-extrabold text-black" id="whyTitle">{{ __('index.whyTitle') }}</h2>
                    </div>
                    <h3 class="text-4xl font-black text-black mt-2" id="whyBrand">{{ __('index.whyBrand') }}</h3>
                    <div class="h-1 w-24 bg-red-600 rounded-none mt-2 ms-auto"></div>
                    <p class="mt-6 text-black leading-relaxed" id="whyProblem">{{ __('index.whyProblem') }}</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="rounded-xl bg-slate-100/90 border border-slate-200 p-5 text-black font-medium text-start" id="whyPoint1">1- {{ __('index.whyPoint1') }}</div>
                    <div class="rounded-xl bg-slate-100/90 border border-slate-200 p-5 text-black font-medium text-start" id="whyPoint2">2- {{ __('index.whyPoint2') }}</div>
                    <div class="rounded-xl bg-slate-100/90 border border-slate-200 p-5 text-black font-medium text-start" id="whyPoint3">3- {{ __('index.whyPoint3') }}</div>
                    <div class="rounded-xl bg-slate-100/90 border border-slate-200 p-5 text-black font-medium text-start" id="whyPoint4">4- {{ __('index.whyPoint4') }}</div>
                    <div class="rounded-xl bg-slate-100/90 border border-slate-200 p-5 text-black font-medium text-start sm:col-span-2" id="whyPoint5">5- {{ __('index.whyPoint5') }}</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Workflow - How it works -->
    <section id="workflow" class="relative py-24 bg-slate-900 overflow-hidden">
        <div class="absolute inset-0 opacity-40" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,0.03) 10px, rgba(255,255,255,0.03) 20px);"></div>
        <div class="absolute top-0 end-0 w-1/2 h-full bg-gradient-to-s from-red-600/25 to-transparent"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center lg:text-start mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white" id="workflowTitle">{{ __('index.workflowTitle') }}</h2>
                <p class="text-xl sm:text-2xl font-bold text-white/90 mt-2" id="workflowBrand">{{ $siteName ?? 'SERVX' }}</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="rounded-xl border border-white/20 bg-slate-800/50 p-6 text-center hover:bg-slate-800/70 hover:border-white/30 transition-all duration-200">
                    <div class="w-12 h-12 rounded-lg border-2 border-white flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-check text-white text-lg" aria-hidden="true"></i></div>
                    <h3 class="font-bold text-white" id="workflowStep1">{{ __('index.workflowStep1') }}</h3>
                    <div class="w-0 h-0 mx-auto mt-2 border-l-[6px] border-r-[6px] border-t-[8px] border-l-transparent border-r-transparent border-t-red-500"></div>
                    <p class="mt-2 text-sm text-white/80" id="workflowDesc1">{{ __('index.workflowDesc1') }}</p>
                </div>
                <div class="rounded-xl border border-white/20 bg-slate-800/50 p-6 text-center hover:bg-slate-800/70 hover:border-white/30 transition-all duration-200">
                    <div class="w-12 h-12 rounded-lg border-2 border-white flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-check text-white text-lg" aria-hidden="true"></i></div>
                    <h3 class="font-bold text-white" id="workflowStep2">{{ __('index.workflowStep2') }}</h3>
                    <div class="w-0 h-0 mx-auto mt-2 border-l-[6px] border-r-[6px] border-t-[8px] border-l-transparent border-r-transparent border-t-red-500"></div>
                    <p class="mt-2 text-sm text-white/80" id="workflowDesc2">{{ __('index.workflowDesc2') }}</p>
                </div>
                <div class="rounded-xl border border-white/20 bg-slate-800/50 p-6 text-center hover:bg-slate-800/70 hover:border-white/30 transition-all duration-200">
                    <div class="w-12 h-12 rounded-lg border-2 border-white flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-check text-white text-lg" aria-hidden="true"></i></div>
                    <h3 class="font-bold text-white" id="workflowStep3">{{ __('index.workflowStep3') }}</h3>
                    <div class="w-0 h-0 mx-auto mt-2 border-l-[6px] border-r-[6px] border-t-[8px] border-l-transparent border-r-transparent border-t-red-500"></div>
                    <p class="mt-2 text-sm text-white/80" id="workflowDesc3">{{ __('index.workflowDesc3') }}</p>
                </div>
                <div class="rounded-xl border border-white/20 bg-slate-800/50 p-6 text-center hover:bg-slate-800/70 hover:border-white/30 transition-all duration-200">
                    <div class="w-12 h-12 rounded-lg border-2 border-white flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-check text-white text-lg" aria-hidden="true"></i></div>
                    <h3 class="font-bold text-white" id="workflowStep4">{{ __('index.workflowStep4') }}</h3>
                    <div class="w-0 h-0 mx-auto mt-2 border-l-[6px] border-r-[6px] border-t-[8px] border-l-transparent border-r-transparent border-t-red-500"></div>
                    <p class="mt-2 text-sm text-white/80" id="workflowDesc4">{{ __('index.workflowDesc4') }}</p>
                </div>
                <div class="rounded-xl border border-white/20 bg-slate-800/50 p-6 text-center hover:bg-slate-800/70 hover:border-white/30 transition-all duration-200">
                    <div class="w-12 h-12 rounded-lg border-2 border-white flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-check text-white text-lg" aria-hidden="true"></i></div>
                    <h3 class="font-bold text-white" id="workflowStep5">{{ __('index.workflowStep5') }}</h3>
                    <div class="w-0 h-0 mx-auto mt-2 border-l-[6px] border-r-[6px] border-t-[8px] border-l-transparent border-r-transparent border-t-red-500"></div>
                    <p class="mt-2 text-sm text-white/80" id="workflowDesc5">{{ __('index.workflowDesc5') }}</p>
                </div>
            </div>
            <div class="mt-10 rounded-xl bg-slate-800/50 border border-white/10 p-6 max-w-xl ms-auto rtl:ms-0 rtl:me-auto text-start">
                <h4 class="font-bold text-white" id="workflowConclusion">{{ __('index.workflowConclusion') }}</h4>
                <p class="mt-2 text-sm text-white/80" id="workflowConclusionDesc">{{ __('index.workflowConclusionDesc') }}</p>
            </div>
        </div>
    </section>

    <!-- Plans (kept for compatibility, hidden or minimal) -->
    <section id="plans" class="hidden mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex flex-row rtl:flex-row-reverse items-end justify-between gap-6 flex-wrap">
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
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan1Item1') }}</li>
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan1Item2') }}</li>
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan1Item3') }}</li>
                </ul>
            </div>

            <div
                class="rounded-3xl bg-slate-900 text-white border border-slate-800 p-7 shadow-soft relative overflow-hidden text-start">
                <div class="absolute -top-12 -end-12 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="text-sm text-white/70" id="plan2Tag">{{ __('index.plan2Tag') }}</div>
                <div class="mt-2 text-2xl font-extrabold" id="plan2Title">{{ __('index.plan2Title') }}</div>
                <div class="mt-4 text-4xl font-extrabold">169 <span class="text-base font-bold text-white/70">/ {{ __('index.perVehicle') }}</span></div>
                <ul class="mt-6 space-y-3 text-sm text-white/90">
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-300 shrink-0"></i>{{ __('index.plan2Item1') }}</li>
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-300 shrink-0"></i>{{ __('index.plan2Item2') }}</li>
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-300 shrink-0"></i>{{ __('index.plan2Item3') }}</li>
                </ul>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 p-7 shadow-soft text-start">
                <div class="text-sm text-slate-500" id="plan3Tag">{{ __('index.plan3Tag') }}</div>
                <div class="mt-2 text-2xl font-extrabold" id="plan3Title">{{ __('index.plan3Title') }}</div>
                <div class="mt-4 text-4xl font-extrabold">{{ __('index.contactUs') }}</div>
                <ul class="mt-6 space-y-3 text-sm text-slate-700">
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan3Item1') }}</li>
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan3Item2') }}</li>
                    <li class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-solid fa-check text-emerald-700 shrink-0"></i>{{ __('index.plan3Item3') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 text-start" id="faqTitle">{{ __('index.faqTitle') }}</h2>
        <div class="mt-10 grid lg:grid-cols-2 gap-6">
            <details class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-200 group text-start">
                <summary class="cursor-pointer font-extrabold text-slate-900 group-hover:text-red-600 transition-colors" id="q1">{{ __('index.q1') }}</summary>
                <p class="mt-3 text-slate-600 text-sm" id="a1">{{ __('index.a1') }}</p>
            </details>
            <details class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-200 group text-start">
                <summary class="cursor-pointer font-extrabold text-slate-900 group-hover:text-red-600 transition-colors" id="q2">{{ __('index.q2') }}</summary>
                <p class="mt-3 text-slate-600 text-sm" id="a2">{{ __('index.a2') }}</p>
            </details>
            <details class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-200 group text-start">
                <summary class="cursor-pointer font-extrabold text-slate-900 group-hover:text-red-600 transition-colors" id="q3">{{ __('index.q3') }}</summary>
                <p class="mt-3 text-slate-600 text-sm" id="a3">{{ __('index.a3') }}</p>
            </details>
            <details class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-200 group text-start">
                <summary class="cursor-pointer font-extrabold text-slate-900 group-hover:text-red-600 transition-colors" id="q4">{{ __('index.q4') }}</summary>
                <p class="mt-3 text-slate-600 text-sm" id="a4">{{ __('index.a4') }}</p>
            </details>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-start order-1 rtl:md:order-3">
                    <div class="flex flex-row rtl:flex-row-reverse items-center gap-3">
                        @if ($siteLogoUrl ?? null)
                            <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'SERV.X' }}" class="h-10 w-10 rounded-full object-cover shadow-soft" loading="lazy" />
                        @else
                            <div class="h-10 w-10 rounded-full bg-red-600 flex items-center justify-center text-white font-black text-sm shadow-soft">
                                {{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div class="text-lg font-extrabold">{{ $siteName ?? 'SERV.X' }}</div>
                            <div class="text-xs text-white/60" id="footerTag">{{ __('index.footerTag') }}</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-white/70" id="footerDesc">{{ __('index.footerDesc') }}</p>
                </div>

                <div class="text-start order-2">
                    <div class="font-extrabold mb-3" id="footerLinks">{{ __('index.footerLinks') }}</div>
                    <ul class="space-y-2 text-sm text-white/70">
                        <li><a class="hover:text-white transition-colors" href="#offers" id="fServices">{{ __('index.fServices') }}</a></li>
                        <li><a class="hover:text-white transition-colors" href="#workflow" id="fHow">{{ __('index.fHow') }}</a></li>
                        <li><a class="hover:text-white transition-colors" href="#faq" id="fFaq">{{ __('index.faqTitle') }}</a></li>
                        <li><a class="hover:text-white transition-colors" href="{{ route('company.register') }}" id="fBooking">{{ __('index.fBooking') }}</a></li>
                        <li><a class="hover:text-white transition-colors" href="{{ route('company.register') }}">{{ __('index.create_company_account') }}</a></li>
                    </ul>
                </div>

                <div class="text-start order-3 rtl:md:order-1">
                    <div class="font-extrabold mb-3" id="footerContact">{{ __('index.footerContact') }}</div>
                    <div class="text-sm text-white/70 space-y-2">
                        <div class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-brands fa-whatsapp shrink-0"></i>WhatsApp: <a
                                href="https://wa.me/{{ $waNumber ?? '966512345678' }}" target="_blank"
                                rel="noopener"
                                class="font-bold hover:text-white transition">{{ $contactWhatsapp ?? '05xxxxxxxx' }}</a>
                        </div>
                        <div class="flex items-center gap-2 rtl:flex-row-reverse"><i class="fa-regular fa-envelope shrink-0"></i>Email: <a
                                href="mailto:{{ $contactEmail ?? 'b2b@oilgo.com' }}"
                                class="font-bold hover:text-white transition">{{ $contactEmail ?? 'b2b@oilgo.com' }}</a>
                        </div>
                        <div class="text-xs text-white/50" id="footerNote">{{ __('index.footerNote') }}</div>
                    </div>
                </div>
            </div>

            <div
                class="mt-10 pt-6 border-t border-white/10 text-xs text-white/50 flex flex-col sm:flex-row rtl:sm:flex-row-reverse items-center justify-between gap-3">
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