<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ __('login.password_title') }} — {{ $siteName ?? 'Servx Motors' }}</title>
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { servx: { black: '#0B0B0D', 'black-soft': '#111111', 'black-card': '#151515', red: '#DC2626', 'red-hover': '#EF4444', silver: '#B8B8B8', 'silver-light': '#E5E5E5' } }, fontFamily: { servx: ['Rajdhani', 'Tajawal', 'system-ui', 'sans-serif'] } } } };
    </script>
    @vite(['resources/css/style.css'])
    <x-vite-cdn-fallback />
</head>
<body class="page-auth min-h-screen bg-servx-black text-servx-silver-light antialiased font-servx">
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-sm">
        <a href="{{ url('/') }}" class="flex items-center justify-center gap-3 mb-6 group">
            @if($siteLogoUrl ?? null)
                <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'Servx Motors' }}" width="44" height="44" class="h-11 w-11 rounded-full object-cover border-2 border-servx-red/50 group-hover:border-servx-red transition-colors">
            @else
                <div class="h-11 w-11 rounded-full bg-servx-black-card border-2 border-servx-red/50 flex items-center justify-center text-servx-red font-bold text-lg">{{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}</div>
            @endif
            <span class="text-xl font-bold text-servx-silver-light group-hover:text-white transition-colors">{{ $siteName ?? 'Servx Motors' }}</span>
        </a>

        <div class="bg-servx-black-card rounded-xl border border-servx-red/30 shadow-servx-card p-6 sm:p-8">
            <h1 class="text-xl font-bold text-white">{{ __('login.password_title') ?? 'Enter Password' }}</h1>
            <p class="mt-1 text-sm text-servx-silver mb-6">{{ __('login.password_subtitle') ?? 'Sign in for' }}: <span class="font-semibold text-servx-silver-light">{{ $identifier ?? '' }}</span></p>
            <p class="mt-1 text-sm text-amber-400/80 mb-4">{{ __('login.admin_2fa_note') ?? 'After password, you will receive an OTP for two-factor verification.' }}</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-500/50 bg-rose-500/10 px-3 py-2 text-sm text-rose-400">
                    <ul class="list-disc ms-5 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.authenticate-password') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-servx-silver-light">{{ __('login.password_label') ?? 'Password' }}</label>
                    <input name="password" type="password" required autocomplete="current-password"
                        class="mt-1.5 block w-full rounded-lg border border-servx-red/30 bg-servx-black-soft px-3 py-2.5 text-servx-silver-light placeholder-servx-silver outline-none focus:border-servx-red focus:ring-2 focus:ring-servx-red/20" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 rounded border-servx-red/30 bg-servx-black-soft text-servx-red focus:ring-servx-red/20" />
                    <label for="remember" class="text-sm text-servx-silver">{{ __('login.remember_me') ?? 'Remember me' }}</label>
                </div>
                <button type="submit" class="w-full rounded-lg bg-servx-red hover:bg-servx-red-hover px-4 py-2.5 min-h-[44px] text-sm font-bold text-white transition-all duration-200 hover:scale-[1.02] active:scale-[0.99]">
                    {{ __('login.continue') ?? 'Continue' }}
                </button>
            </form>
            <a href="{{ route('login') }}" class="mt-4 block text-center text-sm font-medium text-servx-silver hover:text-servx-red transition-colors">{{ __('login.change_identifier') ?? 'Change email/phone' }}</a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-3 text-xs text-servx-silver">
            <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="{{ app()->getLocale() === 'ar' ? 'font-semibold text-servx-red' : 'hover:text-servx-red transition-colors' }}">العربية</a>
            <span>·</span>
            <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'font-semibold text-servx-red' : 'hover:text-servx-red transition-colors' }}">English</a>
        </div>
        <p class="mt-4 text-center text-xs text-servx-silver">© All Rights Reserved – Servix Motors</p>
    </div>
</div>
</body>
</html>
