@extends('admin.layouts.app')

@section('title', __('vehicles.card_vehicle_tracking') . ' | ' . ($vehicle->plate_number ?? 'Servx Motors'))
@section('page_title', __('vehicles.card_vehicle_tracking'))
@section('subtitle', $vehicle->plate_number)

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.card_vehicle_tracking')])

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('company.vehicles.show', $vehicle) }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('vehicles.back_to_overview') }}
    </a>
    @if ($vehicle->imei || $vehicle->usesMobileTracking())
        <a href="{{ route('company.vehicles.track', $vehicle) }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors">
            <i class="fa-solid fa-map"></i> {{ __('tracking.track_vehicle') }}
        </a>
    @endif
</div>

<div class="space-y-6">
    @if($vehicle->usesDeviceApiTracking() && $vehicle->imei)
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
            <h3 class="text-base font-bold text-slate-300 mb-4">{{ __('vehicles.live_tracking_status') }}</h3>
            <p class="text-slate-400 mb-2">{{ __('vehicles.gps_device') }} — {{ __('tracking.odometer') }}:</p>
            <p class="text-3xl font-bold text-white mb-4">
                @if(isset($trackingOdometer) && $trackingOdometer > 0)
                    {{ number_format($trackingOdometer, 1) }} {{ __('common.km') }}
                @else
                    <span class="text-slate-500">—</span>
                @endif
            </p>
            <a href="{{ route('company.vehicles.track', $vehicle) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600/30 text-emerald-300 border border-emerald-400/50 hover:bg-emerald-600/50">
                <i class="fa-solid fa-map"></i> {{ __('tracking.track_vehicle') }}
            </a>
        </div>
    @elseif($vehicle->usesMobileTracking())
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
                <p class="text-sm text-slate-500 mb-1">{{ __('vehicles.accumulated_mileage') }}</p>
                <p class="text-2xl font-bold text-white">{{ number_format($accumulatedMileage ?? 0, 1) }} {{ __('common.km') }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
                <p class="text-sm text-slate-500 mb-1">{{ __('vehicles.monthly_mileage') }}</p>
                <p class="text-2xl font-bold text-white">{{ number_format($currentMonthMileage ?? 0, 1) }} {{ __('common.km') }}</p>
            </div>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
            <h3 class="text-base font-bold text-slate-300 mb-4">{{ __('vehicles.trip_history') }}</h3>
            @if($trackingTrips->count() > 0)
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($trackingTrips as $trip)
                        <div class="p-4 rounded-xl bg-slate-700/50 border border-slate-600/50 flex flex-wrap justify-between items-center gap-2">
                            <div>
                                <p class="text-sm text-white font-bold">{{ __('vehicles.trip_start') }}: {{ $trip->started_at?->translatedFormat('d M Y، H:i') }}</p>
                                <p class="text-sm text-slate-400">{{ __('vehicles.trip_end') }}: {{ $trip->ended_at?->translatedFormat('d M Y، H:i') ?? '—' }}</p>
                            </div>
                            <p class="text-lg font-bold text-emerald-400">{{ number_format((float)$trip->trip_distance_km, 1) }} {{ __('common.km') }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-500 text-sm py-8">{{ __('vehicles.no_orders') }}</p>
            @endif
        </div>
    @else
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
            <p class="text-slate-500">{{ __('vehicles.tracking_coming_soon') }}</p>
        </div>
    @endif
</div>

@include('company.partials.glass-end')
@endsection
