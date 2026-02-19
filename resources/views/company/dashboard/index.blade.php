@extends('admin.layouts.app')

@section('title', __('company.dashboard_title') . ' | ' . ($siteName ?? 'SERV.X'))
@section('page_title', __('company.dashboard_title'))

@section('content')
    <div class="space-y-6">
        {{-- Welcome --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-6">
            <h1 class="text-2xl font-black">
                {{ __('company.welcome') }} {{ $company->company_name }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ __('company.welcome_desc') }}
            </p>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">{{ __('company.orders') }}</p>
                <p class="text-3xl font-black mt-2">{{ $company->orders->count() }}</p>
            </div>
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">{{ __('company.invoices') }}</p>
                <p class="text-3xl font-black mt-2">{{ $company->invoices->count() }}</p>
            </div>
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">{{ __('company.branches') }}</p>
                <p class="text-3xl font-black mt-2">{{ $company->branches->count() }}</p>
            </div>
        </div>

        {{-- Fleet overview cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-4 sm:p-6 border border-slate-200/70 dark:border-slate-800 border-s-4 border-s-sky-500 min-w-0 [dir="rtl"]:border-s-0 [dir="rtl"]:border-e-4 [dir="rtl"]:border-e-sky-500">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('company.vehicles_count') }}</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-slate-100 mt-2">{{ $company->vehicles()->count() }}</h3>
                        <p class="text-emerald-600 text-sm mt-2 flex items-center gap-1">
                            <span class="bg-emerald-100 dark:bg-emerald-900/30 rounded-full p-1">
                                <i class="fa-solid fa-arrow-up text-emerald-600 text-xs"></i>
                            </span>
                            {{ __('company.full_fleet') }}
                        </p>
                    </div>
                    <div class="bg-sky-100 dark:bg-sky-900/30 p-3 rounded-lg">
                        <i class="fa-solid fa-truck-fast text-sky-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-4 sm:p-6 border border-slate-200/70 dark:border-slate-800 border-s-4 border-s-orange-500 min-w-0 [dir="rtl"]:border-s-0 [dir="rtl"]:border-e-4 [dir="rtl"]:border-e-orange-500">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('company.total_maintenance_cost') }}</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100 mt-2">{{ number_format($company->maintenanceCost()) }} <span class="text-lg">{{ __('company.sar') }}</span></h3>
                        @if($company->vehicles()->count() > 0)
                            <p class="text-slate-600 dark:text-slate-300 text-sm mt-2">{{ __('company.avg_cost_per_vehicle') }} {{ number_format($company->maintenanceCost() / $company->vehicles()->count(), 0) }} {{ __('company.sar') }}</p>
                        @endif
                    </div>
                    <div class="bg-orange-100 dark:bg-orange-900/30 p-3 rounded-lg">
                        <i class="fa-solid fa-wrench text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-4 sm:p-6 border border-slate-200/70 dark:border-slate-800 border-s-4 border-s-emerald-500 min-w-0 [dir="rtl"]:border-s-0 [dir="rtl"]:border-e-4 [dir="rtl"]:border-e-emerald-500">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('company.total_fuel_cost') }}</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100 mt-2">{{ number_format($company->fuelsCost()) }} <span class="text-lg">{{ __('company.sar') }}</span></h3>
                        <div class="mt-4">
                            <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('company.total_cost') }}</p>
                            <h4 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($company->totalActualCost()) }} <span class="text-lg">{{ __('company.sar') }}</span></h4>
                        </div>
                        @if($company->otherCost() > 0)
                            <div class="mt-4">
                                <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('company.other') }}</p>
                                <h4 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($company->otherCost()) }} <span class="text-lg">{{ __('company.sar') }}</span></h4>
                            </div>
                        @endif
                    </div>
                    <div class="bg-emerald-100 dark:bg-emerald-900/30 p-3 rounded-lg">
                        <i class="fa-solid fa-gas-pump text-emerald-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-4 sm:p-6 border border-slate-200/70 dark:border-slate-800 border-s-4 border-s-violet-500 min-w-0 [dir="rtl"]:border-s-0 [dir="rtl"]:border-e-4 [dir="rtl"]:border-e-violet-500">
                <div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('company.cost_comparison') }}</p>
                    <div class="mt-4 space-y-4">
                        <div>
                            <div class="flex justify-between">
                                <span class="text-slate-700 dark:text-slate-300">{{ __('company.daily_cost') }}</span>
                                <span class="font-bold">{{ number_format($company->dailyCost()) }} <span class="text-sm">{{ __('company.sar') }}</span></span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 mt-1">
                                <div class="bg-violet-600 h-2 rounded-full" style="width: {{ min($company->dailyProgressPercentage(), 100) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between">
                                <span class="text-slate-700 dark:text-slate-300">{{ __('company.monthly_cost') }}</span>
                                <span class="font-bold">{{ number_format($company->monthlyCost()) }} <span class="text-sm">{{ __('company.thousand_sar') }}</span></span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 mt-1">
                                <div class="bg-sky-600 h-2 rounded-full" style="width: {{ min($company->monthlyProgressPercentage(), 100) }}%"></div>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                            <p class="text-slate-700 dark:text-slate-300 text-sm">{{ __('company.vs_last_7_months') }}</p>
                            @php
                                $percentage = $company->lastSevenMonthsPercentage();
                                $limit = 5;
                            @endphp
                            @if($percentage > $limit)
                                <p class="text-emerald-600 text-sm mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-up"></i>
                                    {{ __('company.higher_by') }} {{ number_format($percentage, 2) }}%
                                </p>
                            @elseif($percentage < -$limit)
                                <p class="text-red-600 text-sm mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-down"></i>
                                    {{ __('company.lower_by') }} {{ number_format(abs($percentage), 2) }}%
                                </p>
                            @else
                                <p class="text-slate-500 text-sm mt-1">{{ __('company.stable') }} (±{{ number_format(abs($percentage), 2) }}%)</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            {{-- Top 5 vehicles by cost --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-4 sm:p-6 border border-slate-200/70 dark:border-slate-800 min-w-0 overflow-hidden">
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100 mb-4 sm:mb-6">{{ __('company.top_5_vehicles') }}</h2>
                <div class="overflow-x-auto -mx-4 sm:mx-0 sm:overflow-visible min-w-0">
                    <table class="w-full min-w-[280px] text-sm sm:text-base">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-3 text-slate-600 dark:text-slate-400 font-medium text-start">{{ __('company.vehicle_number') }}</th>
                                <th class="pb-3 text-slate-600 dark:text-slate-400 font-medium text-start">{{ __('company.type') }}</th>
                                <th class="pb-3 text-slate-600 dark:text-slate-400 font-medium text-end">{{ __('company.cost') }}</th>
                                <th class="pb-3 text-slate-600 dark:text-slate-400 font-medium text-end">{{ __('company.percentage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->getTopVehiclesByServiceConsumptionAndCost() as $v)
                                <tr class="border-b border-slate-100 dark:border-slate-800 last:border-0">
                                    <td class="py-4 text-start">
                                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $v->make }} {{ $v->model }}</div>
                                        <div class="text-xs text-slate-500">{{ $v->plate_number }}</div>
                                    </td>
                                    <td class="py-4 text-slate-600 dark:text-slate-400 text-start">
                                        {{ $v->services_count }} {{ __('company.service') }}
                                        @if(($v->total_fuel_cost ?? 0) > 0)
                                            <span class="text-amber-600">+ {{ __('company.fuel_report') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 font-bold text-slate-900 dark:text-slate-100 text-end">{{ number_format($v->total_cost ?? $v->total_service_cost, 2) }} {{ __('company.sar') }}</td>
                                    <td class="py-4 text-end">
                                        <span class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 text-xs font-semibold px-2.5 py-0.5 rounded">
                                            {{ number_format($v->percentage, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-500">{{ __('company.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @php $summary = $company->getTop5VehiclesSummary(); @endphp
                <div class="mt-4 text-sm text-slate-500">
                    <p>
                        {{ __('company.top5_summary') }}:
                        {{ number_format($summary['top_total'], 2) }} {{ __('company.sar') }}
                        ({{ $summary['ui_percentage'] }}% {{ __('company.of_total_cost') }})
                    </p>
                </div>
            </div>

            {{-- Fleet performance indicators --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-4 sm:p-6 border border-slate-200/70 dark:border-slate-800 min-w-0">
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-6">{{ __('company.fleet_indicators') }}</h2>
                @php
                    $indicatorUI = function ($direction) {
                        return match($direction) {
                            'up' => ['textClass' => 'text-green-600', 'barClass' => 'bg-green-600', 'text' => __('company.above_normal'), 'icon' => '↑'],
                            'down' => ['textClass' => 'text-red-600', 'barClass' => 'bg-red-600', 'text' => __('company.below_normal'), 'icon' => '↓'],
                            default => ['textClass' => 'text-sky-600', 'barClass' => 'bg-blue-600', 'text' => __('company.stable_indicator'), 'icon' => '→'],
                        };
                    };
                    $maintenance = $company->maintenanceCostIndicator();
                    $fuel = $company->fuelConsumptionIndicator();
                    $operating = $company->operatingCostIndicator();
                    $mUI = $indicatorUI($maintenance['direction']);
                    $fUI = $indicatorUI($fuel['direction']);
                    $oUI = $indicatorUI($operating['direction']);
                @endphp
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium text-slate-700 dark:text-slate-300">{{ __('company.maintenance_cost') }}</h3>
                            <span class="{{ $mUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $mUI['text'] }} <span>{{ $mUI['icon'] }}</span>
                            </span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-4">
                            <div class="{{ $mUI['barClass'] }} h-4 rounded-full" style="width: {{ min($maintenance['percent'], 100) }}%"></div>
                        </div>
                        <p class="text-slate-500 text-sm mt-2">
                            {{ $mUI['text'] }} {{ __('company.from_normal_at') }} {{ $maintenance['percent'] }}%
                        </p>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium text-slate-700 dark:text-slate-300">{{ __('company.fuel_consumption') }}</h3>
                            <span class="{{ $fUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $fUI['text'] }} {{ $fUI['icon'] }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-4">
                            <div class="{{ $fUI['barClass'] }} h-4 rounded-full" style="width: {{ min($fuel['percent'], 100) }}%"></div>
                        </div>
                        <p class="text-slate-500 text-sm mt-2">
                            {{ $fUI['text'] }} {{ __('company.from_normal_at') }} {{ $fuel['percent'] }}%
                        </p>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium text-slate-700 dark:text-slate-300">{{ __('company.total_operating_cost') }}</h3>
                            <span class="{{ $oUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $oUI['text'] }} {{ $oUI['icon'] }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-4">
                            <div class="{{ $oUI['barClass'] }} h-4 rounded-full" style="width: {{ min($operating['percent'], 100) }}%"></div>
                        </div>
                        <p class="text-slate-500 text-sm mt-2">
                            {{ $oUI['text'] }} {{ __('company.from_normal_at') }} {{ $operating['percent'] }}%
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl mt-6">
                        <h4 class="font-medium text-slate-700 dark:text-slate-300 mb-2">{{ __('company.indicators_summary') }}</h4>
                        <ul class="text-slate-600 dark:text-slate-400 text-sm space-y-1">
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                <span>{{ __('company.maintenance_performance') }} {{ $maintenance['direction'] === 'down' ? __('company.maintenance_good') : __('company.maintenance_needs_improvement') }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                <span>{{ __('company.fuel_needs_review') }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-sky-500 rounded-full"></span>
                                <span>{{ __('company.operating_cost_ok') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Invoices --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-4 sm:p-6 border border-slate-200/70 dark:border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100">{{ __('company.recent_invoices') }}</h2>
                <a href="{{ route('company.invoices.index') }}" class="text-sm font-semibold text-sky-600 hover:text-sky-700 inline-flex items-center gap-1">
                    {{ __('common.view_all') }} <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
                </a>
            </div>
            @if($company->invoices->count() > 0)
                <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                    <table class="w-full text-sm min-w-[480px]">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400">
                                <th class="pb-3 text-start font-medium">#</th>
                                <th class="pb-3 text-start font-medium">{{ __('company.invoice_number') }}</th>
                                <th class="pb-3 text-start font-medium">{{ __('company.total') }}</th>
                                <th class="pb-3 text-start font-medium">{{ __('company.date') }}</th>
                                <th class="pb-3 text-start font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($company->invoices as $inv)
                                <tr class="border-b border-slate-100 dark:border-slate-800 last:border-0">
                                    <td class="py-3 font-bold">{{ $inv->id }}</td>
                                    <td class="py-3">{{ $inv->invoice_number ?? '-' }}</td>
                                    <td class="py-3 font-semibold">{{ number_format((float)($inv->total ?? 0), 2) }} SAR</td>
                                    <td class="py-3 text-slate-500">{{ $inv->created_at?->format('Y-m-d') }}</td>
                                    <td class="py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('company.invoices.show', $inv) }}" class="text-sky-600 hover:text-sky-700 font-semibold">{{ __('common.view') }}</a>
                                            <a href="{{ route('company.invoices.pdf', $inv) }}" download="invoice-{{ $inv->invoice_number ?? $inv->id }}.pdf" class="text-emerald-600 hover:text-emerald-700 font-semibold">
                                                <i class="fa-solid fa-file-pdf"></i> {{ __('common.download') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-slate-500 dark:text-slate-400 py-6 text-center">{{ __('company.no_invoices_yet') }}</p>
            @endif
        </div>

        <footer class="text-center text-slate-500 dark:text-slate-400 text-sm py-4">
            {{ __('company.last_update') }}: {{ now()->format('Y-m-d') }}
        </footer>

        {{-- Quick Actions --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-black mb-3 sm:mb-4">{{ __('company.quick_actions') }}</h2>
            <div class="flex flex-wrap gap-2 sm:gap-3">
                <a href="{{ route('company.orders.index') }}" class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold">
                    <i class="fa-solid fa-receipt me-2"></i> {{ __('company.orders') }}
                </a>
                <a href="{{ route('company.vehicles.index') }}" class="px-4 py-3 rounded-2xl bg-slate-700 hover:bg-slate-800 text-white font-bold">
                    <i class="fa-solid fa-car me-2"></i> {{ __('company.vehicles') }}
                </a>
                <a href="{{ route('company.fuel.index') }}" class="px-4 py-3 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold">
                    <i class="fa-solid fa-gas-pump me-2"></i> {{ __('company.fuel_report') }}
                </a>
                <a href="{{ route('company.invoices.index') }}" class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                    <i class="fa-solid fa-file-invoice me-2"></i> {{ __('company.invoices') }}
                </a>
                <a href="{{ route('company.branches.index') }}" class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold hover:bg-slate-50 dark:hover:bg-slate-800">
                    <i class="fa-solid fa-code-branch me-2"></i> {{ __('company.branches') }}
                </a>
            </div>
        </div>
    </div>
@endsection
