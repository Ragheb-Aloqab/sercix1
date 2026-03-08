@extends('layouts.driver')

@section('title', __('fuel.register_refill'))

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <h1 class="dash-page-title mb-6">{{ __('fuel.register_refill') }}</h1>
    <p class="text-slate-600 dark:text-servx-silver mb-6">{{ __('fuel.register_refill_desc') }}</p>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl border border-rose-500/40 bg-rose-500/10 text-rose-400">
            <ul class="list-disc ms-5 space-y-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('driver.fuel-refill.store') }}" enctype="multipart/form-data" class="dash-card space-y-4">
        @csrf
        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.vehicle') }} *</label>
            <select name="vehicle_id" required class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 min-h-[44px] text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-amber-500/20 transition-colors duration-300">
                <option value="">— {{ __('fuel.vehicle') }} —</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected(old('vehicle_id') == $v->id)>{{ $v->plate_number }} — {{ $v->make ?? '' }} {{ $v->model ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.liters') }} — {{ __('common.optional') }}</label>
                <input type="number" name="liters" step="0.01" min="0" value="{{ old('liters') }}" placeholder="{{ __('driver.example_liters') }}" class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 min-h-[44px] text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-amber-500/20 transition-colors duration-300" />
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.cost') }} — {{ __('common.optional') }}</label>
                <input type="number" name="cost" step="0.01" min="0" value="{{ old('cost') }}" placeholder="{{ __('driver.example_cost') }}" class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 min-h-[44px] text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-amber-500/20 transition-colors duration-300" />
            </div>
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.refilled_at') }} *</label>
            <input type="datetime-local" name="refilled_at" value="{{ old('refilled_at', now()->format('Y-m-d\TH:i')) }}" required class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 min-h-[44px] text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-amber-500/20 transition-colors duration-300" />
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.odometer_km') }} — {{ __('common.optional') }}</label>
            <input type="number" name="odometer_km" min="0" value="{{ old('odometer_km') }}" placeholder="{{ __('fuel.odometer_hint') }}" class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 min-h-[44px] text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-amber-500/20 transition-colors duration-300" />
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.fuel_type') }}</label>
            <select name="fuel_type" class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 min-h-[44px] text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-amber-500/20 transition-colors duration-300">
                <option value="petrol" @selected(old('fuel_type', 'petrol') === 'petrol')>{{ __('fuel.petrol') }}</option>
                <option value="diesel" @selected(old('fuel_type') === 'diesel')>{{ __('fuel.diesel') }}</option>
                <option value="premium" @selected(old('fuel_type') === 'premium')>{{ __('fuel.premium') }}</option>
            </select>
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.notes') }} ({{ __('common.optional') }})</label>
            <textarea name="notes" rows="2" placeholder="{{ __('driver.notes_placeholder') }}" class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-amber-500/20 transition-colors duration-300">{{ old('notes') }}</textarea>
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.receipt_image') }} — {{ __('common.optional') }}</label>
            <p class="text-xs text-slate-600 dark:text-servx-silver mt-1 mb-2">{{ __('fuel.receipt_hint') }} {{ __('fuel.receipt_camera_or_gallery') }}</p>
            <div class="flex flex-wrap gap-3 mt-2">
                <label class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors file:me-2 file:rounded-xl file:border-0 file:bg-amber-500/20 file:px-4 file:py-2 file:font-bold file:text-amber-400 min-h-[44px]">
                    <i class="fa-solid fa-camera text-amber-500"></i>
                    <span class="text-slate-700 dark:text-servx-silver-light font-semibold">{{ __('fuel.take_photo') }}</span>
                    <input type="file" name="receipt" accept="image/*" capture="environment" class="sr-only" id="receipt-camera" />
                </label>
                <label class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors file:me-2 file:rounded-xl file:border-0 file:bg-amber-500/20 file:px-4 file:py-2 file:font-bold file:text-amber-400 min-h-[44px]">
                    <i class="fa-solid fa-images text-amber-500"></i>
                    <span class="text-slate-700 dark:text-servx-silver-light font-semibold">{{ __('fuel.choose_from_gallery') }}</span>
                    <input type="file" name="receipt" accept="image/*" class="sr-only" id="receipt-gallery" />
                </label>
            </div>
            <p class="text-slate-500 text-xs mt-2" id="receipt-file-name"></p>
            @error('receipt')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button type="submit" class="flex-1 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-extrabold py-3 min-h-[44px] active:scale-[0.99]">
                <i class="fa-solid fa-gas-pump me-2"></i>{{ __('fuel.submit_refill') }}
            </button>
            <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 min-h-[44px] flex items-center justify-center rounded-2xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 font-bold active:scale-[0.99] text-slate-700 dark:text-servx-silver-light transition-colors duration-300">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
