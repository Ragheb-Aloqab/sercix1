@extends('admin.layouts.app')

@section('title', __('vehicles.card_vehicle_reports') . ' | ' . ($vehicle->plate_number ?? 'Servx Motors'))
@section('page_title', __('vehicles.card_vehicle_reports'))
@section('subtitle', $vehicle->plate_number)

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.card_vehicle_reports')])

<div class="mb-6">
    <a href="{{ route('company.vehicles.show', $vehicle) }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('vehicles.back_to_overview') }}
    </a>
</div>

<div class="space-y-6">
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
        <h3 class="text-base font-bold text-slate-300 mb-4">{{ __('vehicles.filter_by_type') }}</h3>
        <form method="GET" action="{{ route('company.vehicles.reports', $vehicle) }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('vehicles.filter_by_type') }}</label>
                <select name="report_type" class="rounded-xl bg-slate-700/50 border border-slate-600/50 text-white px-3 py-2 text-sm">
                    <option value="all" {{ (request('report_type', 'all')) === 'all' ? 'selected' : '' }}>All</option>
                    <option value="fuel" {{ request('report_type') === 'fuel' ? 'selected' : '' }}>{{ __('company.fuel') }}</option>
                    <option value="maintenance" {{ request('report_type') === 'maintenance' ? 'selected' : '' }}>{{ __('company.maintenance') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('company.from_date') }}</label>
                <input type="date" name="report_from" value="{{ request('report_from') }}" class="rounded-xl bg-slate-700/50 border border-slate-600/50 text-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('company.to_date') }}</label>
                <input type="date" name="report_to" value="{{ request('report_to') }}" class="rounded-xl bg-slate-700/50 border border-slate-600/50 text-white px-3 py-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-600/50 hover:bg-slate-600 text-white text-sm font-bold">{{ __('company.apply_filter') }}</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
            <p class="text-sm text-slate-500 mb-1">{{ __('company.total_fuel_cost') }}</p>
            <p class="text-2xl font-bold text-white">{{ number_format($reportFuelTotal ?? 0, 2) }} {{ __('company.sar') }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
            <p class="text-sm text-slate-500 mb-1">{{ __('company.total_maintenance_cost') }}</p>
            <p class="text-2xl font-bold text-white">{{ number_format($reportMaintenanceTotal ?? 0, 2) }} {{ __('company.sar') }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
            <p class="text-sm text-slate-500 mb-1">{{ __('vehicles.combined_total') }}</p>
            <p class="text-2xl font-bold text-amber-400">{{ number_format(($reportFuelTotal ?? 0) + ($reportMaintenanceTotal ?? 0), 2) }} {{ __('company.sar') }}</p>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm">
        <h3 class="text-base font-bold text-slate-300 mb-4">{{ __('vehicles.detailed_transactions') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-600/50 text-slate-400">
                        <th class="text-start py-3 font-bold">{{ __('company.date') }}</th>
                        <th class="text-start py-3 font-bold">{{ __('vehicles.report_type') }}</th>
                        <th class="text-start py-3 font-bold">{{ __('vehicles.description') }}</th>
                        <th class="text-end py-3 font-bold">{{ __('company.cost') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportTransactions ?? [] as $tx)
                        <tr class="border-b border-slate-600/50">
                            <td class="py-3 text-white">{{ $tx->date?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                            <td class="py-3 text-slate-300">{{ $tx->type === 'fuel' ? __('company.fuel') : __('company.maintenance') }}</td>
                            <td class="py-3 text-slate-300">{{ $tx->description ?? '—' }}</td>
                            <td class="py-3 text-end font-bold text-white">{{ number_format($tx->cost ?? 0, 2) }} {{ __('company.sar') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap gap-2 mt-6">
            <a href="{{ route('company.vehicles.report.excel', $vehicle) }}?{{ $reportQs ?? '' }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600/30 text-emerald-300 border border-emerald-400/50 hover:bg-emerald-600/50 font-bold">
                <i class="fa-solid fa-file-excel"></i> {{ __('vehicles.download_excel') }}
            </a>
            <a href="{{ route('company.vehicles.report.pdf', $vehicle) }}?{{ $reportQs ?? '' }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600/30 text-red-300 border border-red-400/50 hover:bg-red-600/50 font-bold">
                <i class="fa-solid fa-file-pdf"></i> {{ __('vehicles.download_pdf') }}
            </a>
        </div>
    </div>
</div>

@include('company.partials.glass-end')
@endsection
