<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('components.seo-meta', [
        'title' => __('login.title') . ' — ' . ($siteName ?? config('app.name', 'Servx Motors')),
        'description' => config('seo.default_description'),
        'noindex' => true,
    ])
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @livewireStyles
    @vite(['resources/js/app.js', 'resources/css/style.css'])
    <x-vite-cdn-fallback />
</head>
<body class="page-auth min-h-screen bg-gradient-to-b from-slate-50 to-slate-100/80 text-slate-800 antialiased overflow-x-hidden">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 sm:py-14">
        {{-- Card container --}}
        <div class="w-full max-w-sm">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center justify-center gap-3 mb-8">
                @if($siteLogoUrl ?? null)
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? config('app.name', 'Servx Motors') }}" width="44" height="44" class="h-11 w-11 rounded-xl object-cover ring-2 ring-white shadow-lg">
                @else
                    <div class="h-11 w-11 rounded-xl bg-slate-800 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                        {{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}
                    </div>
                @endif
                <span class="text-xl font-bold text-slate-800">{{ $siteName ?? config('app.name', 'Servx Motors') }}</span>
            </a>

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200/80 p-6 sm:p-8">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            <div class="mt-6 flex items-center justify-center gap-3 text-xs text-slate-500">
                <a href="{{ url('/') }}" class="hover:text-slate-700">{{ __('login.back_to_home') }}</a>
                <span>·</span>
                <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="{{ app()->getLocale() === 'ar' ? 'font-semibold text-slate-700' : 'hover:text-slate-700' }}">العربية</a>
                <span>·</span>
                <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'font-semibold text-slate-700' : 'hover:text-slate-700' }}">English</a>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
