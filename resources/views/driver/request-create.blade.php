<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>طلب خدمة جديد — {{ $siteName ?? 'SERV.X' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: "Tajawal", system-ui, sans-serif; } .shadow-soft { box-shadow: 0 18px 60px rgba(0,0,0,.12); } </style>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="min-h-screen">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-2xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('driver.dashboard') }}" class="font-extrabold text-lg"><i class="fa-solid fa-arrow-right me-2"></i>{{ $siteName ?? 'SERV.X' }}</a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-black mb-6">طلب خدمة جديد</h1>
        <p class="text-slate-600 mb-6">اختر المركبة وأضف تفاصيل الموقع إن أردت. ستتلقى الشركة الطلب وتوافق عليه، ثم يتم تعيين فني للتواصل معك.</p>

        @if ($errors->any())<div class="mb-6 p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800"><ul class="list-disc ms-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <form method="POST" action="{{ route('driver.request.store') }}" class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 space-y-4" id="driver-request-form">
            @csrf
            <div>
                <label class="text-sm font-bold text-slate-700">المركبة *</label>
                <select name="vehicle_id" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100" id="vehicle_id">
                    <option value="">— اختر المركبة —</option>
                    @foreach($vehicles as $v)
                        @php $selected = old('vehicle_id') == $v->id || request('vehicle') == $v->id || (count($vehicles) === 1 && !old('vehicle_id')); @endphp
                        <option value="{{ $v->id }}" @selected($selected)>{{ $v->plate_number }} — {{ $v->make ?? '' }} {{ $v->model ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div id="services-wrap" class="hidden">
                <label class="text-sm font-bold text-slate-700">{{ __('common.required_services') }} *</label>
                <p class="text-slate-500 text-sm mt-1 mb-2">اختر خدمة أو أكثر ليعرف الفريق ماذا تحتاج.</p>
                <div id="services-list" class="mt-2 space-y-2 max-h-56 overflow-y-auto rounded-2xl border border-slate-200 p-3 bg-slate-50"></div>
                @error('service_ids')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">المدينة (اختياري)</label>
                <input type="text" name="city" value="{{ old('city') }}" placeholder="الرياض، جدة، ..." class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100" />
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">العنوان / موقع الخدمة (اختياري)</label>
                <textarea name="address" rows="3" placeholder="الحي، الشارع، معلم..." class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">{{ old('address') }}</textarea>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">ملاحظات (اختياري)</label>
                <textarea name="notes" rows="2" placeholder="أي تفاصيل إضافية..." class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3" id="submit-btn" disabled><i class="fa-solid fa-paper-plane me-2"></i>إرسال الطلب</button>
                <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 rounded-2xl border border-slate-200 font-bold">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </main>
</div>
@php
    $vehicleServicesForJs = [];
    $fallbackList = isset($fallbackServices) ? $fallbackServices->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray() : [];
    foreach ($vehicles as $v) {
        $list = [];
        if ($v->company && $v->company->relationLoaded('services') && $v->company->services->isNotEmpty()) {
            foreach ($v->company->services as $s) {
                $list[] = ['id' => $s->id, 'name' => $s->name];
            }
        }
        $vehicleServicesForJs[$v->id] = !empty($list) ? $list : $fallbackList;
    }
@endphp
<script>
(function () {
    var vehicleServices = @json($vehicleServicesForJs);
    var wrap = document.getElementById('services-wrap');
    var list = document.getElementById('services-list');
    var vehicleSelect = document.getElementById('vehicle_id');
    var submitBtn = document.getElementById('submit-btn');
    var form = document.getElementById('driver-request-form');

    function setSubmitEnabled() {
        var checked = list.querySelectorAll('input[name="service_ids[]"]:checked').length;
        submitBtn.disabled = checked === 0;
    }

    function updateServices() {
        var vid = vehicleSelect.value;
        list.innerHTML = '';
        if (!vid) {
            wrap.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = true;
            return;
        }
        wrap.classList.remove('hidden');
        var services = vehicleServices[vid];
        if (!services || services.length === 0) {
            list.innerHTML = '<p class="text-amber-700 p-3">لا توجد خدمات مسجّلة. تواصل مع شركتك أو المدير لإضافة الخدمات.</p>';
            if (submitBtn) submitBtn.disabled = true;
            return;
        }
        if (submitBtn) submitBtn.disabled = true;
        var oldIds = [];
        try { (form.querySelectorAll('input[name="service_ids[]"]:checked') || []).forEach(function (c) { oldIds.push(c.value); }); } catch (e) {}
        services.forEach(function (s) {
            var label = document.createElement('label');
            label.className = 'flex items-center gap-3 p-2 rounded-xl hover:bg-white cursor-pointer';
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.name = 'service_ids[]';
            cb.value = s.id;
            if (oldIds.indexOf(String(s.id)) !== -1) cb.checked = true;
            cb.addEventListener('change', setSubmitEnabled);
            label.appendChild(cb);
            label.appendChild(document.createTextNode(s.name));
            list.appendChild(label);
        });
        setSubmitEnabled();
    }

    vehicleSelect.addEventListener('change', updateServices);
    if (vehicleSelect.value) updateServices();
})();
</script>
</body>
</html>
