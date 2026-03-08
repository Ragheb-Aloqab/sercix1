@extends('admin.layouts.app')

@section('title', __('fleet.reports') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('fleet.reports'))
@section('subtitle', __('fleet.reports_desc'))

@section('content')
<x-company.glass :title="__('fleet.reports')">

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
    <a href="{{ route('company.reports.service', ['from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
        class="group block rounded-2xl bg-white border border-slate-200 p-6 hover:border-slate-300 hover:scale-[1.02] transition-all duration-300 shadow-sm">
        <div class="w-14 h-14 rounded-2xl bg-emerald-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-screwdriver-wrench text-2xl text-emerald-600 dark:text-emerald-400"></i>
        </div>
        <h3 class="font-black text-lg mb-1 text-slate-900 dark:text-white">{{ __('fleet.maintenance_cost_report') }}</h3>
        <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('reports.service_report_desc') }}</p>
        <p class="text-xs text-slate-500 mt-2">{{ __('fleet.export_pdf') }} · {{ __('fleet.export_excel') }}</p>
    </a>

    <a href="{{ route('company.fuel.index', ['from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
        class="group block rounded-2xl bg-white border border-slate-200 p-6 hover:border-slate-300 hover:scale-[1.02] transition-all duration-300 shadow-sm">
        <div class="w-14 h-14 rounded-2xl bg-amber-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-gas-pump text-2xl text-amber-600 dark:text-amber-400"></i>
        </div>
        <h3 class="font-black text-lg mb-1 text-slate-900 dark:text-white">{{ __('fleet.fuel_consumption_report') }}</h3>
        <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('reports.fuel_report_desc') }}</p>
        <p class="text-xs text-slate-500 mt-2">{{ __('fleet.export_pdf') }} · {{ __('fleet.export_excel') }}</p>
    </a>

    <a href="{{ route('company.reports.mileage', ['from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
        class="group block rounded-2xl bg-white border border-slate-200 p-6 hover:border-slate-300 hover:scale-[1.02] transition-all duration-300 shadow-sm">
        <div class="w-14 h-14 rounded-2xl bg-sky-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-gauge-high text-2xl text-sky-600 dark:text-sky-400"></i>
        </div>
        <h3 class="font-black text-lg mb-1 text-slate-900 dark:text-white">{{ __('vehicles.vehicle_mileage_reports') }}</h3>
        <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('vehicles.vehicle_mileage_reports_desc') }}</p>
        <p class="text-xs text-slate-500 mt-2">{{ __('fleet.export_pdf') }} · {{ __('fleet.export_excel') }}</p>
    </a>

    <a href="{{ route('company.reports.comprehensive') }}"
        class="group block rounded-2xl bg-white border border-slate-200 p-6 hover:border-slate-300 hover:scale-[1.02] transition-all duration-300 shadow-sm">
        <div class="w-14 h-14 rounded-2xl bg-violet-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-chart-line text-2xl text-violet-600 dark:text-violet-400"></i>
        </div>
        <h3 class="font-black text-lg mb-1 text-slate-900 dark:text-white">{{ __('reports.comprehensive_report') }}</h3>
        <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('reports.comprehensive_report_desc') }}</p>
        <p class="text-xs text-slate-500 mt-2">{{ __('fleet.export_pdf') }} · {{ __('fleet.export_excel') }}</p>
    </a>

    <a href="{{ route('company.reports.tax', ['from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
        class="group block rounded-2xl bg-white border border-slate-200 p-6 hover:border-slate-300 hover:scale-[1.02] transition-all duration-300 shadow-sm">
        <div class="w-14 h-14 rounded-2xl bg-rose-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-receipt text-2xl text-rose-600 dark:text-rose-400"></i>
        </div>
        <h3 class="font-black text-lg mb-1 text-slate-900 dark:text-white">{{ __('reports.tax_reports') }}</h3>
        <p class="text-sm text-slate-600 dark:text-slate-500">{{ __('reports.tax_reports_desc') }}</p>
        <p class="text-xs text-slate-500 dark:text-servx-silver mt-2">{{ __('fleet.export_pdf') }} · {{ __('fleet.export_excel') }}</p>
    </a>
</div>
</x-company.glass>
@endsection
