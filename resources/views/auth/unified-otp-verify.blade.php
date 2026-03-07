<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ __('login.verify_2fa_title') ?? 'Verify 2FA' }} — {{ $siteName ?? 'Servx Motors' }}</title>
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
            <h1 class="text-xl font-bold text-white">{{ __('login.verify_2fa_title') ?? 'Two-Factor Verification' }}</h1>
            <p class="mt-2 text-sm text-servx-silver">{{ __('login.verify_sent') ?? 'Code sent to' }}: <span class="font-bold text-servx-silver-light">{{ $phone }}</span></p>
            @if(app()->environment('local'))
                <p class="mt-1 text-xs text-amber-400">{{ __('login.otp_check_log') ?? 'Check log file for OTP in development' }}</p>
            @endif

            @if (session('success'))<div class="mt-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-3 text-sm text-emerald-400">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="mt-4 rounded-lg border border-rose-500/50 bg-rose-500/10 p-3 text-sm text-rose-400"><ul class="list-disc ms-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('login.verify-otp') }}" class="mt-6 space-y-4" id="otp-form">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-servx-silver-light">{{ __('login.verify_otp_label') ?? 'Verification code (6 digits)' }}</label>
                    <input name="otp" id="otp-input" type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required placeholder="123456"
                        class="mt-2 w-full tracking-[0.5em] text-center text-2xl font-bold rounded-lg border border-servx-red/30 bg-servx-black-soft px-4 py-3 text-servx-silver-light placeholder-servx-silver outline-none focus:border-servx-red focus:ring-2 focus:ring-servx-red/20" />
                </div>
                <button type="submit" class="w-full rounded-lg bg-servx-red hover:bg-servx-red-hover px-6 py-3 min-h-[44px] text-white font-bold transition-all duration-200 hover:scale-[1.02] active:scale-[0.99]">
                    {{ __('login.verify_submit') ?? 'Verify & Sign In' }}
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-600/50">
                <p id="resend-timer" class="text-sm text-servx-silver">
                    {{ __('login.resend_available_in') ?? 'Resend available in' }}: <span id="resend-countdown" class="font-mono font-bold text-servx-silver-light">{{ $resendAvailableIn }}</span>s
                </p>
                <div id="resend-wrap" class="mt-2" style="display:none">
                    <form method="POST" action="{{ route('login.resend-otp') }}" id="resend-form" class="inline">
                        @csrf
                        <button type="submit" id="resend-btn" class="text-sm font-medium text-servx-red hover:text-servx-red-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$canResend ? 'disabled' : '' }}>
                            {{ __('login.resend_code') ?? 'Resend Code' }}
                        </button>
                    </form>
                    <span id="resend-limit-msg" class="text-sm text-rose-400 ms-2" style="display:none">{{ __('login.resend_limit_reached') ?? 'Resend limit reached' }}</span>
                </div>
            </div>

            <a href="{{ route('login') }}" class="mt-4 block text-center text-sm font-medium text-servx-silver hover:text-servx-red transition-colors">{{ __('login.back_to_login') ?? 'Back to login' }}</a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-3 text-xs text-servx-silver">
            <a href="{{ route('set-locale', ['lang' => 'ar']) }}" class="{{ app()->getLocale() === 'ar' ? 'font-semibold text-servx-red' : 'hover:text-servx-red transition-colors' }}">العربية</a>
            <span>·</span>
            <a href="{{ route('set-locale', ['lang' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'font-semibold text-servx-red' : 'hover:text-servx-red transition-colors' }}">English</a>
        </div>
        <p class="mt-4 text-center text-xs text-servx-silver">© All Rights Reserved – Servix Motors</p>
    </div>
</div>
<script>
(function() {
    const cooldownSeconds = {{ $resendAvailableIn }};
    const timerEl = document.getElementById('resend-timer');
    const countdownEl = document.getElementById('resend-countdown');
    const resendWrap = document.getElementById('resend-wrap');
    const resendBtn = document.getElementById('resend-btn');
    const resendForm = document.getElementById('resend-form');
    const resendLimitMsg = document.getElementById('resend-limit-msg');
    const canResend = {{ $canResend ? 'true' : 'false' }};

    let remaining = cooldownSeconds;

    function updateCountdown() {
        if (remaining <= 0) {
            if (timerEl) timerEl.style.display = 'none';
            if (resendWrap) resendWrap.style.display = 'block';
            if (resendBtn && canResend) resendBtn.disabled = false;
            return;
        }
        if (countdownEl) countdownEl.textContent = remaining;
        remaining--;
        setTimeout(updateCountdown, 1000);
    }
    updateCountdown();

    if (resendForm) {
        resendForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = resendForm.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            fetch(resendForm.action, {
                method: 'POST',
                body: new FormData(resendForm),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    remaining = data.resend_available_in || 60;
                    if (timerEl) { timerEl.style.display = 'block'; countdownEl.textContent = remaining; }
                    if (resendWrap) resendWrap.style.display = 'none';
                    updateCountdown();
                    window.location.reload();
                } else {
                    if (data.resend_available_in) remaining = data.resend_available_in;
                    if (data.message && data.message.indexOf('limit') >= 0) {
                        resendLimitMsg.style.display = 'inline';
                        if (btn) btn.disabled = true;
                    }
                    if (btn) btn.disabled = false;
                }
            })
            .catch(() => { if (btn) btn.disabled = false; });
        });
    }

    document.getElementById('otp-input')?.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });
})();
</script>
</body>
</html>
