@extends('layouts.driver')

@section('title', __('driver.new_service_request'))

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <h1 class="text-2xl font-black mb-6">طلب خدمة جديد</h1>
    <p class="text-slate-600 mb-6">اختر المركبة وخدمة واحدة (من القائمة أو خدمة مخصصة) مع إدخال السعر. الطلب سيكون قيد الموافقة حتى توافق الشركة.</p>

    @if ($errors->any())<div class="mb-6 p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800"><ul class="list-disc ms-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('driver.request.store') }}" enctype="multipart/form-data" class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 space-y-4" id="driver-request-form">
        @csrf
        <input type="hidden" name="service_type" id="service_type" value="existing">
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

        {{-- Service type toggle --}}
        <div>
            <label class="text-sm font-bold text-slate-700">نوع الخدمة *</label>
            <div class="mt-2 flex gap-2">
                <label class="flex-1 p-3 rounded-2xl border cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                    <input type="radio" name="service_type_radio" value="existing" checked class="sr-only" id="type_existing">
                    <span class="font-bold">خدمة من القائمة</span>
                </label>
                <label class="flex-1 p-3 rounded-2xl border cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                    <input type="radio" name="service_type_radio" value="custom" class="sr-only" id="type_custom">
                    <span class="font-bold">خدمة أخرى (مخصصة)</span>
                </label>
            </div>
        </div>

        {{-- Existing service --}}
        <div id="existing-wrap">
            <label class="text-sm font-bold text-slate-700">{{ __('common.required_services') }} *</label>
            <p class="text-slate-500 text-sm mt-1 mb-2">اختر خدمة واحدة وأدخل السعر.</p>
            <div id="services-list" class="mt-2 space-y-2 max-h-48 overflow-y-auto rounded-2xl border border-slate-200 p-3 bg-slate-50"></div>
            <div class="mt-2" id="price-wrap-existing">
                <label class="text-sm font-bold text-slate-700">سعر الخدمة (ر.س) *</label>
                <input type="number" name="service_price" id="service_price" step="0.01" min="0.01" placeholder="0.00" value="{{ old('service_price') }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">
                @error('service_price')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            @error('service_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        {{-- Custom service --}}
        <div id="custom-wrap" class="hidden space-y-3">
            <div>
                <label class="text-sm font-bold text-slate-700">اسم الخدمة *</label>
                <input type="text" name="custom_service_name" id="custom_service_name" value="{{ old('custom_service_name') }}" placeholder="مثال: تغيير إطارات" maxlength="255" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">
                @error('custom_service_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">وصف الخدمة *</label>
                <textarea name="custom_service_description" id="custom_service_description" rows="3" placeholder="وصف تفصيلي للخدمة المطلوبة" maxlength="1000" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">{{ old('custom_service_description') }}</textarea>
                @error('custom_service_description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">سعر الخدمة (ر.س) *</label>
                <input type="number" name="custom_service_price" id="custom_service_price" step="0.01" min="0.01" placeholder="0.00" value="{{ old('custom_service_price') }}" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100">
                @error('custom_service_price')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Quotation invoice (required) --}}
        <div>
            <label class="text-sm font-bold text-slate-700">{{ __('driver.upload_quotation_invoice') }} *</label>
            <p class="text-slate-500 text-sm mt-1 mb-2">{{ __('driver.upload_quotation_invoice_help') }}</p>
            <input type="file" name="quotation_invoice" id="quotation_invoice" accept=".pdf,.jpg,.jpeg,.png"
                   class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-emerald-100 file:me-2 file:rounded-xl file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-emerald-700 file:font-semibold">
            @error('quotation_invoice')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
            <div id="quotation-preview" class="mt-3 hidden">
                <p class="text-sm font-semibold text-slate-600 mb-2">{{ __('driver.quotation_preview') }}:</p>
                <div id="quotation-preview-content" class="rounded-xl border border-slate-200 overflow-hidden max-w-xs bg-slate-50"></div>
                <p id="quotation-filename" class="text-xs text-slate-500 mt-1"></p>
            </div>
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
            <button type="submit" class="flex-1 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3 disabled:opacity-50 disabled:cursor-not-allowed" id="submit-btn" disabled><i class="fa-solid fa-paper-plane me-2"></i>{{ __('driver.submit_request') ?? 'إرسال الطلب' }}</button>
            <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 rounded-2xl border border-slate-200 font-bold">{{ __('common.cancel') }}</a>
        </div>
    </form>
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
@push('scripts')
<script>
(function () {
    var vehicleServices = @json($vehicleServicesForJs);
    var existingWrap = document.getElementById('existing-wrap');
    var customWrap = document.getElementById('custom-wrap');
    var list = document.getElementById('services-list');
    var vehicleSelect = document.getElementById('vehicle_id');
    var submitBtn = document.getElementById('submit-btn');
    var form = document.getElementById('driver-request-form');
    var serviceTypeInput = document.getElementById('service_type');
    var typeExisting = document.getElementById('type_existing');
    var typeCustom = document.getElementById('type_custom');

    function setSubmitEnabled() {
        var type = serviceTypeInput.value;
        var valid = false;
        if (type === 'existing') {
            var checked = list.querySelector('input[name="service_id"]:checked');
            var price = parseFloat(document.getElementById('service_price').value);
            valid = !!checked && !isNaN(price) && price > 0;
        } else {
            var name = document.getElementById('custom_service_name').value.trim();
            var desc = document.getElementById('custom_service_description').value.trim();
            var price = parseFloat(document.getElementById('custom_service_price').value);
            valid = name && desc && !isNaN(price) && price > 0;
        }
        var fileInput = document.getElementById('quotation_invoice');
        var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
        submitBtn.disabled = !valid || !hasFile;
    }

    function toggleServiceType() {
        var isCustom = typeCustom.checked;
        serviceTypeInput.value = isCustom ? 'custom' : 'existing';
        existingWrap.classList.toggle('hidden', isCustom);
        customWrap.classList.toggle('hidden', !isCustom);
        if (isCustom) {
            list.querySelectorAll('input[name="service_id"]').forEach(function(c) { c.checked = false; c.removeAttribute('name'); });
        } else {
            list.querySelectorAll('input[data-service-id]').forEach(function(c) { c.setAttribute('name', 'service_id'); });
        }
        setSubmitEnabled();
    }

    typeExisting.addEventListener('change', toggleServiceType);
    typeCustom.addEventListener('change', toggleServiceType);

    function updateServices() {
        var vid = vehicleSelect.value;
        list.innerHTML = '';
        if (!vid) {
            if (submitBtn) submitBtn.disabled = true;
            return;
        }
        var services = vehicleServices[vid];
        if (!services || services.length === 0) {
            list.innerHTML = '<p class="text-amber-700 p-3">لا توجد خدمات مسجّلة. اختر "خدمة أخرى" أو تواصل مع شركتك.</p>';
            if (submitBtn) submitBtn.disabled = true;
            return;
        }
        var oldId = (form.querySelector('input[name="service_id"]:checked') || {}).value;
        services.forEach(function (s) {
            var label = document.createElement('label');
            label.className = 'flex items-center gap-3 p-2 rounded-xl hover:bg-white cursor-pointer';
            var radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'service_id';
            radio.value = s.id;
            radio.setAttribute('data-service-id', s.id);
            if (oldId === String(s.id)) radio.checked = true;
            radio.addEventListener('change', setSubmitEnabled);
            label.appendChild(radio);
            label.appendChild(document.createTextNode(s.name));
            list.appendChild(label);
        });
        setSubmitEnabled();
    }

    vehicleSelect.addEventListener('change', updateServices);
    document.getElementById('service_price').addEventListener('input', setSubmitEnabled);
    var quotationInput = document.getElementById('quotation_invoice');
    if (quotationInput) {
        quotationInput.addEventListener('change', function() {
            setSubmitEnabled();
            var preview = document.getElementById('quotation-preview');
            var previewContent = document.getElementById('quotation-preview-content');
            var filenameEl = document.getElementById('quotation-filename');
            if (this.files && this.files[0]) {
                preview.classList.remove('hidden');
                var file = this.files[0];
                filenameEl.textContent = file.name;
                var ext = (file.name.split('.').pop() || '').toLowerCase();
                if (['jpg','jpeg','png'].indexOf(ext) >= 0) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewContent.innerHTML = '<img src="' + e.target.result + '" class="w-full max-h-48 object-contain" alt="Preview">';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContent.innerHTML = '<div class="p-4 text-center"><i class="fa-solid fa-file-pdf text-4xl text-rose-600"></i><p class="text-sm mt-2">PDF</p></div>';
                }
            } else {
                preview.classList.add('hidden');
            }
        });
    }
    document.getElementById('custom_service_name').addEventListener('input', setSubmitEnabled);
    document.getElementById('custom_service_description').addEventListener('input', setSubmitEnabled);
    document.getElementById('custom_service_price').addEventListener('input', setSubmitEnabled);

    if (vehicleSelect.value) updateServices();
    setSubmitEnabled();
})();
</script>
@endpush
@endsection
