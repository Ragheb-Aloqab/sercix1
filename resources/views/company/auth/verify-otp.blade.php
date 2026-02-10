<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>تأكيد OTP</title>
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
            <div class="bg-white border border-slate-200 rounded-3xl shadow-soft p-6 sm:p-8">
                <h1 class="text-2xl font-extrabold">تأكيد رمز التحقق</h1>
                <p class="mt-2 text-sm text-slate-600">
                    تم إرسال رمز إلى: <span class="font-extrabold">{{ $phone }}</span>
                    <br>
                    (تجريبي: الرمز موجود في ملف log)
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

                <form method="POST" action="{{ route('company.verify_otp') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="text-sm font-bold text-slate-700">رمز التحقق (6 أرقام)</label>
                        <input name="otp" inputmode="numeric" maxlength="6" placeholder="مثال: 123456"
                            class="mt-2 w-full tracking-widest text-center text-2xl font-extrabold rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100" />
                        <p class="mt-2 text-xs text-slate-500">* أدخل الرمز قبل انتهاء الصلاحية.</p>
                    </div>

                    <button type="submit"
                        class="w-full rounded-2xl bg-emerald-600 px-6 py-3 text-white font-extrabold hover:bg-emerald-700 transition">
                        تحقق ودخول
                    </button>

                    <div class="flex items-center justify-between text-sm">
                        <a href="{{ route('company.login') }}" class="font-bold text-slate-700 hover:text-slate-900">
                            تعديل رقم الجوال
                        </a>

                        <form method="POST" action="{{ route('company.send_otp') }}">
                            @csrf
                            <input type="hidden" name="phone" value="{{ $phone }}">
                            <button type="submit" class="font-bold text-emerald-700 hover:text-emerald-800">
                                إعادة إرسال
                            </button>
                        </form>
                    </div>
                </form>
            </div>

            <div class="mt-4 text-center text-xs text-slate-500">
                © {{ date('Y') }} {{ $siteName ?? 'SERV.X' }}
            </div>
        </div>
    </div>
</body>

</html>
