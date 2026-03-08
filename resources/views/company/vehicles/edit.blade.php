@extends('admin.layouts.app')

@section('title', 'تعديل مركبة | Servx Motors')
@section('page_title', 'تعديل مركبة')
@section('subtitle', 'تعديل بيانات المركبة')

@section('content')
@include('company.partials.glass-start', ['title' => __('common.edit_vehicle') . ' — ' . $vehicle->plate_number])

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

        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                {{ session('success') }}
            </div>
        @endif

        <form id="vehicle-edit-form" method="POST" action="{{ route('company.vehicles.update', $vehicle->id) }}"
            class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm space-y-4">
            @csrf
            @method('PATCH')

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-black text-white">{{ __('common.edit_vehicle') }}</h2>
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                    رجوع
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('tracking.vehicle_name') }} ({{ __('common.optional') }})</label>
                    <input type="text" name="name" value="{{ old('name', $vehicle->name) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white"
                        placeholder="{{ __('tracking.vehicle_name') }}">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('vehicles.plate_number') }} *</label>
                    <input type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white"
                        required>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('vehicles.original_vehicle_number') }} ({{ __('common.optional') }})</label>
                    <input type="text" name="original_vehicle_number" value="{{ old('original_vehicle_number', $vehicle->original_vehicle_number) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white"
                        placeholder="{{ __('vehicles.original_vehicle_number_placeholder') }}">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('tracking.tracking_source') }}</label>
                    <select name="tracking_source" id="tracking_source_edit"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                        <option value="device_api" @selected(old('tracking_source', $vehicle->tracking_source ?? 'device_api') === 'device_api')>{{ __('tracking.source_device_api') }}</option>
                        <option value="mobile" @selected(old('tracking_source', $vehicle->tracking_source ?? '') === 'mobile')>{{ __('tracking.source_mobile') }}</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">{{ __('tracking.tracking_source_hint') }}</p>
                </div>

                <div id="imei-field-edit">
                    <label class="text-sm font-bold text-slate-400">IMEI <span id="imei-required-edit">({{ __('common.optional') }})</span></label>
                    <input type="text" name="imei" value="{{ old('imei', $vehicle->imei) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-mono"
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
                            <option value="{{ $b->id }}" @selected(old('company_branch_id', $vehicle->company_branch_id) == $b->id)>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">الماركة</label>
                    <input type="text" name="brand" value="{{ old('brand', $vehicle->brand) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">الموديل</label>
                    <input type="text" name="model" value="{{ old('model', $vehicle->model) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">السنة</label>
                    <input type="number" name="year" value="{{ old('year', $vehicle->year) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">VIN (اختياري)</label>
                    <input type="text" name="vin" value="{{ old('vin', $vehicle->vin) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">اسم السائق (اختياري)</label>
                    <input type="text" name="driver_name" value="{{ old('driver_name', $vehicle->driver_name) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="اسم السائق">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-400">جوال السائق (اختياري)</label>
                    <input type="text" name="driver_phone" value="{{ old('driver_phone', $vehicle->driver_phone) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="05xxxxxxxx">
                </div>
                <div class="lg:col-span-2">
                    <label class="text-sm font-bold text-slate-400">ملاحظات</label>
                    <textarea name="notes" rows="3"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">{{ old('notes', $vehicle->notes) }}</textarea>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded accent-sky-500"
                        @checked(old('is_active', $vehicle->is_active))>
                    <label for="is_active" class="text-sm font-bold text-slate-300">نشط</label>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-4">
                <button type="submit" class="px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-black transition-colors">
                    حفظ التعديل
                </button>
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-5 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-black hover:border-slate-400/50 transition-colors">
                    إلغاء
                </a>
            </div>
        </form>
        <form method="POST" action="{{ route('company.vehicles.destroy', $vehicle) }}" class="inline mt-4"
            onsubmit="return confirm('{{ __('common.confirm_delete') }}');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="px-5 py-3 rounded-2xl border border-red-500/50 text-red-400 font-black hover:bg-red-500/20 transition-colors">
                <i class="fa-solid fa-trash me-1"></i> {{ __('common.delete') }}
            </button>
        </form>

        {{-- Vehicle Documents (separate forms - cannot nest forms in HTML) --}}
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm mt-6">
            <div id="documents">
                <h3 class="text-base font-bold text-slate-300 mb-4">{{ __('vehicles.vehicle_documents') }}</h3>
                <p class="text-xs text-slate-500 mb-4">{{ __('vehicles.max_size') }}</p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Registration --}}
                    <div class="rounded-xl bg-slate-700/30 border border-slate-500/30 p-4">
                        <h4 class="font-bold text-white mb-3">{{ __('vehicles.registration') }}</h4>
                        <form method="POST" action="{{ route('company.vehicles.documents.registration', $vehicle) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="text-sm text-slate-400">{{ __('vehicles.expiry_date') }} *</label>
                                <input type="date" name="expiry_date" value="{{ old('registration_expiry_date', $vehicle->registration_expiry_date?->format('Y-m-d')) }}" required
                                    class="mt-1 w-full px-4 py-2 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white">
                            </div>
                            <div>
                                <label class="text-sm text-slate-400">{{ $vehicle->registration_document_path ? __('vehicles.replace_document') : __('vehicles.upload_registration') }} *</label>
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required
                                    class="mt-1 w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-600 file:text-white file:font-bold">
                            </div>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm">
                                {{ $vehicle->registration_document_path ? __('vehicles.replace_document') : __('vehicles.upload_registration') }}
                            </button>
                        </form>
                    </div>

                    {{-- Insurance --}}
                    <div class="rounded-xl bg-slate-700/30 border border-slate-500/30 p-4">
                        <h4 class="font-bold text-white mb-3">{{ __('vehicles.insurance') }}</h4>
                        <form method="POST" action="{{ route('company.vehicles.documents.insurance', $vehicle) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="text-sm text-slate-400">{{ __('vehicles.expiry_date') }} *</label>
                                <input type="date" name="expiry_date" value="{{ old('insurance_expiry_date', $vehicle->insurance_expiry_date?->format('Y-m-d')) }}" required
                                    class="mt-1 w-full px-4 py-2 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white">
                            </div>
                            <div>
                                <label class="text-sm text-slate-400">{{ $vehicle->insurance_document_path ? __('vehicles.replace_document') : __('vehicles.upload_insurance') }} *</label>
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required
                                    class="mt-1 w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-600 file:text-white file:font-bold">
                            </div>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm">
                                {{ $vehicle->insurance_document_path ? __('vehicles.replace_document') : __('vehicles.upload_insurance') }}
                            </button>
                        </form>
                    </div>
                </div>

                @if ($vehicle->registration_document_path || $vehicle->insurance_document_path)
                <form method="POST" action="{{ route('company.vehicles.documents.expiry', $vehicle) }}" class="mt-4 p-4 rounded-xl bg-slate-700/20 border border-slate-500/20">
                    @csrf
                    @method('PATCH')
                    <p class="text-sm text-slate-400 mb-3">{{ __('vehicles.expiry_updated') }}</p>
                    <div class="flex flex-wrap gap-4 items-end">
                        @if ($vehicle->registration_document_path)
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-slate-400">{{ __('vehicles.registration') }}:</label>
                            <input type="date" name="registration_expiry_date" value="{{ $vehicle->registration_expiry_date?->format('Y-m-d') }}"
                                class="px-3 py-1.5 rounded-lg border border-slate-500/50 bg-slate-800/40 text-white text-sm">
                        </div>
                        @endif
                        @if ($vehicle->insurance_document_path)
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-slate-400">{{ __('vehicles.insurance') }}:</label>
                            <input type="date" name="insurance_expiry_date" value="{{ $vehicle->insurance_expiry_date?->format('Y-m-d') }}"
                                class="px-3 py-1.5 rounded-lg border border-slate-500/50 bg-slate-800/40 text-white text-sm">
                        </div>
                        @endif
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-slate-600 hover:bg-slate-500 text-white text-sm font-bold">{{ __('livewire.update') }}</button>
                    </div>
                </form>
                @endif
            </div>
        </div>

<script>
document.getElementById('tracking_source_edit')?.addEventListener('change', function() {
    var isDeviceApi = this.value === 'device_api';
    var inp = document.querySelector('#vehicle-edit-form input[name="imei"]');
    if (inp) inp.required = isDeviceApi;
    var span = document.getElementById('imei-required-edit');
    if (span) span.textContent = isDeviceApi ? '*' : '({{ __('common.optional') }})';
});
document.getElementById('tracking_source_edit')?.dispatchEvent(new Event('change'));
</script>
@include('company.partials.glass-end')
@endsection
