<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>تسجيل الدخول — OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Tajawal", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }

        :root {
            --shadow: 0 18px 60px rgba(0, 0, 0, .12);
        }

        .shadow-soft {
            box-shadow: var(--shadow);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900">
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">

        <a href="{{ url('/') }}" class="flex items-center justify-center mb-6">
            <div class="bg-white rounded-2xl px-5 py-3 shadow-soft border border-slate-200 text-center">
                <div class="text-lg font-extrabold">OilGo Business</div>
                <div class="text-xs text-slate-500">تسجيل دخول الشركات</div>
            </div>
        </a>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-soft p-6 sm:p-8">

            {{-- اختيار نوع الدخول --}}
            <div class="grid grid-cols-2 gap-2 mb-6">
                <button type="button"
                        class="rounded-2xl px-4 py-3 font-bold text-sm border bg-slate-900 text-white">
                    شركة
                </button>

                <button type="button"
                        onclick="window.location.href='{{ route('login', ['as' => 'tech']) }}'"
                        class="rounded-2xl px-4 py-3 font-bold text-sm border border-slate-200 text-slate-600 hover:bg-slate-50">
                    فني
                </button>
            </div>

            <h1 class="text-2xl font-extrabold">تسجيل دخول شركة</h1>
            <p class="mt-2 text-sm text-slate-600">
                أدخل رقم الجوال لإرسال رمز تحقق (OTP).
            </p>

            @if (session('success'))
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
                    <ul class="list-disc ms-5 space-y-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('company.send_otp') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-bold text-slate-700">رقم الجوال</label>
                    <input name="phone"
                           value="{{ old('phone') }}"
                           placeholder="مثال: 05xxxxxxxx"
                           class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100" />
                    <p class="mt-2 text-xs text-slate-500">
                        * يفضّل إدخال الرقم بصيغة السعودية.
                    </p>
                </div>

                <button type="submit"
                        class="w-full rounded-2xl bg-slate-900 px-6 py-3 text-white font-extrabold hover:bg-slate-800 transition">
                    إرسال رمز التحقق
                </button>

                {{-- ✅ زر إنشاء حساب --}}
                <a href="{{ route('company.register') }}"
                   class="block w-full text-center rounded-2xl border border-slate-300 px-6 py-3 font-extrabold text-slate-700 hover:bg-slate-50 transition">
                    إنشاء حساب شركة
                </a>

                <div class="text-xs text-slate-500 text-center">
                    بالضغط على “إرسال” أنت توافق على الشروط وسياسة الخصوصية (Demo).
                </div>
            </form>
        </div>

        <div class="mt-4 text-center text-xs text-slate-500">
            © {{ date('Y') }} OilGo Business
        </div>
    </div>
</div>
</body>

</html>
