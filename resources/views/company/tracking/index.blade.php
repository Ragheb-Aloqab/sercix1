@extends('admin.layouts.app')

@section('title', __('tracking.tracking_page') . ' | Servx Motors')
@section('page_title', __('tracking.tracking_page'))
@section('subtitle', __('company.tracking_placeholder_desc'))

@section('content')
    @include('company.partials.glass-start', ['title' => __('tracking.tracking_page')])

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <a href="{{ route('company.vehicles.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
            {{ __('vehicles.vehicles_list') }}
        </a>
        <div class="flex items-center gap-4 text-sm">
            <span class="inline-flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                {{ __('tracking.status_moving') }}
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                {{ __('tracking.status_stopped') }}
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                {{ __('tracking.status_idle') }}
            </span>
            <span class="inline-flex items-center gap-1 text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ __('tracking.live_updates') }}
            </span>
        </div>
    </div>

    @php
        $apiConfigured = !empty($company->tracking_base_url) && !empty($company->tracking_api_key);
        $hasDeviceApiVehicles = $company->vehicles()->where('tracking_source', 'device_api')->whereNotNull('imei')->where('imei', '!=', '')->exists();
    @endphp
    @if (!$apiConfigured && $hasDeviceApiVehicles)
        <div class="mb-6 p-4 rounded-2xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
            {{ __('tracking.api_not_configured') }}
            <a href="{{ route('company.settings') }}" class="underline ms-2">{{ __('dashboard.settings') }}</a>
        </div>
    @endif
    <livewire:company.vehicle-tracking-map
        map-height="550px"
        :show-info-panel="false"
    />

    @include('company.partials.glass-end')
@endsection
