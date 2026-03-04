<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="transition-colors duration-300">
<head>
    <x-theme-init />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ __('maintenance.verify_otp') ?? 'تأكيد الرمز' }} — {{ $siteName ?? 'Servx Motors' }}</title>
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { servx: { black: '#0B0B0D', 'black-soft': '#111111', 'black-card': '#151515', red: '#DC2626', silver: '#B8B8B8', 'silver-light': '#E5E5E5' } }, fontFamily: { servx: ['Rajdhani', 'Tajawal', 'system-ui', 'sans-serif'] } } } };
    </script>
    @vite(['resources/css/app.css', 'resources/css/style.css'])
    <x-vite-cdn-fallback />
</head>
<body class="page-auth min-h-screen bg-slate-50 dark:bg-servx-black text-slate-900 dark:text-servx-silver-light antialiased font-servx transition-colors duration-300">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10 relative">
        <div class="absolute top-4 end-4">
            @include('components.theme-toggle-vanilla')
        </div>
        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-servx-black-card rounded-xl border border-slate-200 dark:border-servx-red/30 shadow-lg dark:shadow-servx-card p-6 sm:p-8 transition-colors duration-300">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('maintenance.verify_otp') ?? 'تأكيد رمز التحقق' }}</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-servx-silver">{{ __('maintenance.code_sent_to') ?? 'تم إرسال الرمز إلى' }}: <span class="font-bold text-slate-900 dark:text-white">{{ $phone }}</span></p>

                @if (session('success'))
                    <div class="mt-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-3 text-sm text-emerald-400">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-rose-500/50 bg-rose-500/10 p-3 text-sm text-rose-400">
                        <ul class="list-disc ms-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('maintenance-center.verify') }}" class="mt-6">
                    @csrf
                    <label class="block text-sm font-medium text-slate-600 dark:text-servx-silver-light">{{ __('maintenance.otp_code') ?? 'رمز التحقق (6 أرقام)' }}</label>
                    <input type="text" name="otp" inputmode="numeric" maxlength="6" required placeholder="123456" class="mt-2 w-full tracking-widest text-center text-2xl font-bold rounded-lg border border-slate-300 dark:border-servx-red/30 bg-white dark:bg-servx-black-soft px-4 py-3 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                    <button type="submit" class="mt-4 w-full rounded-lg bg-servx-red hover:bg-servx-red-hover px-6 py-3 text-white font-bold">{{ __('maintenance.verify_and_login') ?? 'تحقق ودخول' }}</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
