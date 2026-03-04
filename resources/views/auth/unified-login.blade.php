<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="transition-colors duration-300">
<head>
    <x-theme-init />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    @include('components.seo-meta', [
        'title' => __('login.title') . ' — ' . ($siteName ?? 'Servx Motors'),
        'description' => config('seo.default_description'),
        'noindex' => true,
    ])
    @if($siteLogoUrl ?? null)
        <link rel="icon" href="{{ $siteLogoUrl }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/style.css', 'resources/js/app.js'])
    <x-vite-cdn-fallback />
    @livewireStyles
</head>
<body class="page-auth min-h-screen bg-slate-50 dark:bg-servx-black text-slate-900 dark:text-servx-silver-light antialiased overflow-x-hidden font-servx transition-colors duration-300">
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-10 relative">
    <div class="absolute top-4 end-4">
        <livewire:dashboard.theme-toggle-standalone />
    </div>
    <div class="w-full max-w-sm">
        <a href="{{ url('/') }}" class="flex items-center justify-center gap-3 mb-6 group">
            @if($siteLogoUrl ?? null)
                <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'Servx Motors' }}" width="44" height="44" class="h-11 w-11 rounded-full object-cover border-2 border-slate-300 dark:border-servx-red/50 group-hover:border-slate-900 dark:group-hover:border-servx-red transition-colors duration-300">
            @else
                <div class="h-11 w-11 rounded-full bg-slate-200 dark:bg-servx-black-card border-2 border-slate-300 dark:border-servx-red/50 flex items-center justify-center text-slate-700 dark:text-servx-red font-bold text-lg">{{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}</div>
            @endif
            <span class="text-xl font-bold text-slate-700 dark:text-servx-silver-light group-hover:text-slate-900 dark:group-hover:text-white transition-colors duration-300">{{ $siteName ?? 'Servx Motors' }}</span>
        </a>

        <div class="bg-white dark:bg-servx-black-card rounded-xl border border-slate-200 dark:border-servx-red/30 shadow-lg dark:shadow-servx-card p-6 sm:p-8 transition-colors duration-300">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white mb-6">{{ __('login.title') }}</h1>

            @if (session('success'))<div class="mb-4 rounded-lg border border-emerald-400/50 dark:border-servx-red/30 bg-emerald-50 dark:bg-servx-red/10 px-3 py-2 text-sm text-emerald-800 dark:text-servx-silver-light">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="mb-4 rounded-lg border border-rose-400/50 dark:border-servx-red/50 bg-rose-50 dark:bg-servx-red/10 px-3 py-2 text-sm text-rose-800 dark:text-servx-silver-light">{{ session('error') }}</div>@endif
            @if ($errors->any())<div class="mb-4 rounded-lg border border-rose-400/50 dark:border-servx-red/50 bg-rose-50 dark:bg-servx-red/10 px-3 py-2 text-sm text-rose-800 dark:text-servx-silver-light"><ul class="list-disc ms-5 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('login.identify') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-servx-silver-light">{{ __('login.identifier_label') }}</label>
                    <input name="identifier" value="{{ old('identifier') }}" placeholder="{{ __('login.identifier_placeholder') }}"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 dark:border-servx-red/30 bg-white dark:bg-servx-black-soft px-3 py-2.5 min-h-[44px] text-slate-900 dark:text-servx-silver-light placeholder-slate-400 dark:placeholder-servx-silver outline-none focus:border-sky-500 dark:focus:border-servx-red focus:ring-2 focus:ring-sky-200 dark:focus:ring-servx-red/20 transition-colors duration-300"
                        autocomplete="username" autofocus />
                </div>
                <button type="submit" class="w-full rounded-lg bg-servx-red hover:bg-servx-red-hover px-4 py-2.5 min-h-[44px] text-sm font-bold text-white transition-all duration-200 hover:scale-[1.02] active:scale-[0.99]">
                    {{ __('login.continue') }}
                </button>
            </form>

            <a href="{{ route('company.register') }}" class="mt-4 block w-full text-center rounded-lg border border-slate-300 dark:border-servx-red/50 px-4 py-2.5 min-h-[44px] flex items-center justify-center text-sm font-semibold text-slate-600 dark:text-servx-silver-light hover:bg-slate-100 dark:hover:bg-servx-red/20 hover:text-slate-900 dark:hover:text-white transition-colors duration-300 active:scale-[0.99]">
                {{ __('login.create_company_account') }}
            </a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-3 text-xs text-slate-500 dark:text-servx-silver">
            <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="{{ app()->getLocale() === 'ar' ? 'font-semibold text-sky-600 dark:text-servx-red' : 'hover:text-slate-900 dark:hover:text-servx-red transition-colors duration-300' }}">العربية</a>
            <span>·</span>
            <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'font-semibold text-sky-600 dark:text-servx-red' : 'hover:text-slate-900 dark:hover:text-servx-red transition-colors duration-300' }}">English</a>
        </div>
        <p class="mt-4 text-center text-xs text-slate-500 dark:text-servx-silver">© {{ date('Y') }} {{ $siteName ?? 'Servx Motors' }}</p>
    </div>
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
