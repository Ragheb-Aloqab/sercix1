<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    @include('components.seo-meta', [
        'title' => __('index.create_company_account') . ' — ' . ($siteName ?? 'Servx Motors'),
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
    @vite(['resources/css/style.css'])
    <x-vite-cdn-fallback />
</head>

<body class="page-auth min-h-screen bg-servx-black text-servx-silver-light antialiased overflow-x-hidden font-servx">
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <a href="{{ url('/') }}" class="flex items-center justify-center gap-3 mb-6 group">
            @if($siteLogoUrl ?? null)
                <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'Servx Motors' }}" width="44" height="44" class="h-11 w-11 rounded-full object-cover border-2 border-servx-red/50 group-hover:border-servx-red transition-colors">
            @else
                <div class="h-11 w-11 rounded-full bg-servx-black-card border-2 border-servx-red/50 flex items-center justify-center text-servx-red font-bold text-lg">{{ strtoupper(substr($siteName ?? 'S', 0, 1)) }}</div>
            @endif
            <div>
                <div class="text-lg font-bold text-servx-silver-light group-hover:text-white transition-colors">{{ $siteName ?? 'Servx Motors' }}</div>
                <div class="text-xs text-servx-silver">{{ __('index.create_company_account') }}</div>
            </div>
        </a>

        <div class="bg-servx-black-card rounded-xl border border-servx-red/30 shadow-servx-card p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-white">{{ __('index.create_company_account') }}</h1>
            <p class="mt-2 text-sm text-servx-silver">
                {{ app()->getLocale() === 'ar' ? 'أدخل بيانات الشركة، ثم سنرسل رمز تحقق (OTP) لتفعيل الدخول.' : 'Enter company details, then we\'ll send a verification code (OTP) to activate your account.' }}
            </p>

            @if (session('success'))
                <div class="mt-4 rounded-lg border border-servx-red/30 bg-servx-red/10 p-3 text-sm text-servx-silver-light">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-servx-red/50 bg-servx-red/10 p-3 text-sm text-servx-silver-light">
                    <ul class="list-disc ms-5 space-y-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('company.register.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-servx-silver-light">{{ app()->getLocale() === 'ar' ? 'اسم الشركة' : 'Company name' }}</label>
                    <input name="name" value="{{ old('name') }}" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: شركة الزيت الذهبي' : 'e.g. Golden Oil Co.' }}"
                        class="mt-2 w-full rounded-lg border border-servx-red/30 bg-servx-black-soft px-4 py-3 text-servx-silver-light placeholder-servx-silver outline-none focus:border-servx-red focus:ring-2 focus:ring-servx-red/20" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-servx-silver-light">{{ app()->getLocale() === 'ar' ? 'رقم الجوال' : 'Phone number' }}</label>
                    <input name="phone" value="{{ old('phone') }}" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: +9665xxxxxxxx' : 'e.g. +9665xxxxxxxx' }}"
                        class="mt-2 w-full rounded-lg border border-servx-red/30 bg-servx-black-soft px-4 py-3 text-servx-silver-light placeholder-servx-silver outline-none focus:border-servx-red focus:ring-2 focus:ring-servx-red/20" />
                    <p class="mt-2 text-xs text-servx-silver">{{ app()->getLocale() === 'ar' ? '* يفضّل إدخال الرقم بصيغة السعودية.' : '* Prefer Saudi format.' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-servx-silver-light">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني (اختياري)' : 'Email (optional)' }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="example@company.com"
                        class="mt-2 w-full rounded-lg border border-servx-red/30 bg-servx-black-soft px-4 py-3 text-servx-silver-light placeholder-servx-silver outline-none focus:border-servx-red focus:ring-2 focus:ring-servx-red/20" />
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-servx-red hover:bg-servx-red-hover px-6 py-3 min-h-[44px] text-white font-bold transition-all duration-200 hover:scale-[1.02] active:scale-[0.99]">
                    {{ app()->getLocale() === 'ar' ? 'إنشاء الحساب وإرسال رمز التحقق' : 'Create account & send verification code' }}
                </button>

                <div class="text-xs text-servx-silver text-center">
                    {{ app()->getLocale() === 'ar' ? 'بإنشاء الحساب أنت توافق على الشروط وسياسة الخصوصية (Demo).' : 'By creating an account you agree to the terms and privacy policy (Demo).' }}
                </div>

                <div class="pt-2 text-center">
                    <a href="{{ route('sign-in.index') }}"
                        class="text-sm font-medium text-servx-silver hover:text-servx-red transition-colors">
                        {{ app()->getLocale() === 'ar' ? 'لدي حساب بالفعل — تسجيل الدخول' : 'Already have an account — Sign in' }}
                    </a>
                </div>
            </form>
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
