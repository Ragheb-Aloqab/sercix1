@extends('layouts.driver')

@section('title', __('driver.new_maintenance_request'))

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <h1 class="dash-page-title mb-6">{{ __('driver.new_maintenance_request') ?? 'طلب صيانة جديد' }}</h1>
    <p class="text-servx-silver mb-6">{{ __('driver.maintenance_request_help') ?? 'اختر المركبة ونوع الصيانة ووصف مختصر. سيتم إرسال الطلب للشركة لطلب عروض من مراكز الصيانة.' }}</p>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl border border-rose-500/40 bg-rose-500/10 text-rose-400">
            <ul class="list-disc ms-5 space-y-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('driver.maintenance-request.store') }}" enctype="multipart/form-data" class="dash-card space-y-4">
        @csrf
        <div>
            <label class="text-sm font-bold text-servx-silver-light">{{ __('driver.vehicle') ?? 'المركبة' }} *</label>
            <select name="vehicle_id" required class="mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 min-h-[44px] text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20">
                <option value="">— {{ __('driver.select_vehicle') ?? 'اختر المركبة' }} —</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected(($selectedVehicleId ?? null) == $v->id)>
                        {{ $v->plate_number }} — {{ $v->make ?? '' }} {{ $v->model ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-bold text-servx-silver-light">{{ __('driver.maintenance_type') ?? 'نوع الصيانة' }} *</label>
            <select name="maintenance_type" required class="mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 min-h-[44px] text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20">
                @foreach($maintenanceTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('maintenance_type') == $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-bold text-servx-silver-light">{{ __('driver.description') ?? 'الوصف المختصر' }} *</label>
            <textarea name="description" rows="3" required maxlength="500" placeholder="{{ __('driver.description_placeholder') ?? 'وصف مختصر للمشكلة أو المطلوب...' }}" class="mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="text-sm font-bold text-servx-silver-light">{{ __('driver.optional_images') ?? 'صور (اختياري)' }}</label>
            <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png" class="mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20 file:me-2 file:rounded-xl file:border-0 file:bg-emerald-500/20 file:px-4 file:py-2 file:text-emerald-400 file:font-semibold">
        </div>

        <div>
            <label class="text-sm font-bold text-servx-silver-light">{{ __('driver.city') ?? 'المدينة' }} ({{ __('common.optional') ?? 'اختياري' }})</label>
            <input type="text" name="city" value="{{ old('city') }}" placeholder="الرياض، جدة، ..." class="mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20">
        </div>
        <div>
            <label class="text-sm font-bold text-servx-silver-light">{{ __('driver.address') ?? 'العنوان' }} ({{ __('common.optional') ?? 'اختياري' }})</label>
            <textarea name="address" rows="2" placeholder="{{ __('driver.address_placeholder') ?? 'الحي، الشارع...' }}" class="mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20">{{ old('address') }}</textarea>
        </div>
        <div>
            <label class="text-sm font-bold text-servx-silver-light">{{ __('driver.notes') ?? 'ملاحظات' }} ({{ __('common.optional') ?? 'اختياري' }})</label>
            <textarea name="notes" rows="2" placeholder="{{ __('driver.notes_placeholder') ?? 'أي تفاصيل إضافية...' }}" class="mt-2 w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3">
                <i class="fa-solid fa-paper-plane me-2"></i>{{ __('driver.submit_request') ?? 'إرسال الطلب' }}
            </button>
            <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 rounded-2xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
