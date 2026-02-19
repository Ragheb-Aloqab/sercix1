<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('ui.dir', app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
    <title>@yield('title', __('driver.dashboard')) — {{ $siteName ?? 'SERV.X' }}</title>
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: "Tajawal", system-ui, sans-serif; }
        .shadow-soft { box-shadow: 0 18px 60px rgba(0,0,0,.12); }
        .pb-tabbar { padding-bottom: calc(5rem + env(safe-area-inset-bottom, 0)); }
        @media (min-width: 1024px) {
            .pb-tabbar { padding-bottom: 0; }
        }
        .driver-avatar { font-size: 1rem; font-weight: 700; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900">
<div class="min-h-screen flex flex-col pb-tabbar">
    {{-- Top bar (always visible) --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('driver.dashboard') }}" class="font-extrabold text-lg flex items-center gap-2 min-w-0 truncate shrink-0">
                @if($siteLogoUrl ?? null)<img src="{{ $siteLogoUrl }}" alt="" class="h-8 w-8 rounded-lg object-cover shrink-0">@endif
                <span class="truncate">{{ $siteName ?? 'SERV.X' }}</span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                {{-- Language switcher --}}
                <div class="flex items-center gap-0.5 rounded-xl border border-slate-200 p-0.5">
                    <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="px-2.5 py-1.5 rounded-lg text-sm font-semibold transition-colors {{ app()->getLocale() === 'ar' ? 'bg-slate-200 text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">ع</a>
                    <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="px-2.5 py-1.5 rounded-lg text-sm font-semibold transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-200 text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">EN</a>
                </div>
                {{-- Menu dropdown with avatar --}}
                <div class="relative" id="driver-menu-wrap">
                    <button type="button" id="driver-menu-btn"
                        class="flex items-center gap-2 px-2 sm:px-3 py-2 rounded-full sm:rounded-xl border border-slate-200 hover:bg-slate-50 font-semibold text-sm transition-colors">
                        @php $initial = mb_substr($driverName ?? __('driver.driver'), 0, 1); @endphp
                        <span class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 driver-avatar" title="{{ $driverName ?? __('driver.driver') }}">{{ $initial }}</span>
                        <span class="max-w-[80px] sm:max-w-[120px] truncate hidden sm:inline">{{ $driverName ?? __('driver.driver') }}</span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform hidden sm:inline" id="driver-menu-chevron"></i>
                    </button>
                <div id="driver-menu-dropdown" class="hidden absolute top-full end-0 mt-1 w-48 rounded-2xl bg-white border border-slate-200 shadow-lg py-1 z-50">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 text-slate-700 font-medium">
                        <i class="fa-solid fa-house w-5 text-center"></i>
                        {{ __('dashboard.main_page') }}
                    </a>
                    <form method="POST" action="{{ route('driver.logout') }}" class="block">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 hover:bg-slate-50 text-slate-700 font-medium text-start">
                            <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
                            {{ __('dashboard.logout') }}
                        </button>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Desktop: sidebar + main | Mobile: main only --}}
    <div class="flex-1 flex flex-col lg:flex-row min-w-0">
        {{-- Sidebar (desktop only) --}}
        <aside class="hidden lg:flex lg:flex-col w-64 shrink-0 bg-white border-e border-slate-200">
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('driver.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->routeIs('driver.dashboard') ? 'bg-emerald-100 text-emerald-800' : 'hover:bg-slate-100 text-slate-700' }}">
                    <i class="fa-solid fa-house w-5 text-center"></i>
                    {{ __('driver.dashboard') }}
                </a>
                <a href="{{ route('driver.request.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->routeIs('driver.request.create') ? 'bg-emerald-100 text-emerald-800' : 'hover:bg-slate-100 text-slate-700' }}">
                    <i class="fa-solid fa-wrench w-5 text-center"></i>
                    {{ __('driver.new_service_request') }}
                </a>
                <a href="{{ route('driver.fuel-refill.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->routeIs('driver.fuel-refill.*') ? 'bg-amber-100 text-amber-800' : 'hover:bg-slate-100 text-slate-700' }}">
                    <i class="fa-solid fa-gas-pump w-5 text-center"></i>
                    {{ __('fuel.fuel_refill_btn') }}
                </a>
            </nav>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 p-4 lg:p-8 min-w-0">
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800">{{ session('error') }}</div>
        @endif
        @yield('content')
        </main>
    </div>

    {{-- Bottom tab bar (mobile only) --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-slate-200" style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);">
        <div class="flex justify-around items-center h-[72px] min-h-[44px]">
            <a href="{{ route('driver.dashboard') }}" class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-2 text-slate-600 active:scale-[0.98] {{ request()->routeIs('driver.dashboard') ? 'text-emerald-600 font-bold' : '' }}">
                <i class="fa-solid fa-house text-xl mb-0.5"></i>
                <span class="text-xs">{{ __('driver.nav_home') }}</span>
            </a>
            <a href="{{ route('driver.request.create') }}" class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-2 text-slate-600 active:scale-[0.98] {{ request()->routeIs('driver.request.create') ? 'text-emerald-600 font-bold' : '' }}">
                <i class="fa-solid fa-wrench text-xl mb-0.5"></i>
                <span class="text-xs">{{ __('driver.nav_service') }}</span>
            </a>
            <a href="{{ route('driver.fuel-refill.create') }}" class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-2 text-slate-600 active:scale-[0.98] {{ request()->routeIs('driver.fuel-refill.*') ? 'text-amber-600 font-bold' : '' }}">
                <i class="fa-solid fa-gas-pump text-xl mb-0.5"></i>
                <span class="text-xs">{{ __('driver.nav_fuel') }}</span>
            </a>
        </div>
    </nav>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('driver-menu-btn');
    var dropdown = document.getElementById('driver-menu-dropdown');
    var chevron = document.getElementById('driver-menu-chevron');
    if (btn && dropdown) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        });
        document.addEventListener('click', function() {
            dropdown.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        });
    }
});
</script>
@stack('scripts')
</body>
</html>
