@extends('admin.layouts.app')

@section('title', 'إضافة مركبة | Servx Motors')
@section('page_title', 'إضافة مركبة')
@section('subtitle', 'إضافة مركبة جديدة')

@section('content')
@include('company.partials.glass-start', ['title' => 'إضافة مركبة'])

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50">
                <p class="font-bold mb-2">يوجد أخطاء في الإدخال:</p>
                <ul class="list-disc ms-5 text-sm space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('company.vehicles.store') }}"
            class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm space-y-4">
            @csrf

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-black text-white">بيانات المركبة</h2>
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                    رجوع
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('tracking.vehicle_name') }} ({{ __('common.optional') }})</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="{{ __('tracking.vehicle_name') }}">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('vehicles.plate_number') }} *</label>
                    <input type="text" name="plate_number" value="{{ old('plate_number') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="ABC-1234" required>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('vehicles.original_vehicle_number') }} ({{ __('common.optional') }})</label>
                    <input type="text" name="original_vehicle_number" value="{{ old('original_vehicle_number') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="{{ __('vehicles.original_vehicle_number_placeholder') }}">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('tracking.tracking_source') }}</label>
                    <select name="tracking_source" id="tracking_source"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                        <option value="device_api" @selected(old('tracking_source', 'device_api') === 'device_api')>{{ __('tracking.source_device_api') }}</option>
                        <option value="mobile" @selected(old('tracking_source') === 'mobile')>{{ __('tracking.source_mobile') }}</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">{{ __('tracking.tracking_source_hint') }}</p>
                </div>

                <div id="imei-field">
                    <label class="text-sm font-bold text-slate-400">IMEI <span id="imei-required">*</span></label>
                    <input type="text" name="imei" value="{{ old('imei') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500 font-mono"
                        placeholder="123456789012345" maxlength="20"
                        pattern="[0-9]{10,20}" title="{{ __('tracking.imei_required') }}">
                    <p class="text-xs text-slate-500 mt-1">{{ __('tracking.imei_required') }}</p>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('vehicles.branch') }} ({{ __('common.optional') }})</label>
                    <select name="company_branch_id"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                        <option value="">— بدون —</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('company_branch_id') == $b->id)>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">الماركة</label>
                    <input type="text" name="brand" value="{{ old('brand') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="Toyota, Hyundai ...">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">الموديل</label>
                    <input type="text" name="model" value="{{ old('model') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="Camry, Elantra ...">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">السنة</label>
                    <input type="number" name="year" value="{{ old('year') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="2022">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">VIN (اختياري)</label>
                    <input type="text" name="vin" value="{{ old('vin') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="Vehicle Identification Number">
                </div>

                <div class="lg:col-span-2">
                    <label class="text-sm font-bold text-slate-400">ملاحظات</label>
                    <textarea name="notes" rows="3"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="أي تفاصيل إضافية...">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">اسم السائق (اختياري)</label>
                    <input type="text" name="driver_name" value="{{ old('driver_name') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="اسم السائق للتواصل">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-400">جوال السائق (اختياري)</label>
                    <input type="text" name="driver_phone" value="{{ old('driver_phone') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="05xxxxxxxx — للتسجيل وطلبات الخدمة">
                </div>
                <div class="lg:col-span-2 flex items-center gap-2">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded accent-sky-500"
                        @checked(old('is_active', 1))>
                    <label for="is_active" class="text-sm font-bold text-slate-300">نشط</label>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-4">
                <button class="px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-black transition-colors">
                    حفظ
                </button>
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-5 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-black hover:border-slate-400/50 transition-colors">
                    إلغاء
                </a>
            </div>
        </form>

<script>
document.getElementById('tracking_source')?.addEventListener('change', function() {
    var isDeviceApi = this.value === 'device_api';
    var inp = document.querySelector('input[name="imei"]');
    if (inp) inp.required = isDeviceApi;
    document.getElementById('imei-required').style.display = isDeviceApi ? '' : 'none';
});
document.getElementById('tracking_source')?.dispatchEvent(new Event('change'));
</script>
@include('company.partials.glass-end')
@endsection
