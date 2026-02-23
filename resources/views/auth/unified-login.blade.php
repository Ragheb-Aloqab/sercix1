<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        servx: {
                            black: '#0B0B0D',
                            'black-soft': '#111111',
                            'black-card': '#151515',
                            red: '#DC2626',
                            'red-hover': '#EF4444',
                            silver: '#B8B8B8',
                            'silver-light': '#E5E5E5',
                        }
                    },
                    fontFamily: { servx: ['Rajdhani', 'Tajawal', 'system-ui', 'sans-serif'] },
                    boxShadow: { 'servx-card': '0 8px 32px rgba(0,0,0,0.5)' }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    @vite(['resources/css/style.css'])
</head>
<body class="page-auth min-h-screen bg-servx-black text-servx-silver-light antialiased overflow-x-hidden font-servx">
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-sm">
        <a href="{{ url('/') }}" class="flex items-center justify-center gap-3 mb-6 group">
            @if($siteLogoUrl ?? null)
                <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'Servx Motors' }}" class="h-11 w-11 rounded-full object-cover border-2 border-servx-red/50 group-hover:border-servx-red transition-colors">
            @else
                <div class="h-11 w-11 rounded-full bg-servx-black-card border-2 border-servx-red/50 flex items-center justify-center text-servx-red font-bold text-lg">{{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}</div>
            @endif
            <span class="text-xl font-bold text-servx-silver-light group-hover:text-white transition-colors">{{ $siteName ?? 'Servx Motors' }}</span>
        </a>

        <div class="bg-servx-black-card rounded-xl border border-servx-red/30 shadow-servx-card p-6 sm:p-8">
            <h1 class="text-xl font-bold text-white">{{ __('login.title') }}</h1>
            <p class="mt-1 text-sm text-servx-silver mb-6">{{ __('login.unified_subtitle') }}</p>

            @if (session('success'))<div class="mb-4 rounded-lg border border-servx-red/30 bg-servx-red/10 px-3 py-2 text-sm text-servx-silver-light">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="mb-4 rounded-lg border border-servx-red/50 bg-servx-red/10 px-3 py-2 text-sm text-servx-silver-light">{{ session('error') }}</div>@endif
            @if ($errors->any())<div class="mb-4 rounded-lg border border-servx-red/50 bg-servx-red/10 px-3 py-2 text-sm text-servx-silver-light"><ul class="list-disc ms-5 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('sign-in.identify') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-servx-silver-light">{{ __('login.identifier_label') }}</label>
                    <input name="identifier" value="{{ old('identifier') }}" placeholder="{{ __('login.identifier_placeholder') }}"
                        class="mt-1.5 block w-full rounded-lg border border-servx-red/30 bg-servx-black-soft px-3 py-2.5 min-h-[44px] text-servx-silver-light placeholder-servx-silver outline-none focus:border-servx-red focus:ring-2 focus:ring-servx-red/20"
                        autocomplete="username" autofocus />
                </div>
                <button type="submit" class="w-full rounded-lg bg-servx-red hover:bg-servx-red-hover px-4 py-2.5 min-h-[44px] text-sm font-bold text-white transition-all duration-200 hover:scale-[1.02] active:scale-[0.99]">
                    {{ __('login.continue') }}
                </button>
            </form>

            <p class="mt-4 text-xs text-servx-silver text-center">
                {{ __('login.identifier_hint') }}
            </p>

            <a href="{{ route('company.register') }}" class="mt-4 block w-full text-center rounded-lg border border-servx-red/50 px-4 py-2.5 min-h-[44px] flex items-center justify-center text-sm font-semibold text-servx-silver-light hover:bg-servx-red/20 hover:text-white transition-colors active:scale-[0.99]">
                {{ __('login.create_company_account') }}
            </a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-3 text-xs text-servx-silver">
            <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="{{ app()->getLocale() === 'ar' ? 'font-semibold text-servx-red' : 'hover:text-servx-red transition-colors' }}">العربية</a>
            <span>·</span>
            <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'font-semibold text-servx-red' : 'hover:text-servx-red transition-colors' }}">English</a>
        </div>
        <p class="mt-4 text-center text-xs text-servx-silver">© {{ date('Y') }} {{ $siteName ?? 'Servx Motors' }}</p>
    </div>
</div>
</body>
</html>
