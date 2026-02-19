@extends('admin.layouts.app')

@section('title', __('reports.reports') . ' | ' . ($siteName ?? 'SERV.X'))
@section('page_title', __('reports.reports'))
@section('subtitle', __('reports.reports_subtitle'))

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Fuel report --}}
            <a href="{{ route('company.fuel.index') }}"
                class="group block rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6 hover:border-amber-300 dark:hover:border-amber-700 transition-colors">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-gas-pump text-2xl text-amber-600 dark:text-amber-400"></i>
                </div>
                <h3 class="font-black text-lg mb-1">{{ __('reports.fuel_report') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('reports.fuel_report_desc') }}</p>
            </a>

            {{-- Service report --}}
            <a href="{{ route('company.reports.service') }}"
                class="group block rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6 hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-screwdriver-wrench text-2xl text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <h3 class="font-black text-lg mb-1">{{ __('reports.service_report') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('reports.service_report_desc') }}</p>
            </a>

            {{-- Other (per vehicle / combined) --}}
            <a href="{{ route('company.vehicles.index') }}"
                class="group block rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6 hover:border-sky-300 dark:hover:border-sky-700 transition-colors">
                <div class="w-14 h-14 rounded-2xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-car text-2xl text-sky-600 dark:text-sky-400"></i>
                </div>
                <h3 class="font-black text-lg mb-1">{{ __('reports.other_per_vehicle') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('reports.other_per_vehicle_desc') }}</p>
            </a>
        </div>
    </div>
@endsection
