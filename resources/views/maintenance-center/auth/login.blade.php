<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="transition-colors duration-300">
<head>
    <x-theme-init />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ __('maintenance.center_login') ?? 'تسجيل دخول مركز الصيانة' }} — {{ $siteName ?? 'Servx Motors' }}</title>
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
    @vite(['resources/css/app.css', 'resources/css/style.css'])
    <x-vite-cdn-fallback />
</head>
<body class="page-auth min-h-screen bg-slate-50 dark:bg-servx-black text-slate-900 dark:text-servx-silver-light antialiased font-servx transition-colors duration-300">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10 relative">
        <div class="absolute top-4 end-4">
            <x-theme-toggle-vanilla />
        </div>
        <div class="w-full max-w-md">
            <a href="{{ url('/') }}" class="flex items-center justify-center gap-3 mb-6 group">
                <span class="text-xl font-bold text-slate-700 dark:text-white group-hover:text-slate-900 dark:group-hover:text-white transition-colors duration-300">{{ $siteName ?? 'Servx Motors' }}</span>
            </a>
            <div class="bg-white dark:bg-servx-black-card rounded-xl border border-slate-200 dark:border-servx-red/30 shadow-lg dark:shadow-none p-6 sm:p-8 transition-colors duration-300">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('maintenance.center_login') ?? 'تسجيل دخول مركز الصيانة' }}</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-servx-silver">{{ __('maintenance.center_login_help') ?? 'أدخل رقم الجوال لإرسال رمز التحقق' }}</p>

                @if (session('error'))
                    <div class="mt-4 rounded-lg border border-rose-400/50 dark:border-rose-500/50 bg-rose-50 dark:bg-rose-500/10 p-3 text-sm text-rose-700 dark:text-rose-400">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-rose-400/50 dark:border-rose-500/50 bg-rose-50 dark:bg-rose-500/10 p-3 text-sm text-rose-700 dark:text-rose-400">
                        <ul class="list-disc ms-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('maintenance-center.send-otp') }}" class="mt-6">
                    @csrf
                    <label class="block text-sm font-medium text-slate-600 dark:text-servx-silver-light">{{ __('maintenance.phone') ?? 'رقم الجوال' }}</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="05xxxxxxxx" class="mt-2 w-full rounded-lg border border-slate-300 dark:border-servx-red/30 bg-white dark:bg-servx-black-soft px-4 py-3 text-slate-900 dark:text-servx-silver-light placeholder-slate-400 dark:placeholder-servx-silver focus:border-sky-500 dark:focus:border-servx-red focus:ring-2 focus:ring-sky-200 dark:focus:ring-servx-red/20 transition-colors duration-300">
                    <button type="submit" class="mt-4 w-full rounded-lg bg-servx-red hover:bg-servx-red-hover px-6 py-3 text-white font-bold transition-colors duration-300">{{ __('maintenance.send_otp') ?? 'إرسال الرمز' }}</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
