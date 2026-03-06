@extends('admin.layouts.app')

@section('title', __('reports.comprehensive_report') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('reports.comprehensive_report'))
@section('subtitle', __('reports.comprehensive_report_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('reports.comprehensive_report')])

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <a href="{{ route('company.reports.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('reports.back_to_reports') }}
        </a>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('company.reports.comprehensive.pdf', request()->query()) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-red-500/50 bg-red-900/20 text-red-400 font-bold hover:border-red-400/50 transition-colors">
                <i class="fa-solid fa-file-pdf"></i> {{ __('reports.export_pdf') }}
            </a>
            <a href="{{ route('company.reports.comprehensive.excel', request()->query()) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-emerald-500/50 bg-emerald-900/20 text-emerald-400 font-bold hover:border-emerald-400/50 transition-colors">
                <i class="fa-solid fa-file-excel"></i> {{ __('reports.export_excel') }}
            </a>
        </div>
    </div>

    {{-- Filter form (scalable for future month/vehicle selection) --}}
    <form method="GET" action="{{ route('company.reports.comprehensive') }}" class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 sm:p-5 backdrop-blur-sm mb-6">
        <h3 class="text-sm font-bold text-slate-400 mb-3">{{ __('reports.filters') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-sm font-bold text-slate-400 mb-1">{{ __('reports.month') }}</label>
                <select name="month" class="w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-2">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected(($data['month'] ?? now()->month) == $m)>{{ \Carbon\Carbon::createFromDate($data['year'] ?? now()->year, $m, 1)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-400 mb-1">{{ __('reports.year') }}</label>
                <select name="year" class="w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-2">
                    @foreach(range(now()->year, now()->year - 5) as $y)
                        <option value="{{ $y }}" @selected(($data['year'] ?? now()->year) == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-400 mb-1">{{ __('company.vehicle') }}</label>
                <select name="vehicle_id" class="w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-2">
                    <option value="">{{ __('company.all_vehicles') }}</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(($data['vehicle_id'] ?? null) == $v->id)>{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
                    <i class="fa-solid fa-filter me-2"></i>{{ __('company.apply_filter') }}
                </button>
            </div>
        </div>
    </form>

    {{-- Period label --}}
    <p class="text-servx-silver text-sm mb-4">{{ __('reports.period') }}: <strong class="text-white">{{ $data['period_label'] ?? now()->translatedFormat('F Y') }}</strong></p>

    {{-- Four statistic cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="rounded-2xl bg-emerald-500/20 border border-emerald-400/50 p-4 sm:p-5 backdrop-blur-sm">
            <div class="flex items-center gap-3 mb-2">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-500/30">
                    <i class="fa-solid fa-screwdriver-wrench text-emerald-400"></i>
                </span>
                <p class="text-emerald-300 text-sm font-bold">{{ __('reports.total_maintenance_cost') }}</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-emerald-300">{{ number_format($data['total_maintenance_cost'] ?? 0, 2) }} {{ __('company.sar') }}</p>
        </div>

        <div class="rounded-2xl bg-amber-500/20 border border-amber-400/50 p-4 sm:p-5 backdrop-blur-sm">
            <div class="flex items-center gap-3 mb-2">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-amber-500/30">
                    <i class="fa-solid fa-gas-pump text-amber-400"></i>
                </span>
                <p class="text-amber-300 text-sm font-bold">{{ __('reports.total_fuel_cost') }}</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-amber-300">{{ number_format($data['total_fuel_cost'] ?? 0, 2) }} {{ __('company.sar') }}</p>
        </div>

        <div class="rounded-2xl bg-sky-500/20 border border-sky-400/50 p-4 sm:p-5 backdrop-blur-sm">
            <div class="flex items-center gap-3 mb-2">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-sky-500/30">
                    <i class="fa-solid fa-gauge-high text-sky-400"></i>
                </span>
                <p class="text-sky-300 text-sm font-bold">{{ __('reports.monthly_mileage') }}</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-sky-300">{{ number_format($data['monthly_mileage'] ?? 0, 2) }} {{ __('common.km') }}</p>
        </div>

        <div class="rounded-2xl bg-violet-500/20 border border-violet-400/50 p-4 sm:p-5 backdrop-blur-sm">
            <div class="flex items-center gap-3 mb-2">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center bg-violet-500/30">
                    <i class="fa-solid fa-road text-violet-400"></i>
                </span>
                <p class="text-violet-300 text-sm font-bold">{{ __('reports.total_accumulated_mileage') }}</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-violet-300">{{ number_format($data['total_accumulated_mileage'] ?? 0, 2) }} {{ __('common.km') }}</p>
        </div>
    </div>

@include('company.partials.glass-end')
@endsection
