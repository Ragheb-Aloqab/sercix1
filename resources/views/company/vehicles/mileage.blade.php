@extends('admin.layouts.app')

@section('title', __('vehicles.card_vehicle_mileage') . ' | ' . ($vehicle->plate_number ?? 'Servx Motors'))
@section('page_title', __('vehicles.card_vehicle_mileage'))
@section('subtitle', $vehicle->plate_number)

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.card_vehicle_mileage')])

<div class="mb-6">
    <a href="{{ route('company.vehicles.show', $vehicle) }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-700 dark:text-white font-bold hover:border-slate-400 dark:hover:border-slate-400/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-all duration-300">
        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('vehicles.back_to_overview') }}
    </a>
</div>

<div class="space-y-6">
    {{-- Current / Previous / Total Distance (unified for GPS and Manual) --}}
    <div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-6 backdrop-blur-sm shadow-sm dark:shadow-none transition-colors duration-300">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-300 mb-4">{{ __('vehicles.current_previous_total') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 dark:text-slate-500 mb-1">{{ __('vehicles.current_mileage') }}</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($mileageSummary['current_mileage'] ?? 0, 1) }} {{ __('common.km') }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 dark:text-slate-500 mb-1">{{ __('vehicles.previous_mileage') }}</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $mileageSummary['previous_mileage'] !== null ? number_format($mileageSummary['previous_mileage'], 1) . ' ' . __('common.km') : '—' }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 dark:text-slate-500 mb-1">{{ __('vehicles.total_distance') }}</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($mileageSummary['total_distance'] ?? 0, 1) }} {{ __('common.km') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-6 backdrop-blur-sm shadow-sm dark:shadow-none transition-colors duration-300">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-300 mb-2">1️⃣ {{ __('vehicles.accumulated_mileage') }}</h3>
        <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($accumulatedMileage ?? 0, 1) }} {{ __('common.km') }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">{{ __('vehicles.never_reset') }}</p>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-6 backdrop-blur-sm shadow-sm dark:shadow-none transition-colors duration-300">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-300 mb-4">2️⃣ {{ __('vehicles.monthly_mileage') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 dark:text-slate-500 mb-1">{{ __('vehicles.monthly_mileage') }} ({{ now()->translatedFormat('M') }})</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($currentMonthMileage ?? 0, 1) }} {{ __('common.km') }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 dark:text-slate-500 mb-1">{{ __('vehicles.estimated_market_cost') }}</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($estimatedMarketCost ?? 0, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600/50 p-4">
                <p class="text-xs text-slate-500 dark:text-slate-500 mb-1">{{ __('vehicles.avg_market_operating_cost') }}</p>
                <p class="text-xl font-bold text-slate-600 dark:text-slate-300">{{ number_format($marketCostPerKm ?? 0.37, 2) }} {{ __('company.sar') }}/km</p>
            </div>
        </div>
        <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 mb-2">{{ __('vehicles.monthly_mileage') }} {{ __('vehicles.view_all') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-600/50 text-slate-600 dark:text-slate-400">
                        <th class="text-start py-2 font-bold">{{ __('vehicles.monthly_mileage') }}</th>
                        <th class="text-end py-2 font-bold">{{ __('common.km') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyMileageHistory ?? [] as $m)
                        <tr class="border-b border-slate-200 dark:border-slate-600/50">
                            <td class="py-2 text-slate-900 dark:text-white">{{ $m['month_label'] ?? '' }}</td>
                            <td class="py-2 text-end font-bold text-slate-900 dark:text-white">{{ number_format($m['total_km'] ?? 0, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mileage History (structured history table) --}}
    <div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-6 backdrop-blur-sm shadow-sm dark:shadow-none transition-colors duration-300">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-300 mb-4">3️⃣ {{ __('vehicles.mileage_history') }}</h3>
        <p class="text-sm text-slate-500 dark:text-slate-500 mb-4">{{ __('vehicles.mileage_history_hint') }}</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-600/50 text-slate-600 dark:text-slate-400">
                        <th class="text-start py-2 font-bold">{{ __('vehicles.recorded_date') }}</th>
                        <th class="text-start py-2 font-bold">{{ __('vehicles.tracking_type') }}</th>
                        <th class="text-end py-2 font-bold">{{ __('vehicles.previous_reading') }}</th>
                        <th class="text-end py-2 font-bold">{{ __('vehicles.current_reading') }}</th>
                        <th class="text-end py-2 font-bold">{{ __('vehicles.calculated_difference') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mileageHistory ?? [] as $h)
                        <tr class="border-b border-slate-200 dark:border-slate-600/50">
                            <td class="py-2 text-slate-900 dark:text-white">{{ $h->recorded_date?->translatedFormat('Y-m-d') ?? $h->recorded_date }}</td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 rounded text-xs {{ $h->tracking_type === 'gps' ? 'bg-sky-500/20 text-sky-600 dark:text-sky-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' }}">
                                    {{ $h->tracking_type === 'gps' ? __('tracking.source_device_api') : __('tracking.source_mobile') }}
                                </span>
                            </td>
                            <td class="py-2 text-end text-slate-600 dark:text-slate-300">{{ $h->previous_reading !== null ? number_format($h->previous_reading, 1) : '—' }}</td>
                            <td class="py-2 text-end text-slate-900 dark:text-white font-semibold">{{ number_format($h->current_reading, 1) }}</td>
                            <td class="py-2 text-end text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($h->calculated_difference, 1) }} {{ __('common.km') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500 dark:text-slate-500">{{ __('vehicles.no_mileage_history') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('company.partials.glass-end')
@endsection
