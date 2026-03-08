@extends('admin.layouts.app')

@section('title', __('vehicles.vehicle_details') . ' | ' . ($vehicle->plate_number ?? 'Servx Motors'))
@section('page_title', __('vehicles.vehicle_details'))
@section('subtitle', $vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')))

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.vehicle_overview')])

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('company.vehicles.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('vehicles.back_to_vehicles') }}
    </a>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('company.vehicles.edit', $vehicle) }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
            <i class="fa-solid fa-pen"></i> {{ __('vehicles.edit_vehicle') }}
        </a>
        <form method="POST" action="{{ route('company.vehicles.destroy', $vehicle) }}" class="inline"
            onsubmit="return confirm('{{ __('common.confirm_delete') }}');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-red-500/50 text-red-400 font-bold hover:bg-red-500/20 transition-colors">
                <i class="fa-solid fa-trash"></i> {{ __('common.delete') }}
            </button>
        </form>
        @if ($vehicle->imei || $vehicle->usesMobileTracking())
            <a href="{{ route('company.vehicles.track', $vehicle) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors">
                <i class="fa-solid fa-location-dot"></i> {{ __('tracking.track_vehicle') }}
            </a>
        @endif
    </div>
</div>

{{-- 5 Clickable Navigation Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
    <a href="{{ route('company.vehicles.details', $vehicle) }}"
        class="block rounded-2xl bg-white border border-slate-200 dark:border-slate-500/30 p-6 hover:border-sky-400/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <span class="w-14 h-14 rounded-xl flex items-center justify-center bg-sky-500/20 text-sky-600 dark:text-sky-400 group-hover:bg-sky-500/30 transition-colors">
                <i class="fa-solid fa-car text-2xl"></i>
            </span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-300">{{ __('vehicles.card_vehicle_details') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('vehicles.static_metadata') }}</p>
            </div>
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-600 dark:text-slate-500 group-hover:text-sky-600 dark:group-hover:text-sky-400 ms-auto transition-colors"></i>
        </div>
    </a>

    <a href="{{ route('company.vehicles.tracking', $vehicle) }}"
        class="block rounded-2xl bg-white border border-slate-200 dark:border-slate-500/30 p-6 hover:border-emerald-400/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <span class="w-14 h-14 rounded-xl flex items-center justify-center bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-500/30 transition-colors">
                <i class="fa-solid fa-location-dot text-2xl"></i>
            </span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-300">{{ __('vehicles.card_vehicle_tracking') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('vehicles.trip_history_mileage') }}</p>
            </div>
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-600 dark:text-slate-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 ms-auto transition-colors"></i>
        </div>
    </a>

    <a href="{{ route('company.vehicles.images', $vehicle) }}"
        class="block rounded-2xl bg-white border border-slate-200 dark:border-slate-500/30 p-6 hover:border-amber-400/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <span class="w-14 h-14 rounded-xl flex items-center justify-center bg-amber-500/20 text-amber-600 dark:text-amber-400 group-hover:bg-amber-500/30 transition-colors">
                <i class="fa-solid fa-images text-2xl"></i>
            </span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-300">{{ __('vehicles.card_vehicle_images') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('vehicles.images_archive') }}</p>
            </div>
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-600 dark:text-slate-500 group-hover:text-amber-600 dark:group-hover:text-amber-400 ms-auto transition-colors"></i>
        </div>
    </a>

    <a href="{{ route('company.vehicles.reports', $vehicle) }}"
        class="block rounded-2xl bg-white border border-slate-200 dark:border-slate-500/30 p-6 hover:border-sky-400/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <span class="w-14 h-14 rounded-xl flex items-center justify-center bg-sky-500/20 text-sky-600 dark:text-sky-400 group-hover:bg-sky-500/30 transition-colors">
                <i class="fa-solid fa-file-invoice text-2xl"></i>
            </span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-300">{{ __('vehicles.card_vehicle_reports') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('vehicles.fuel_maintenance_reports') }}</p>
            </div>
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-600 dark:text-slate-500 group-hover:text-sky-600 dark:group-hover:text-sky-400 ms-auto transition-colors"></i>
        </div>
    </a>

    <a href="{{ route('company.vehicles.mileage', $vehicle) }}"
        class="block rounded-2xl bg-white border border-slate-200 dark:border-slate-500/30 p-6 hover:border-emerald-400/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all duration-300 group sm:col-span-2 lg:col-span-1">
        <div class="flex items-center gap-4">
            <span class="w-14 h-14 rounded-xl flex items-center justify-center bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-500/30 transition-colors">
                <i class="fa-solid fa-gauge-high text-2xl"></i>
            </span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-300">{{ __('vehicles.card_vehicle_mileage') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('vehicles.accumulated_monthly_market') }}</p>
            </div>
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-slate-600 dark:text-slate-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 ms-auto transition-colors"></i>
        </div>
    </a>
</div>

@include('company.partials.glass-end')
@endsection
