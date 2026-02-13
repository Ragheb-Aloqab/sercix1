<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('ui.dir', app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ __('login.password_title') }} — {{ $siteName ?? 'SERV.X' }}</title>
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { boxShadow: { 'soft': '0 25px 50px -12px rgba(0,0,0,.15)' } } } }</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',system-ui,sans-serif}</style>
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100/80 text-slate-800">
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-sm">
        <a href="{{ url('/') }}" class="flex items-center justify-center gap-3 mb-6">
            @if($siteLogoUrl ?? null)
                <img src="{{ $siteLogoUrl }}" alt="" class="h-11 w-11 rounded-xl object-cover ring-2 ring-white shadow-lg">
            @else
                <div class="h-11 w-11 rounded-xl bg-slate-800 flex items-center justify-center text-white font-bold text-lg">{{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}</div>
            @endif
            <span class="text-xl font-bold text-slate-800">{{ $siteName ?? 'SERV.X' }}</span>
        </a>

        <div class="bg-white rounded-2xl shadow-soft border border-slate-200/80 p-6 sm:p-8">
            <h1 class="text-xl font-semibold text-slate-900">{{ __('login.password_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500 mb-6">{{ __('login.password_subtitle') }}: <span class="font-semibold text-slate-700">{{ $email }}</span></p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                    <ul class="list-disc ms-5 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('sign-in.authenticate_password') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('login.password_label') }}</label>
                    <input name="password" type="password" required autocomplete="current-password"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 rounded border-slate-300 text-slate-800 focus:ring-slate-400" />
                    <label for="remember" class="text-sm text-slate-600">{{ __('login.remember_me') }}</label>
                </div>
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    {{ __('login.sign_in') }}
                </button>
            </form>
            <a href="{{ route('sign-in.index') }}" class="mt-4 block text-center text-sm font-medium text-slate-600 hover:text-slate-800">
                {{ __('login.change_email') }}
            </a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-3 text-xs text-slate-500">
            <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="{{ app()->getLocale() === 'ar' ? 'font-semibold text-slate-700' : 'hover:text-slate-700' }}">العربية</a>
            <span>·</span>
            <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'font-semibold text-slate-700' : 'hover:text-slate-700' }}">English</a>
        </div>
        <p class="mt-4 text-center text-xs text-slate-500">© {{ date('Y') }} {{ $siteName ?? 'SERV.X' }}</p>
    </div>
</div>
</body>
</html>
