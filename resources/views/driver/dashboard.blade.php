<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>لوحة السائق — {{ $siteName ?? 'SERV.X' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: "Tajawal", system-ui, sans-serif; } .shadow-soft { box-shadow: 0 18px 60px rgba(0,0,0,.12); } </style>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="min-h-screen">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($siteLogoUrl ?? null)<img src="{{ $siteLogoUrl }}" alt="" class="h-9 w-9 rounded-xl object-cover">@endif
                <span class="font-extrabold text-lg">{{ $siteName ?? 'SERV.X' }} — السائق</span>
            </div>
            <form method="POST" action="{{ route('driver.logout') }}" class="inline">@csrf<button type="submit" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold hover:bg-slate-50">خروج</button></form>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        @if (session('success'))<div class="mb-6 p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="mb-6 p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800">{{ session('error') }}</div>@endif

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black">مركباتي وطلبات الخدمة</h1>
            <a href="{{ route('driver.request.create') }}" class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                <i class="fa-solid fa-plus me-2"></i>طلب خدمة جديد
            </a>
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 mb-8">
            <h2 class="font-black text-lg mb-4">المركبات المرتبطة بجوالك</h2>
            @if($vehicles->isEmpty())
                <p class="text-slate-500">لا توجد مركبات. تواصل مع شركتك لإضافة جوالك كسائق لمركبة.</p>
            @else
                <ul class="space-y-3">
                    @foreach($vehicles as $v)
                        <li class="flex items-center justify-between p-4 rounded-2xl border border-slate-100">
                            <div>
                                <span class="font-bold">{{ $v->make }} {{ $v->model }}</span>
                                <span class="text-slate-500 text-sm ms-2">— {{ $v->plate_number }}</span>
                                @if($v->company)<p class="text-xs text-slate-500 mt-1">{{ $v->company->company_name }}</p>@endif
                            </div>
                            <a href="{{ route('driver.request.create') }}?vehicle={{ $v->id }}" class="px-3 py-2 rounded-xl bg-sky-600 text-white text-sm font-semibold">طلب خدمة</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6">
            <h2 class="font-black text-lg mb-4">آخر الطلبات</h2>
            @if($requests->isEmpty())
                <p class="text-slate-500">لم تقم بأي طلبات بعد.</p>
            @else
                <ul class="space-y-3">
                    @foreach($requests as $r)
                        <li class="flex items-center justify-between p-4 rounded-2xl border border-slate-100">
                            <div>
                                <span class="font-bold">طلب #{{ $r->id }}</span>
                                <span class="text-slate-500 text-sm ms-2">— {{ $r->vehicle ? $r->vehicle->plate_number : '-' }}</span>
                                <p class="text-xs text-slate-500 mt-1">الحالة: {{ $r->status }} — {{ $r->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-xl text-sm font-semibold
                                @if($r->status === 'requested') bg-amber-100 text-amber-800
                                @elseif($r->status === 'completed') bg-emerald-100 text-emerald-800
                                @else bg-slate-100 text-slate-700 @endif">{{ $r->status }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </main>
</div>
</body>
</html>
