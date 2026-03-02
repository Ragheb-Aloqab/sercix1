@extends('admin.layouts.app')

@section('title', __('fleet.reports') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('fleet.reports'))
@section('subtitle', __('fleet.reports_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('fleet.reports')])

{{-- Date range filter --}}
<div class="dash-card mb-6">
    <form method="GET" action="{{ request()->url() }}" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('fleet.date_range') }}</label>
            <div class="flex gap-2 items-center">
                <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-white">
                <span class="text-servx-silver">—</span>
                <input type="date" name="to" value="{{ request('to', now()->format('Y-m-d')) }}" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-white">
            </div>
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold">{{ __('company.apply_filter') }}</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
    <a href="{{ route('company.reports.service', ['from' => request('from', now()->startOfMonth()->format('Y-m-d')), 'to' => request('to', now()->format('Y-m-d'))]) }}"
        class="group block rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm hover:border-slate-400/50 hover:scale-[1.02] transition-all duration-300">
        <div class="w-14 h-14 rounded-2xl bg-emerald-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-screwdriver-wrench text-2xl text-emerald-400"></i>
        </div>
        <h3 class="font-black text-lg mb-1 text-white">{{ __('fleet.maintenance_cost_report') }}</h3>
        <p class="text-sm text-slate-500">{{ __('reports.service_report_desc') }}</p>
        <p class="text-xs text-servx-silver mt-2">{{ __('fleet.export_pdf') }} · {{ __('fleet.export_excel') }}</p>
    </a>

    <a href="{{ route('company.fuel.index', ['from' => request('from', now()->startOfMonth()->format('Y-m-d')), 'to' => request('to', now()->format('Y-m-d'))]) }}"
        class="group block rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm hover:border-slate-400/50 hover:scale-[1.02] transition-all duration-300">
        <div class="w-14 h-14 rounded-2xl bg-amber-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-gas-pump text-2xl text-amber-400"></i>
        </div>
        <h3 class="font-black text-lg mb-1 text-white">{{ __('fleet.fuel_consumption_report') }}</h3>
        <p class="text-sm text-slate-500">{{ __('reports.fuel_report_desc') }}</p>
        <p class="text-xs text-servx-silver mt-2">{{ __('fleet.export_pdf') }} · {{ __('fleet.export_excel') }}</p>
    </a>

    <a href="{{ route('company.vehicles.index') }}"
        class="group block rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm hover:border-slate-400/50 hover:scale-[1.02] transition-all duration-300">
        <div class="w-14 h-14 rounded-2xl bg-sky-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-car text-2xl text-sky-400"></i>
        </div>
        <h3 class="font-black text-lg mb-1 text-white">{{ __('fleet.vehicle_usage_report') }}</h3>
        <p class="text-sm text-slate-500">{{ __('reports.other_per_vehicle_desc') }}</p>
        <p class="text-xs text-servx-silver mt-2">{{ __('fleet.export_pdf') }} · {{ __('fleet.export_excel') }}</p>
    </a>
</div>

@include('company.partials.glass-end')
@endsection
