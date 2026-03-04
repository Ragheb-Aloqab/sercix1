@extends('admin.layouts.app')

@section('title', __('vehicles.card_vehicle_details') . ' | ' . ($vehicle->plate_number ?? 'Servx Motors'))
@section('page_title', __('vehicles.card_vehicle_details'))
@section('subtitle', $vehicle->plate_number)

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.card_vehicle_details')])

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('company.vehicles.show', $vehicle) }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('vehicles.back_to_overview') }}
    </a>
    <a href="{{ route('company.vehicles.edit', $vehicle) }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
        <i class="fa-solid fa-pen"></i> {{ __('vehicles.edit_vehicle') }}
    </a>
</div>

<div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 sm:p-8 backdrop-blur-sm">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.plate_number') }}</span>
            <span class="font-bold text-white">{{ $vehicle->plate_number ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.vehicle') }}</span>
            <span class="font-bold text-white">{{ $vehicle->display_name }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.model') }}</span>
            <span class="font-bold text-white">{{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: '—' }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.year') }}</span>
            <span class="font-bold text-white">{{ $vehicle->year ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.company') }}</span>
            <span class="font-bold text-white">{{ $vehicle->company->company_name ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.assigned_driver') }}</span>
            <span class="font-bold text-white">{{ $vehicle->driver_name ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.status') }}</span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $vehicle->is_active ? 'border-emerald-400/50 text-emerald-300 bg-emerald-500/20' : 'border-slate-500/50 text-slate-400' }}">
                {{ $vehicle->is_active ? __('vehicles.active') : __('vehicles.inactive') }}
            </span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('tracking.tracking_source') }}</span>
            <span class="font-bold text-white">
                @if($vehicle->usesDeviceApiTracking())
                    <i class="fa-solid fa-satellite-dish text-sky-400 me-1"></i> {{ __('vehicles.gps_device') }}
                @else
                    <i class="fa-solid fa-mobile-screen text-emerald-400 me-1"></i> {{ __('vehicles.mobile_tracking') }}
                @endif
            </span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.created_date') }}</span>
            <span class="font-bold text-white">{{ $vehicle->created_at?->translatedFormat('d M Y، H:i') ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center py-3">
            <span class="text-slate-400">{{ __('vehicles.last_update_date') }}</span>
            <span class="font-bold text-white">{{ $vehicle->updated_at?->translatedFormat('d M Y، H:i') ?? '—' }}</span>
        </div>
    </div>
</div>

@include('company.partials.glass-end')
@endsection
