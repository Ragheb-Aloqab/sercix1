@extends('admin.layouts.app')

@section('title', __('fleet.create_request') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('fleet.create_request'))
@section('subtitle', __('maintenance.maintenance_requests_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('fleet.create_request')])

@if ($errors->any())
    <div class="mb-6 p-4 rounded-2xl border border-rose-500/40 bg-rose-500/10 text-rose-400">
        <ul class="list-disc ms-5 space-y-1">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('company.maintenance-requests.store') }}" enctype="multipart/form-data" class="dash-card space-y-6">
    @csrf

    <div>
        <label class="block text-sm font-bold text-servx-silver-light mb-2">{{ __('fleet.select_vehicle') }} *</label>
        <select name="vehicle_id" required class="w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 min-h-[44px] text-servx-silver-light outline-none focus:ring-4 focus:ring-sky-500/20">
            <option value="">— {{ __('fleet.select_vehicle') }} —</option>
            @foreach($vehicles as $v)
                <option value="{{ $v->id }}" @selected(($selectedVehicleId ?? null) == $v->id)>
                    {{ $v->plate_number }} — {{ $v->display_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-bold text-servx-silver-light mb-2">{{ __('driver.maintenance_type') }} *</label>
        <select name="maintenance_type" required class="w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 min-h-[44px] text-servx-silver-light outline-none focus:ring-4 focus:ring-sky-500/20">
            @foreach($maintenanceTypes as $type)
                <option value="{{ $type->value }}" @selected(old('maintenance_type') == $type->value)>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-bold text-servx-silver-light mb-2">{{ __('fleet.describe_issue') }} *</label>
        <textarea name="description" rows="4" required maxlength="500" placeholder="{{ __('driver.description_placeholder') ?? 'Describe the issue...' }}" class="w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-sky-500/20">{{ old('description') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-bold text-servx-silver-light mb-2">{{ __('fleet.priority') }}</label>
        <select name="priority" class="w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 min-h-[44px] text-servx-silver-light outline-none focus:ring-4 focus:ring-sky-500/20">
            <option value="low" @selected(old('priority') == 'low')>{{ __('fleet.priority_low') }}</option>
            <option value="medium" @selected(old('priority', 'medium') == 'medium')>{{ __('fleet.priority_medium') }}</option>
            <option value="high" @selected(old('priority') == 'high')>{{ __('fleet.priority_high') }}</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-bold text-servx-silver-light mb-2">{{ __('fleet.upload_images') }} ({{ __('common.optional') }})</label>
        <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png" class="w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-sky-500/20 file:me-2 file:rounded-xl file:border-0 file:bg-sky-500/20 file:px-4 file:py-2 file:text-sky-400 file:font-semibold">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold text-servx-silver-light mb-2">{{ __('driver.city') }} ({{ __('common.optional') }})</label>
            <input type="text" name="city" value="{{ old('city') }}" class="w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-sky-500/20">
        </div>
        <div>
            <label class="block text-sm font-bold text-servx-silver-light mb-2">{{ __('driver.address') }} ({{ __('common.optional') }})</label>
            <input type="text" name="address" value="{{ old('address') }}" class="w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-sky-500/20">
        </div>
    </div>

    <div>
        <label class="block text-sm font-bold text-servx-silver-light mb-2">{{ __('driver.notes') }} ({{ __('common.optional') }})</label>
        <textarea name="notes" rows="2" class="w-full rounded-2xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-servx-silver-light outline-none focus:ring-4 focus:ring-sky-500/20">{{ old('notes') }}</textarea>
    </div>

    <div class="flex flex-wrap gap-3 pt-4">
        <button type="submit" class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
            <i class="fa-solid fa-plus me-2"></i>{{ __('fleet.create_request') }}
        </button>
        <a href="{{ route('company.maintenance-requests.index') }}" class="px-6 py-3 rounded-2xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">{{ __('common.cancel') }}</a>
    </div>
</form>

@include('company.partials.glass-end')
@endsection
