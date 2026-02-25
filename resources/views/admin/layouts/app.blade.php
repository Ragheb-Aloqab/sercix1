<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="{{ auth('company')->check() || session('ui.theme') === 'dark' || request()->routeIs('admin.*') ? 'dark' : '' }} h-full scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @include('components.seo-meta', [
        'title' => trim((string) ($__env->yieldContent('title') ?? '')) ?: (__('dashboard.subtitle_default') . ' | ' . ($siteName ?? 'Servx Motors')),
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

    @if(auth('company')->check())
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @endif

    {{-- Font Awesome --}}

    @livewireStyles
    @stack('styles')
</head>

<body class="admin-layout h-full overflow-x-hidden {{ auth('company')->check() ? 'company-dashboard bg-servx-black text-servx-silver-light font-servx' : 'bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100' }}">

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
     class="min-h-screen flex w-full min-w-0 {{ auth('company')->check() ? 'company-dashboard-layout' : '' }}"
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

    {{-- Sidebar wrapper: fixed 250px, slide-out on mobile, always visible on lg+ --}}
    <div class="dashboard-sidebar-wrapper flex flex-col max-w-[90vw]
                transform transition-transform duration-300 ease-out
                lg:!translate-x-0"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full'">
        <livewire:dashboard.sidebar />
    </div>

    {{-- Mobile bottom tab bar (visible on lg and below) --}}
    <livewire:dashboard.mobile-tab-bar />

    {{-- Main: margin-left 250px on lg+ so content sits next to sidebar --}}
    <main class="dashboard-main flex-1 min-w-0 w-full transition-[margin] duration-200">
        {{-- Topbar --}}
        @include('admin.partials.topbar')

        {{-- Page Content --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6 pb-24 lg:pb-6 w-full min-w-0 overflow-x-hidden {{ auth('company')->check() ? 'company-dashboard-content' : '' }}">
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

            <div class="mt-8 text-sm {{ auth('company')->check() ? 'text-slate-500' : 'text-slate-500 dark:text-slate-400' }}">
                © {{ date('Y') }} {{ $siteName ?? 'Servx Motors' }}
            </div>
        </section>
    </main>
</div>

{{-- Map scripts (Leaflet + Alpine) - must run after content so @push is populated --}}
@stack('scripts-head')

@livewireScripts

{{-- Modal --}}
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('ui-theme-changed', ({ theme }) => {
            document.documentElement.classList.toggle('dark', theme === 'dark');
        });

        Livewire.on('ui-dir-changed', ({ dir }) => {
            document.documentElement.setAttribute('dir', dir);
            document.documentElement.setAttribute('lang', dir === 'rtl' ? 'ar' : 'en');
        });
    });
</script>


@stack('scripts')
</body>
</html>
