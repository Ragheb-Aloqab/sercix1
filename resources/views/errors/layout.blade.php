{{--
    Base layout for all error pages (401, 403, 404, 419, 500).
    Keeps consistent styling across error views.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('errors.page_error')) — {{ config('app.name', 'Laravel') }}</title>
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-vite-cdn-fallback />
</head>
<body class="page-figtree font-sans antialiased min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        @yield('content')
    </div>
</body>
</html>
