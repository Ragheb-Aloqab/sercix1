<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="transition-colors duration-300">
<head>
    <x-theme-init />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    @if($wlBranding ?? false)
    <meta name="theme-color" content="{{ app('tenant')->getResolvedPrimaryColor() }}" />
    @endif
    @include('components.seo-meta', [
        'title' => __('login.verify_title') . ' — ' . ($brandTitle ?? $siteName ?? 'Servx Motors'),
        'description' => config('seo.default_description'),
        'noindex' => true,
    ])
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    @if($wlBranding ?? false)
    <style>
        :root {
            --wl-primary: {{ app('tenant')->getResolvedPrimaryColor() }};
            --wl-secondary: {{ app('tenant')->getResolvedSecondaryColor() }};
            --tenant-primary: var(--wl-primary);
            --tenant-secondary: var(--wl-secondary);
            --tenant-primary-hover: color-mix(in srgb, var(--wl-primary) 85%, white);
            --tenant-secondary-hover: color-mix(in srgb, var(--wl-secondary) 85%, white);
        }
        .page-auth .auth-btn-primary { background-color: var(--tenant-primary) !important; }
        .page-auth .auth-btn-primary:hover { background-color: var(--tenant-primary-hover) !important; }
        .page-auth a.text-sky-600, .page-auth a.text-servx-red { color: var(--tenant-primary) !important; }
    </style>
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/style.css', 'resources/js/app.js'])
    <x-vite-cdn-fallback />
    @livewireStyles
</head>
<body class="page-auth min-h-screen bg-slate-50 dark:bg-servx-black text-slate-900 dark:text-servx-silver-light antialiased overflow-x-hidden font-servx transition-colors duration-300" @if($wlBranding ?? false) data-wl-branding @endif>
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-10 relative">
    <div class="absolute top-4 end-4 wl-theme-toggle-wrap">
        <livewire:dashboard.theme-toggle-standalone />
    </div>
    <div class="w-full max-w-sm">
        <a href="{{ url('/') }}" class="flex items-center justify-center gap-3 mb-6 group">
            @if($siteLogoUrl ?? null)
                <img src="{{ $siteLogoUrl }}" alt="{{ $brandTitle ?? $siteName ?? 'Servx Motors' }}" width="44" height="44" class="h-11 w-11 rounded-full object-cover border-2 border-slate-300 dark:border-servx-red/50 group-hover:border-slate-900 dark:group-hover:border-servx-red transition-colors duration-300">
            @else
                <div class="h-11 w-11 rounded-full bg-slate-200 dark:bg-servx-black-card border-2 border-slate-300 dark:border-servx-red/50 flex items-center justify-center text-slate-700 dark:text-servx-red font-bold text-lg">{{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}</div>
            @endif
            <span class="text-xl font-bold text-slate-700 dark:text-servx-silver-light group-hover:text-slate-900 dark:group-hover:text-white transition-colors duration-300">{{ $brandTitle ?? $siteName ?? 'Servx Motors' }}</span>
        </a>

        <div class="bg-white dark:bg-servx-black-card rounded-xl border border-slate-200 dark:border-servx-red/30 shadow-lg dark:shadow-servx-card p-6 sm:p-8 transition-colors duration-300">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('login.verify_title') }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-servx-silver">{{ __('login.verify_sent') }}: <span class="font-bold text-slate-900 dark:text-servx-silver-light">{{ $phone }}</span></p>

            @if (session('success'))<div class="mt-4 rounded-lg border border-emerald-400/50 dark:border-servx-red/30 bg-emerald-50 dark:bg-servx-red/10 px-3 py-2 text-sm text-emerald-800 dark:text-servx-silver-light">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="mt-4 rounded-lg border border-rose-400/50 dark:border-servx-red/50 bg-rose-50 dark:bg-servx-red/10 px-3 py-2 text-sm text-rose-800 dark:text-servx-silver-light"><ul class="list-disc ms-5 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('login.verify.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-servx-silver-light">{{ __('login.verify_otp_label') }}</label>
                    <input name="otp" inputmode="numeric" maxlength="6" placeholder="{{ __('login.verify_otp_placeholder') }}"
                        class="mt-2 w-full tracking-widest text-center text-2xl font-bold rounded-lg border border-slate-300 dark:border-servx-red/30 bg-white dark:bg-servx-black-soft px-4 py-3 text-slate-900 dark:text-servx-silver-light placeholder-slate-400 dark:placeholder-servx-silver outline-none focus:border-sky-500 dark:focus:border-servx-red focus:ring-2 focus:ring-sky-200 dark:focus:ring-servx-red/20 transition-colors duration-300" />
                </div>
                <button type="submit" class="w-full rounded-lg px-4 py-3 min-h-[44px] text-sm font-bold text-white transition-all duration-200 hover:scale-[1.02] active:scale-[0.99] {{ ($wlBranding ?? false) ? 'auth-btn-primary' : 'bg-servx-red hover:bg-servx-red-hover' }}">
                    {{ __('login.verify_submit') }}
                </button>
            </form>
            <a href="{{ route('login') }}" class="mt-4 block text-center text-sm font-medium text-slate-600 dark:text-servx-silver hover:text-sky-600 dark:hover:text-servx-red transition-colors duration-300">{{ __('login.change_phone') }}</a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-3 text-xs text-slate-500 dark:text-servx-silver">
            <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="{{ app()->getLocale() === 'ar' ? 'font-semibold text-sky-600 dark:text-servx-red' : 'hover:text-slate-900 dark:hover:text-servx-red transition-colors duration-300' }}">العربية</a>
            <span>·</span>
            <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'font-semibold text-sky-600 dark:text-servx-red' : 'hover:text-slate-900 dark:hover:text-servx-red transition-colors duration-300' }}">English</a>
        </div>
        <p class="mt-4 text-center text-xs text-slate-500 dark:text-servx-silver">© All Rights Reserved – {{ $siteName ?? 'Servx Motors' }}</p>
    </div>
</div>
@livewireScripts
<script src="{{ asset('js/theme-livewire.js') }}"></script>
</body>
</html>
