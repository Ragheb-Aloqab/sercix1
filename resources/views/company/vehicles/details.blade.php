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

{{-- Vehicle Information --}}
<div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 sm:p-8 backdrop-blur-sm mb-6">
    <h3 class="text-base font-bold text-slate-300 mb-4">{{ __('vehicles.vehicle_information') }}</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.plate_number') }}</span>
            <span class="font-bold text-white">{{ $vehicle->plate_number ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-slate-600/50">
            <span class="text-slate-400">{{ __('vehicles.original_vehicle_number') }}</span>
            <span class="font-bold text-white">{{ $vehicle->original_vehicle_number ?? '—' }}</span>
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

{{-- Insurance & Registration (Istimara) Cards --}}
@php
    $expiryWarningDays = 30;
    $insuranceExpiry = $vehicle->insurance_expiry_date;
    $registrationExpiry = $vehicle->registration_expiry_date;
    $expiryStatus = function ($date) use ($expiryWarningDays) {
        if (!$date) return ['status' => 'missing', 'class' => 'text-slate-400', 'bg' => 'bg-slate-500/20', 'border' => 'border-slate-500/50'];
        if ($date->isPast()) return ['status' => 'expired', 'class' => 'text-red-400', 'bg' => 'bg-red-500/20', 'border' => 'border-red-400/50'];
        if ($date->diffInDays(now()) <= $expiryWarningDays) return ['status' => 'expiring_soon', 'class' => 'text-amber-400', 'bg' => 'bg-amber-500/20', 'border' => 'border-amber-400/50'];
        return ['status' => 'valid', 'class' => 'text-emerald-400', 'bg' => 'bg-emerald-500/20', 'border' => 'border-emerald-400/50'];
    };
    $insuranceStatus = $expiryStatus($insuranceExpiry);
    $registrationStatus = $expiryStatus($registrationExpiry);
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    {{-- Vehicle Insurance --}}
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm">
        <div class="flex items-center gap-2 mb-3">
            <i class="fa-solid fa-shield-halved text-sky-400"></i>
            <h3 class="text-base font-bold text-white">{{ __('vehicles.insurance') }}</h3>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-slate-400 text-sm">{{ __('vehicles.insurance_status') }}</span>
                <span class="text-sm font-semibold {{ $vehicle->insurance_document_path ? 'text-emerald-400' : 'text-slate-500' }}">
                    {{ $vehicle->insurance_document_path ? __('vehicles.document_uploaded') : __('vehicles.missing') }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400 text-sm">{{ __('vehicles.expiry_date') }}</span>
                <span class="text-sm font-bold {{ $insuranceStatus['class'] }}">
                    @if($insuranceExpiry)
                        {{ $insuranceExpiry->translatedFormat('d M Y') }}
                        @if($insuranceStatus['status'] === 'expired')
                            <span class="text-xs">({{ __('vehicles.expired') }})</span>
                        @elseif($insuranceStatus['status'] === 'expiring_soon')
                            <span class="text-xs">({{ __('vehicles.expiring_soon') }})</span>
                        @endif
                    @else
                        —
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Vehicle Registration (Istimara) --}}
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm">
        <div class="flex items-center gap-2 mb-3">
            <i class="fa-solid fa-file-contract text-emerald-400"></i>
            <h3 class="text-base font-bold text-white">{{ __('vehicles.registration') }}</h3>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-slate-400 text-sm">{{ __('vehicles.registration_status') }}</span>
                <span class="text-sm font-semibold {{ $vehicle->registration_document_path ? 'text-emerald-400' : 'text-slate-500' }}">
                    {{ $vehicle->registration_document_path ? __('vehicles.document_uploaded') : __('vehicles.missing') }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400 text-sm">{{ __('vehicles.expiry_date') }}</span>
                <span class="text-sm font-bold {{ $registrationStatus['class'] }}">
                    @if($registrationExpiry)
                        {{ $registrationExpiry->translatedFormat('d M Y') }}
                        @if($registrationStatus['status'] === 'expired')
                            <span class="text-xs">({{ __('vehicles.expired') }})</span>
                        @elseif($registrationStatus['status'] === 'expiring_soon')
                            <span class="text-xs">({{ __('vehicles.expiring_soon') }})</span>
                        @endif
                    @else
                        —
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

@include('company.partials.glass-end')
@endsection
