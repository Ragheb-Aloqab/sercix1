<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ app()->getLocale() === 'ar' ? 'تأكيد OTP' : 'Verify OTP' }} — {{ $siteName ?? 'Servx Motors' }}</title>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const expiresAt = {{ $expiresAt ?? 0 }};
            const timerEl = document.getElementById('otp-timer');
            const timerVal = document.getElementById('otp-timer-value');
            const resendWrap = document.getElementById('otp-resend-wrap');

            function updateTimer() {
                const now = Math.floor(Date.now() / 1000);
                const left = Math.max(0, expiresAt - now);
                if (left <= 0) {
                    if (timerEl) timerEl.style.display = 'none';
                    if (resendWrap) resendWrap.style.display = 'flex';
                    return;
                }
                const m = Math.floor(left / 60);
                const s = left % 60;
                if (timerVal) timerVal.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                setTimeout(updateTimer, 1000);
            }
            updateTimer();
        });
    </script>
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
                <span class="text-xl font-bold text-servx-silver-light group-hover:text-white transition-colors">{{ $siteName ?? 'Servx Motors' }}</span>
            </a>

            <div class="bg-servx-black-card rounded-xl border border-servx-red/30 shadow-servx-card p-6 sm:p-8">
                <h1 class="text-2xl font-bold text-white">{{ app()->getLocale() === 'ar' ? 'تأكيد رمز التحقق' : 'Verify code' }}</h1>
                <p class="mt-2 text-sm text-servx-silver">
                    {{ app()->getLocale() === 'ar' ? 'تم إرسال رمز إلى:' : 'Code sent to:' }} <span class="font-bold text-servx-silver-light">{{ $phone }}</span>
                    @if(app()->environment('local'))
                        <br><span class="text-xs text-servx-silver">({{ app()->getLocale() === 'ar' ? 'تجريبي: الرمز موجود في ملف log' : 'Demo: check log file' }})</span>
                    @endif
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

                <form method="POST" action="{{ route('company.verify_otp') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-servx-silver-light">{{ app()->getLocale() === 'ar' ? 'رمز التحقق (6 أرقام)' : 'Verification code (6 digits)' }}</label>
                        <input name="otp" inputmode="numeric" maxlength="6" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: 123456' : 'e.g. 123456' }}"
                            class="mt-2 w-full tracking-widest text-center text-2xl font-bold rounded-lg border border-servx-red/30 bg-servx-black-soft px-4 py-3 text-servx-silver-light placeholder-servx-silver outline-none focus:border-servx-red focus:ring-2 focus:ring-servx-red/20" />
                        <p class="mt-2 text-xs text-servx-silver">{{ app()->getLocale() === 'ar' ? '* أدخل الرمز قبل انتهاء الصلاحية.' : '* Enter the code before it expires.' }}</p>
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-servx-red hover:bg-servx-red-hover px-6 py-3 min-h-[44px] text-white font-bold transition-all duration-200 hover:scale-[1.02] active:scale-[0.99]">
                        {{ app()->getLocale() === 'ar' ? 'تحقق ودخول' : 'Verify & sign in' }}
                    </button>

                    <div class="mt-4 space-y-2">
                        <p id="otp-timer" class="text-sm text-servx-silver">
                            {{ app()->getLocale() === 'ar' ? 'إعادة الإرسال خلال:' : 'Resend in:' }} <span id="otp-timer-value" class="font-mono font-bold text-servx-silver-light">02:00</span>
                        </p>
                        <div id="otp-resend-wrap" class="flex items-center justify-between text-sm" style="display:none">
                            <a href="{{ $isRegistration ?? false ? route('company.register') : route('sign-in.index') }}" class="font-medium text-servx-silver hover:text-servx-red transition-colors">
                                {{ app()->getLocale() === 'ar' ? 'تعديل رقم الجوال' : 'Change phone number' }}
                            </a>
                            @if($isRegistration ?? false)
                                <form method="POST" action="{{ route('company.resend_register_otp') }}" class="inline">
                                    @csrf
                                    <button type="submit" id="otp-resend-btn" class="font-medium text-servx-red hover:text-servx-red-hover transition-colors">
                                        {{ app()->getLocale() === 'ar' ? 'إعادة إرسال الرمز' : 'Send again' }}
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('company.send_otp') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="phone" value="{{ $phone }}">
                                    <button type="submit" id="otp-resend-btn" class="font-medium text-servx-red hover:text-servx-red-hover transition-colors">
                                        {{ app()->getLocale() === 'ar' ? 'إعادة إرسال' : 'Send again' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </form>
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
