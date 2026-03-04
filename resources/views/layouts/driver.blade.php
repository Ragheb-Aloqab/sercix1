<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    @include('components.seo-meta', [
        'title' => trim((string) ($__env->yieldContent('title') ?? '')) ?: (__('driver.dashboard') . ' — ' . ($siteName ?? 'Servx Motors')),
        'description' => config('seo.default_description'),
        'noindex' => true,
    ])
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/style.css'])
    <x-vite-cdn-fallback />
    @stack('styles')
</head>
<body class="page-tajawal company-dashboard bg-servx-black text-servx-silver-light font-servx overflow-x-hidden min-h-screen">
<div class="min-h-screen flex flex-col pb-tabbar">
    {{-- Top bar (always visible) --}}
    <header class="bg-slate-800/95 border-b border-slate-600/50 backdrop-blur sticky top-0 z-40 w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('driver.dashboard') }}" class="font-extrabold text-lg flex items-center gap-2 min-w-0 truncate shrink-0 text-white">
                @if($siteLogoUrl ?? null)<img src="{{ $siteLogoUrl }}" alt="" width="32" height="32" class="h-8 w-8 rounded-lg object-cover shrink-0">@endif
                <span class="truncate">{{ $siteName ?? 'Servx Motors' }}</span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                {{-- Notifications --}}
                <a href="{{ route('driver.notifications.index') }}" class="relative flex items-center justify-center w-10 h-10 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 text-slate-300 transition-colors" aria-label="{{ __('common.notifications') }}">
                    <i class="fa-solid fa-bell text-lg"></i>
                    @if(($driverNotificationCount ?? 0) > 0)
                        <span class="absolute -top-0.5 -end-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-rose-500 text-white text-xs font-bold px-1">{{ $driverNotificationCount > 99 ? '99+' : $driverNotificationCount }}</span>
                    @endif
                </a>
                {{-- Language switcher --}}
                <div class="flex items-center gap-0.5 rounded-xl border border-slate-600/50 p-0.5 bg-slate-800/60">
                    <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="px-2.5 py-1.5 rounded-lg text-sm font-semibold transition-colors {{ app()->getLocale() === 'ar' ? 'bg-slate-600 text-white' : 'text-slate-400 hover:text-white' }}">ع</a>
                    <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="px-2.5 py-1.5 rounded-lg text-sm font-semibold transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-600 text-white' : 'text-slate-400 hover:text-white' }}">EN</a>
                </div>
                {{-- Menu dropdown with avatar --}}
                <div class="relative" id="driver-menu-wrap">
                    <button type="button" id="driver-menu-btn"
                        class="flex items-center gap-2 px-2 sm:px-3 py-2 rounded-full sm:rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-semibold text-sm transition-colors text-slate-300">
                        <span class="w-9 h-9 rounded-full bg-sky-500 text-white flex items-center justify-center shrink-0 driver-avatar" title="{{ $driverName ?? __('driver.driver') }}">{{ $driverInitial ?? mb_substr($driverName ?? __('driver.driver'), 0, 1) }}</span>
                        <span class="max-w-[80px] sm:max-w-[120px] truncate hidden sm:inline">{{ $driverName ?? __('driver.driver') }}</span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform hidden sm:inline" id="driver-menu-chevron"></i>
                    </button>
                <div id="driver-menu-dropdown" class="hidden absolute top-full end-0 mt-1 w-48 rounded-2xl bg-slate-800 border border-slate-600/50 shadow-lg py-1 z-50">
                    <a href="{{ route('driver.notifications.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-700/50 text-slate-300 font-medium">
                        <i class="fa-solid fa-bell w-5 text-center"></i>
                        {{ __('common.notifications') }}
                        @if(($driverNotificationCount ?? 0) > 0)
                            <span class="ms-auto px-2 py-0.5 rounded-full bg-rose-500 text-white text-xs font-bold">{{ $driverNotificationCount > 99 ? '99+' : $driverNotificationCount }}</span>
                        @endif
                    </a>
                    <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-700/50 text-slate-300 font-medium">
                        <i class="fa-solid fa-house w-5 text-center"></i>
                        {{ __('dashboard.main_page') }}
                    </a>
                    <form method="POST" action="{{ route('driver.logout') }}" class="block">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 hover:bg-slate-700/50 text-slate-300 font-medium text-start">
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
        <aside class="hidden lg:flex lg:flex-col w-64 shrink-0 bg-slate-800/60 border-e border-slate-600/50">
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('driver.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->routeIs('driver.dashboard') ? 'bg-sky-500/20 text-sky-400' : 'hover:bg-slate-700/50 text-slate-300' }}">
                    <i class="fa-solid fa-house w-5 text-center"></i>
                    {{ __('driver.dashboard') }}
                </a>
                <a href="{{ route('driver.history') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->routeIs('driver.history') ? 'bg-sky-500/20 text-sky-400' : 'hover:bg-slate-700/50 text-slate-300' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                    {{ __('driver.latest_requests') }}
                </a>
                <a href="{{ route('driver.notifications.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->routeIs('driver.notifications.*') ? 'bg-sky-500/20 text-sky-400' : 'hover:bg-slate-700/50 text-slate-300' }}">
                    <i class="fa-solid fa-bell w-5 text-center"></i>
                    {{ __('common.notifications') }}
                </a>
                <a href="{{ route('driver.request.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->routeIs('driver.request.create') ? 'bg-sky-500/20 text-sky-400' : 'hover:bg-slate-700/50 text-slate-300' }}">
                    <i class="fa-solid fa-wrench w-5 text-center"></i>
                    {{ __('driver.new_service_request') }}
                </a>
                <a href="{{ route('driver.fuel-refill.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->routeIs('driver.fuel-refill.*') ? 'bg-amber-500/20 text-amber-400' : 'hover:bg-slate-700/50 text-slate-300' }}">
                    <i class="fa-solid fa-gas-pump w-5 text-center"></i>
                    {{ __('fuel.fuel_refill_btn') }}
                </a>
            </nav>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 p-4 lg:p-8 min-w-0">
        @if (session('success'))
            <div id="toast-success" class="fixed bottom-24 end-4 lg:bottom-4 z-[100] max-w-sm rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400 font-medium shadow-lg backdrop-blur-sm flex items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-check shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div id="toast-error" class="fixed bottom-24 end-4 lg:bottom-4 z-[100] max-w-sm rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-400 font-medium shadow-lg backdrop-blur-sm flex items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @yield('content')
        </main>
    </div>

    {{-- Bottom tab bar (mobile only) - fixed at bottom, do not modify --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 w-full z-40 bg-slate-800/95 border-t border-slate-600/50 backdrop-blur" style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);">
        <div class="flex justify-around items-center h-[72px] min-h-[44px]">
            <a href="{{ route('driver.dashboard') }}" class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-2 text-slate-400 active:scale-[0.98] {{ request()->routeIs('driver.dashboard') ? 'text-sky-400 font-bold' : 'hover:text-white' }}">
                <i class="fa-solid fa-house text-xl mb-0.5"></i>
                <span class="text-xs">{{ __('driver.nav_home') }}</span>
            </a>
            <a href="{{ route('driver.history') }}" class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-2 text-slate-400 active:scale-[0.98] {{ request()->routeIs('driver.history') ? 'text-sky-400 font-bold' : 'hover:text-white' }}">
                <i class="fa-solid fa-clock-rotate-left text-xl mb-0.5"></i>
                <span class="text-xs">{{ __('driver.nav_history') }}</span>
            </a>
            <a href="{{ route('driver.request.create') }}" class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-2 text-slate-400 active:scale-[0.98] {{ request()->routeIs('driver.request.create') ? 'text-sky-400 font-bold' : 'hover:text-white' }}">
                <i class="fa-solid fa-wrench text-xl mb-0.5"></i>
                <span class="text-xs">{{ __('driver.nav_service') }}</span>
            </a>
            <a href="{{ route('driver.fuel-refill.create') }}" class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-2 text-slate-400 active:scale-[0.98] {{ request()->routeIs('driver.fuel-refill.*') ? 'text-amber-400 font-bold' : 'hover:text-white' }}">
                <i class="fa-solid fa-gas-pump text-xl mb-0.5"></i>
                <span class="text-xs">{{ __('driver.nav_fuel') }}</span>
            </a>
        </div>
    </nav>
</div>

{{-- Global tracking indicator: runs on every driver page, auto-resumes tracking when active --}}
<script>
(function() {
    var statusUrl = '{{ route('driver.tracking.status') }}';
    var reportUrl = '{{ route('driver.tracking.report') }}';
    var stopUrl = '{{ route('driver.tracking.stop') }}';
    var trackingPageUrl = '{{ route('driver.tracking', ['vehicle' => '__VID__']) }}';
    var csrf = '{{ csrf_token() }}';

    function isOnTrackingPage(vehicleId) {
        var params = new URLSearchParams(window.location.search);
        return window.location.pathname.indexOf('/tracking') !== -1 && parseInt(params.get('vehicle'), 10) === vehicleId;
    }

    function fetchStatus() {
        return fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); });
    }

    function startTracking(vehicleId, displayName) {
        if (!navigator.geolocation) return;
        var watchId = navigator.geolocation.watchPosition(
            function(pos) {
                var fd = new FormData();
                fd.append('_token', csrf);
                fd.append('vehicle_id', vehicleId);
                fd.append('lat', pos.coords.latitude);
                fd.append('lng', pos.coords.longitude);
                fd.append('speed', pos.coords.speed != null ? (pos.coords.speed * 3.6) : '');
                fetch(reportUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(function(r) {
                        if (r.status === 403) stopTracking(vehicleId);
                        return r.json();
                    });
            },
            function() {},
            { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 }
        );
        return watchId;
    }

    function stopTracking(vehicleId, skipModal) {
        if (!skipModal) {
            var endVal = prompt('{{ __("tracking.enter_end_odometer_prompt") }}', '');
            if (endVal === null) return;
            var num = parseFloat(endVal);
            if (isNaN(num) || num < 0) {
                alert('{{ __("tracking.odometer_invalid") }}');
                return;
            }
            doStopTrackingFromBar(vehicleId, num);
        } else {
            doStopTrackingFromBar(vehicleId, 0);
        }
    }

    function doStopTrackingFromBar(vehicleId, endOdometer) {
        var bar = document.getElementById('driver-tracking-bar');
        if (bar) bar.remove();
        if (window.__driverTrackingWatchId) {
            navigator.geolocation.clearWatch(window.__driverTrackingWatchId);
            window.__driverTrackingWatchId = null;
        }
        var form = new FormData();
        form.append('_token', csrf);
        form.append('vehicle_id', vehicleId);
        form.append('end_odometer', endOdometer);
        fetch(stopUrl, { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
    }

    function showTrackingBar(vehicleId, displayName) {
        if (document.getElementById('driver-tracking-bar')) return;
        var bar = document.createElement('div');
        bar.id = 'driver-tracking-bar';
        bar.className = 'fixed bottom-24 inset-x-4 z-50 lg:bottom-auto lg:top-20 lg:inset-x-auto lg:end-4 lg:start-auto max-w-sm';
        bar.innerHTML = '<div class="rounded-2xl shadow-lg border border-emerald-500/40 bg-emerald-500/10 flex items-center justify-between gap-3 px-4 py-3">' +
            '<div class="flex items-center gap-2 min-w-0">' +
            '<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>' +
            '<span class="text-sm font-semibold text-emerald-400 truncate">' + (displayName || '{{ __("tracking.tracking_active") }}') + '</span>' +
            '</div>' +
            '<div class="flex items-center gap-2 shrink-0">' +
            '<a href="' + trackingPageUrl.replace('__VID__', vehicleId) + '" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">{{ __("common.view") }}</a>' +
            '<button type="button" id="driver-tracking-stop" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold">{{ __("tracking.stop_tracking") }}</button>' +
            '</div></div>';
        document.body.appendChild(bar);
        bar.querySelector('#driver-tracking-stop').addEventListener('click', function() {
            stopTracking(vehicleId);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchStatus().then(function(data) {
            if (!data.active || !data.vehicle_id) return;
            if (isOnTrackingPage(data.vehicle_id)) return;
            showTrackingBar(data.vehicle_id, data.display_name || data.plate_number);
            window.__driverTrackingWatchId = startTracking(data.vehicle_id, data.display_name);
        });
    });
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    [].forEach.call(document.querySelectorAll('#toast-success, #toast-error'), function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.3s ease';
            el.style.opacity = '0';
            setTimeout(function() { el.remove(); }, 300);
        }, el.id === 'toast-error' ? 5000 : 4000);
    });
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
