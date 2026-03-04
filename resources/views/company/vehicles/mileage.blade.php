@extends('admin.layouts.app')

@section('title', __('vehicles.card_vehicle_mileage') . ' | ' . ($vehicle->plate_number ?? 'Servx Motors'))
@section('page_title', __('vehicles.card_vehicle_mileage'))
@section('subtitle', $vehicle->plate_number)

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.card_vehicle_mileage')])

<div class="mb-6">
    <a href="{{ route('company.vehicles.show', $vehicle) }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('vehicles.back_to_overview') }}
    </a>
</div>

<div class="space-y-6">
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
        <h3 class="text-base font-bold text-slate-300 mb-2">1️⃣ {{ __('vehicles.accumulated_mileage') }}</h3>
        <p class="text-3xl font-bold text-white">{{ number_format($accumulatedMileage ?? 0, 1) }} {{ __('common.km') }}</p>
        <p class="text-sm text-slate-500 mt-1">{{ __('vehicles.never_reset') }}</p>
    </div>

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
        <h3 class="text-base font-bold text-slate-300 mb-4">2️⃣ {{ __('vehicles.monthly_mileage') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl bg-slate-700/50 border border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 mb-1">{{ __('vehicles.monthly_mileage') }} ({{ now()->translatedFormat('M') }})</p>
                <p class="text-xl font-bold text-white">{{ number_format($currentMonthMileage ?? 0, 1) }} {{ __('common.km') }}</p>
            </div>
            <div class="rounded-xl bg-slate-700/50 border border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 mb-1">{{ __('vehicles.estimated_market_cost') }}</p>
                <p class="text-xl font-bold text-amber-400">{{ number_format($estimatedMarketCost ?? 0, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-xl bg-slate-700/50 border border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 mb-1">{{ __('vehicles.avg_market_operating_cost') }}</p>
                <p class="text-xl font-bold text-slate-300">{{ number_format($marketCostPerKm ?? 0.37, 2) }} {{ __('company.sar') }}/km</p>
            </div>
        </div>
        <h3 class="text-sm font-bold text-slate-400 mb-2">{{ __('vehicles.monthly_mileage') }} {{ __('vehicles.view_all') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-600/50 text-slate-400">
                        <th class="text-start py-2 font-bold">{{ __('vehicles.monthly_mileage') }}</th>
                        <th class="text-end py-2 font-bold">{{ __('common.km') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyMileageHistory ?? [] as $m)
                        <tr class="border-b border-slate-600/50">
                            <td class="py-2 text-white">{{ $m['month_label'] ?? '' }}</td>
                            <td class="py-2 text-end font-bold text-white">{{ number_format($m['total_km'] ?? 0, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('company.partials.glass-end')
@endsection
