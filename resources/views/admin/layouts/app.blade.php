<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ session('ui.dir', app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}"
      class="{{ session('ui.theme') === 'dark' ? 'dark' : '' }} h-full scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
                    }
                }
            }
        }
    </script>

    <style>
        ::-webkit-scrollbar { height: 10px; width: 10px }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px }
        .dark ::-webkit-scrollbar-thumb { background: #475569 }
        /* RTL: sidebar on right, hide to right when closed */
        [dir="rtl"] #sidebar { left: auto; right: 0; }
        [dir="rtl"] #sidebar { transform: translateX(100%); }
        [dir="rtl"] #sidebar.sidebar-open { transform: translateX(0); }
        @media (min-width: 1024px) {
            [dir="rtl"] #sidebar { transform: translateX(0); }
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>

<body class="h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 overflow-x-hidden">

{{-- Backdrop --}}
<div id="backdrop" class="fixed inset-0 bg-black/40 hidden z-40 lg:hidden"></div>

<div class="min-h-screen flex w-full min-w-0">
    {{-- Sidebar --}}
    <livewire:dashboard.sidebar />

    {{-- Main --}}
    <main class="flex-1 min-w-0 w-full lg:ms-80 lg:min-w-0">
        {{-- Topbar --}}
        @include('admin.partials.topbar')

        {{-- Page Content --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6 w-full min-w-0">
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

    // Sidebar
    const sidebar = $('sidebar');
    const backdrop = $('backdrop');

    const isRtl = () => document.documentElement.dir === 'rtl';
    const openSidebar = () => {
        if (!sidebar) return;
        sidebar.classList.remove('translate-x-full', '-translate-x-full');
        sidebar.classList.add('sidebar-open');
        backdrop?.classList.remove('hidden');
    };
    const closeSidebar = () => {
        if (!sidebar) return;
        sidebar.classList.remove('sidebar-open');
        if (!isRtl()) sidebar.classList.add('translate-x-full');
        backdrop?.classList.add('hidden');
    };

    $('openSidebar')?.addEventListener('click', openSidebar);
    $('closeSidebar')?.addEventListener('click', closeSidebar);
    backdrop?.addEventListener('click', closeSidebar);

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

    // Responsive reset
    window.addEventListener('resize', () => {
        if (!sidebar || !backdrop) return;
        if (window.innerWidth >= 1024) {
            backdrop.classList.add('hidden');
            sidebar.classList.remove('translate-x-full', '-translate-x-full', 'sidebar-open');
        } else {
            sidebar.classList.remove('sidebar-open');
            if (!isRtl()) sidebar.classList.add('translate-x-full');
        }
    });
</script>

@stack('scripts')
</body>
</html>
