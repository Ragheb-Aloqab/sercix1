@extends('admin.layouts.app')

@section('title', __('reports.reports') . ' | ' . ($siteName ?? 'SERV.X'))
@section('page_title', __('reports.reports'))
@section('subtitle', __('reports.reports_subtitle'))

@section('content')
@include('company.partials.glass-start', ['title' => __('reports.reports')])
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <a href="{{ route('company.fuel.index') }}"
            class="group block rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm hover:border-slate-400/50 hover:scale-[1.02] transition-all duration-300">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-gas-pump text-2xl text-amber-400"></i>
            </div>
            <h3 class="font-black text-lg mb-1 text-white">{{ __('reports.fuel_report') }}</h3>
            <p class="text-sm text-slate-500">{{ __('reports.fuel_report_desc') }}</p>
        </a>

        <a href="{{ route('company.reports.service') }}"
            class="group block rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm hover:border-slate-400/50 hover:scale-[1.02] transition-all duration-300">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-screwdriver-wrench text-2xl text-emerald-400"></i>
            </div>
            <h3 class="font-black text-lg mb-1 text-white">{{ __('reports.service_report') }}</h3>
            <p class="text-sm text-slate-500">{{ __('reports.service_report_desc') }}</p>
        </a>

        <a href="{{ route('company.vehicles.index') }}"
            class="group block rounded-2xl bg-slate-800/40 border border-slate-500/30 p-6 backdrop-blur-sm hover:border-slate-400/50 hover:scale-[1.02] transition-all duration-300">
            <div class="w-14 h-14 rounded-2xl bg-sky-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-car text-2xl text-sky-400"></i>
            </div>
            <h3 class="font-black text-lg mb-1 text-white">{{ __('reports.other_per_vehicle') }}</h3>
            <p class="text-sm text-slate-500">{{ __('reports.other_per_vehicle_desc') }}</p>
        </a>
    </div>
@include('company.partials.glass-end')
@endsection
