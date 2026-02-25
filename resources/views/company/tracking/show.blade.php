@extends('admin.layouts.app')

@section('title', __('tracking.tracking_page') . ' — ' . $vehicle->display_name . ' | Servx Motors')
@section('page_title', __('tracking.tracking_page'))
@section('subtitle', $vehicle->display_name . ' | ' . ($vehicle->plate_number ?? ''))

@section('content')
    @include('company.partials.glass-start', [
        'title' => __('tracking.tracking_page') . ' — ' . $vehicle->display_name,
    ])

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <a href="{{ route('company.vehicles.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
            {{ __('vehicles.back_to_vehicles') }}
        </a>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <span class="inline-flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ __('tracking.live_updates') }}
            </span>
        </div>
    </div>

    @if ($vehicle->usesDeviceApiTracking() && !empty($vehicle->imei) && (empty($company->tracking_base_url) || empty($company->tracking_api_key)))
        <div class="mb-6 p-4 rounded-2xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
            {{ __('tracking.api_not_configured') }}
            <a href="{{ route('company.settings') }}" class="underline ms-2">{{ __('dashboard.settings') }}</a>
        </div>
    @else
        <livewire:company.vehicle-tracking-map
            :vehicle-id="$vehicle->id"
            map-height="500px"
            :show-info-panel="true"
        />
    @endif

    @include('company.partials.glass-end')
@endsection
