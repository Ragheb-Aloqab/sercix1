<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="transition-colors duration-300">
    <head>
        <x-theme-init />
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('components.seo-meta', [
            'title' => trim((string) ($__env->yieldContent('title') ?? '')) ?: config('app.name', 'Laravel'),
            'description' => config('seo.default_description'),
            'noindex' => request()->is('profile') || request()->is('dashboard*'),
        ])
        @if($siteLogoUrl ?? null)
            <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
        @else
            <link rel="icon" href="{{ asset('favicon.ico') }}" />
        @endif

        <!-- Fonts: preconnect + swap for faster text render -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/style.css', 'resources/js/app.js'])
        <x-vite-cdn-fallback />
        @livewireStyles
    </head>
    <body class="font-sans antialiased transition-colors duration-300 bg-gray-100 dark:bg-slate-900 text-gray-900 dark:text-slate-100">
        <div class="min-h-screen">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-slate-800 shadow dark:shadow-slate-900/50 transition-colors duration-300">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
        <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('ui-theme-changed', ({ theme }) => {
                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.style.colorScheme = theme === 'dark' ? 'dark' : 'light';
                try { localStorage.setItem('sercix_theme', theme); } catch (e) {}
            });
        });
        </script>
    </body>
</html>
