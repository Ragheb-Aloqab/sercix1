<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>تسجيل الدخول — {{ $siteName ?? 'SERV.X' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: "Tajawal", system-ui, sans-serif; } .shadow-soft { box-shadow: 0 18px 60px rgba(0,0,0,.12); } </style>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <a href="{{ url('/') }}" class="flex items-center justify-center mb-6">
            <div class="bg-white rounded-2xl px-5 py-3 shadow-soft border border-slate-200 text-center flex items-center gap-3">
                @if($siteLogoUrl ?? null)<img src="{{ $siteLogoUrl }}" alt="" class="h-10 w-10 rounded-xl object-cover">@endif
                <div>
                    <div class="text-lg font-extrabold">{{ $siteName ?? 'SERV.X' }}</div>
                    <div class="text-xs text-slate-500">تسجيل الدخول</div>
                </div>
            </div>
        </a>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-soft p-6 sm:p-8">
            <h1 class="text-2xl font-extrabold">تسجيل الدخول</h1>
            <p class="mt-2 text-sm text-slate-600 mb-4">اختر نوع الدخول ثم أدخل رقم الجوال (للشركة أو السائق).</p>

            {{-- اختيار نوع الدخول --}}
            <div class="grid grid-cols-3 gap-2 mb-6">
                <a href="{{ route('login') }}" class="rounded-2xl px-4 py-3 font-bold text-sm border-2 border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-400 transition text-center">
                    <i class="fa-solid fa-wrench block text-lg mb-1"></i>
                    فني
                </a>
                <button type="button" data-role="company" class="role-btn rounded-2xl px-4 py-3 font-bold text-sm border-2 border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-400 transition text-center">
                    <i class="fa-solid fa-building block text-lg mb-1"></i>
                    شركة
                </button>
                <button type="button" data-role="driver" class="role-btn rounded-2xl px-4 py-3 font-bold text-sm border-2 border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-400 transition text-center">
                    <i class="fa-solid fa-car block text-lg mb-1"></i>
                    سائق
                </button>
            </div>

            @if (session('success'))<div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">{{ session('error') }}</div>@endif
            @if ($errors->any())<div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800"><ul class="list-disc ms-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <div id="phone-form-wrap" class="hidden mt-4">
                <form method="POST" action="{{ route('sign-in.send_otp') }}" class="space-y-4" id="phone-form">
                    @csrf
                    <input type="hidden" name="role" id="input-role" value="" />
                    <div>
                        <label class="text-sm font-bold text-slate-700">رقم الجوال</label>
                        <input name="phone" value="{{ old('phone') }}" placeholder="05xxxxxxxx أو +9665xxxxxxxx"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100" />
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-slate-900 px-6 py-3 text-white font-extrabold hover:bg-slate-800">إرسال رمز التحقق</button>
                </form>
            </div>

            <a href="{{ route('company.register') }}" class="mt-4 block w-full text-center rounded-2xl border border-slate-300 px-6 py-3 font-bold text-slate-700 hover:bg-slate-50">
                إنشاء حساب شركة
            </a>
        </div>
        <p class="mt-4 text-center text-xs text-slate-500">© {{ date('Y') }} {{ $siteName ?? 'SERV.X' }}</p>
    </div>
</div>
<script>
(function () {
    var wrap = document.getElementById('phone-form-wrap');
    var form = document.getElementById('phone-form');
    var inputRole = document.getElementById('input-role');
    var roleBtns = document.querySelectorAll('.role-btn');

    roleBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var role = this.getAttribute('data-role');
            roleBtns.forEach(function (b) {
                b.classList.remove('border-emerald-600', 'bg-emerald-50', 'text-emerald-800');
                b.classList.add('border-slate-200', 'text-slate-600');
            });
            this.classList.remove('border-slate-200');
            this.classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-800');
            inputRole.value = role;
            wrap.classList.remove('hidden');
        });
    });
    var initialRole = '{{ old('role') }}';
    if (initialRole) {
        inputRole.value = initialRole;
        wrap.classList.remove('hidden');
        var sel = document.querySelector('.role-btn[data-role="' + initialRole + '"]');
        if (sel) {
            roleBtns.forEach(function (b) { b.classList.remove('border-emerald-600', 'bg-emerald-50', 'text-emerald-800'); b.classList.add('border-slate-200', 'text-slate-600'); });
            sel.classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-800');
            sel.classList.remove('border-slate-200');
        }
    }
})();
</script>
</body>
</html>
