<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ session('ui.dir', app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}"
      class="{{ session('ui.theme') === 'dark' ? 'dark' : '' }} h-full scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', __('dashboard.subtitle_default') . ' | ' . ($siteName ?? 'SERV.X'))</title>
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif

    
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    boxShadow: {
                        soft: "0 12px 30px rgba(0,0,0,.08)"
                    },
                    minHeight: {
                        'touch': '44px'
                    }
                }
            }
        }
    </script>

    <style>
        ::-webkit-scrollbar { height: 10px; width: 10px }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px }
        .dark ::-webkit-scrollbar-thumb { background: #475569 }
        /* Sidebar positioning (desktop only; hidden on mobile, replaced by tab bar) */
        [dir="ltr"] #sidebar { left: 0; right: auto; }
        [dir="rtl"] #sidebar { left: auto; right: 0; }
        [x-cloak] { display: none !important; }
        /* Responsive: prevent horizontal scroll, ensure touch-friendly */
        html { overflow-x: hidden; }
        body { -webkit-overflow-scrolling: touch; }
        /* Minimum font size for readability on mobile */
        @media (max-width: 639px) {
            body { font-size: 15px; }
        }
        /* Table scroll containers: smooth scroll on touch devices */
        .overflow-x-auto { -webkit-overflow-scrolling: touch; }
    </style>

    @livewireStyles
    @stack('styles')
</head>

<body class="h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 overflow-x-hidden">

<div class="min-h-screen flex w-full min-w-0">
    {{-- Sidebar --}}
    <livewire:dashboard.sidebar />

    {{-- Mobile bottom tab bar (visible on lg and below) --}}
    <livewire:dashboard.mobile-tab-bar />

    {{-- Main --}}
    <main class="flex-1 min-w-0 w-full lg:ms-80 lg:min-w-0">
        {{-- Topbar --}}
        @include('admin.partials.topbar')

        {{-- Page Content --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6 pb-24 lg:pb-6 w-full min-w-0 overflow-x-hidden">
            @yield('content')

            <div class="mt-8 text-sm text-slate-500 dark:text-slate-400">
                © {{ date('Y') }} {{ $siteName ?? 'SERV.X' }}
            </div>
        </section>
    </main>
</div>

@livewireScripts

{{-- Modal --}}
@include('admin.partials.modals.create-order')

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

<script>
    const $ = (id) => document.getElementById(id);

    // Modal: Create Order
    const modal = $('createOrderModal');
    const openModal = () => modal?.classList.remove('hidden');
    const closeModal = () => modal?.classList.add('hidden');

    $('openCreateOrder')?.addEventListener('click', openModal);
    $('closeCreateOrder')?.addEventListener('click', closeModal);
    $('cancelCreateOrder')?.addEventListener('click', closeModal);

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
</script>

@stack('scripts')
</body>
</html>
