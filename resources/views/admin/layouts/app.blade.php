<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full scroll-smooth transition-colors duration-300">

<head>
    <x-theme-init />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if(auth('company')->check() || auth('maintenance_center')->check())
    <meta name="theme-color" content="{{ (app()->bound('tenant') || app()->bound('company')) ? (app()->bound('tenant') ? app('tenant') : app('company'))->getResolvedPrimaryColor() : '#0f172a' }}" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="mobile-web-app-capable" content="yes" />
    @endif
    @include('components.seo-meta', [
        'title' => trim((string) ($__env->yieldContent('title') ?? '')) ?: (($brandTitle ?? $siteName ?? 'Servx Motors') . ' — ' . __('dashboard.subtitle_default')),
        'description' => config('seo.default_description'),
        'noindex' => true,
    ])
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif

    @vite(['resources/css/app.css', 'resources/css/style.css', 'resources/js/app.js'])
    <x-vite-cdn-fallback />

    @if($wlBranding ?? false)
    <style>
        :root {
            --wl-primary: {{ (app()->bound('tenant') ? app('tenant') : app('company'))->getResolvedPrimaryColor() }};
            --wl-secondary: {{ (app()->bound('tenant') ? app('tenant') : app('company'))->getResolvedSecondaryColor() }};
            --wl-primary-dark: color-mix(in srgb, var(--wl-primary) 85%, black);
            --tenant-primary: var(--wl-primary);
            --tenant-secondary: var(--wl-secondary);
            --tenant-primary-hover: color-mix(in srgb, var(--wl-primary) 85%, white);
            --tenant-secondary-hover: color-mix(in srgb, var(--wl-secondary) 85%, white);
            --tenant-primary-muted: color-mix(in srgb, var(--wl-primary) 20%, transparent);
            --tenant-secondary-muted: color-mix(in srgb, var(--wl-secondary) 20%, transparent);
            --servx-blue: var(--wl-primary);
            --servx-blue-hover: var(--tenant-primary-hover);
            --servx-green: var(--wl-secondary);
            --servx-green-muted: var(--tenant-secondary-muted);
        }
    </style>
    @endif

    @if(auth('company')->check() || auth('maintenance_center')->check())
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @endif

    {{-- Font Awesome --}}

    @livewireStyles
    <x-echo-setup />
    @stack('styles')
</head>

<body class="admin-layout h-full overflow-x-hidden transition-colors duration-300 {{ auth('company')->check() || auth('maintenance_center')->check() ? 'company-dashboard font-servx bg-slate-50 text-slate-900 dark:bg-servx-black dark:text-servx-silver-light' : 'bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100' }}" @if($wlBranding ?? false) data-wl-branding @endif>

<div x-data="{
        sidebarOpen: false,
        sidebarCollapsed: false,
        init() {
            this.sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        },
        toggleSidebarCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebar-collapsed', this.sidebarCollapsed);
        }
    }"
     @keydown.escape.window="sidebarOpen = false"
     @close-sidebar.window="sidebarOpen = false"
     class="min-h-screen flex w-full min-w-0 transition-colors duration-300 {{ auth('company')->check() || auth('maintenance_center')->check() ? 'company-dashboard-layout' : '' }}"
     :class="{ 'sidebar-collapsed': sidebarCollapsed }">
    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="lg:hidden fixed inset-0 z-[999] bg-black/50 backdrop-blur-sm"></div>

    {{-- Sidebar wrapper: fixed 260px (WL) or 250px, right in RTL, full height — main content sits beside via margin --}}
    <div class="dashboard-sidebar-wrapper flex flex-col max-w-[90vw]
                transform transition-transform duration-300 ease-out
                lg:!translate-x-0"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full'">
        <livewire:dashboard.sidebar />
    </div>

    {{-- Mobile bottom tab bar (visible on lg and below) --}}
    <livewire:dashboard.mobile-tab-bar />

    {{-- Main: flex-1 fills remaining width; margin-inline-start/end (lg+) reserves space so content does not overlap sidebar --}}
    <main class="dashboard-main flex-1 min-w-0 w-full transition-[margin] duration-200 flex flex-col">
        {{-- Topbar --}}
        @include('admin.partials.topbar')

        {{-- Page Content --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6 w-full min-w-0 overflow-x-hidden {{ auth('company')->check() || auth('maintenance_center')->check() ? 'company-dashboard-content' : '' }} {{ auth('company')->check() || auth('maintenance_center')->check() ? 'pb-tabbar-mobile lg:pb-6' : 'pb-24 lg:pb-6' }}">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed bottom-4 end-4 z-[100] max-w-sm rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400 font-medium shadow-lg backdrop-blur-sm"
                     role="alert">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed bottom-4 end-4 z-[100] max-w-sm rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-400 font-medium shadow-lg backdrop-blur-sm"
                     role="alert">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                        {{ session('error') }}
                    </div>
                </div>
            @endif
            @if (session('info'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed bottom-4 end-4 z-[100] max-w-sm rounded-2xl border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-sm text-sky-400 font-medium shadow-lg backdrop-blur-sm"
                     role="alert">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-sky-400"></i>
                        {{ session('info') }}
                    </div>
                </div>
            @endif
            @yield('content')

            <div class="mt-8 text-sm {{ auth('company')->check() || auth('maintenance_center')->check() ? 'text-slate-500' : 'text-slate-500 dark:text-slate-400' }}">
                © All Rights Reserved – {{ ($wlBranding ?? false) && (app()->bound('tenant') || app()->bound('company')) ? (app()->bound('tenant') ? app('tenant') : app('company'))->company_name : ($siteName ?? 'Servx Motors') }}
            </div>
        </section>
    </main>
</div>

{{-- Map scripts (Leaflet + Alpine) - must run after content so @push is populated --}}
@stack('scripts-head')

@livewireScripts

{{-- Theme + Dir listeners (external to avoid ModSecurity block on Hostinger) --}}
<script src="{{ asset('js/theme-livewire.js') }}"></script>


@stack('scripts')
</body>
</html>
