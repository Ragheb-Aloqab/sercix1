@extends('admin.layouts.app')

@section('title', __('company.dashboard_title') . ' | ' . ($siteName ?? 'SERV.X'))
@section('page_title', __('company.dashboard_title'))

@push('styles')
<style>
    .dashboard-glass {
        position: relative;
        background-image: url('https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1920');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    .dashboard-glass::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 41, 59, 0.88) 100%);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .dashboard-glass .dashboard-content {
        position: relative;
        z-index: 1;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    .animate-fade-in-delay-1 { animation-delay: 0.1s; }
    .animate-fade-in-delay-2 { animation-delay: 0.2s; }
    .animate-fade-in-delay-3 { animation-delay: 0.3s; }
</style>
@endpush

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto">
        {{-- Header Section --}}
        <header class="mb-8 sm:mb-10 animate-fade-in">
            <div class="inline-block px-6 py-3 rounded-xl bg-slate-900/80 border border-sky-500/60">
                <h1 class="text-2xl sm:text-3xl font-black text-white" dir="rtl">
                    {{ __('dashboard.data_board') }}
                </h1>
            </div>
        </header>

        {{-- First Row: 3 KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
            {{-- عدد السيارات --}}
            <div class="group rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:scale-[1.02] hover:border-slate-400/50 transition-all duration-300 animate-fade-in animate-fade-in-delay-1 opacity-0">
                <p class="text-sm text-slate-400 mb-2 text-end">{{ __('company.vehicles_count') }}</p>
                <div class="flex items-end justify-between gap-3">
                    <span class="text-3xl sm:text-4xl font-black text-white">{{ $vehiclesCount ?? 0 }}</span>
                    @if(($vehiclesTrend ?? 'up') === 'up')
                        <span class="text-emerald-500 text-xl"><i class="fa-solid fa-caret-up"></i></span>
                    @else
                        <span class="text-red-500 text-xl"><i class="fa-solid fa-caret-down"></i></span>
                    @endif
                </div>
            </div>

            {{-- إجمالي تكلفة الصيانة --}}
            <div class="group rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:scale-[1.02] hover:border-slate-400/50 transition-all duration-300 animate-fade-in animate-fade-in-delay-2 opacity-0">
                <p class="text-sm text-slate-400 mb-2 text-end">{{ __('company.total_maintenance_cost') }}</p>
                <div class="flex items-end justify-between gap-3">
                    <span class="text-3xl sm:text-4xl font-black text-white">{{ number_format($maintenanceSummary['total'] ?? 0, 0) }}</span>
                    @if(($maintenanceTrend ?? 'down') === 'up')
                        <span class="text-emerald-500 text-xl"><i class="fa-solid fa-caret-up"></i></span>
                    @else
                        <span class="text-red-500 text-xl"><i class="fa-solid fa-caret-down"></i></span>
                    @endif
                </div>
            </div>

            {{-- إجمالي تكلفة المحروقات --}}
            <div class="group rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:scale-[1.02] hover:border-slate-400/50 transition-all duration-300 animate-fade-in animate-fade-in-delay-3 opacity-0">
                <p class="text-sm text-slate-400 mb-2 text-end">{{ __('company.total_fuel_cost') }}</p>
                <div class="flex items-end justify-between gap-3">
                    <span class="text-3xl sm:text-4xl font-black text-white">{{ number_format($fuelSummary['total'] ?? 0, 0) }}</span>
                    @if(($fuelTrend ?? 'down') === 'up')
                        <span class="text-emerald-500 text-xl"><i class="fa-solid fa-caret-up"></i></span>
                    @else
                        <span class="text-red-500 text-xl"><i class="fa-solid fa-caret-down"></i></span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Second Row: 3 Main Sections --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- A) إجمالي التكلفة Card --}}
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <h2 class="text-base font-bold text-slate-300 mb-4 text-end">{{ __('company.total_cost') }}</h2>
                <div class="flex flex-col sm:flex-row items-end justify-between gap-4">
                    <div class="flex gap-3 sm:gap-4 flex-wrap">
                        <div class="rounded-xl bg-slate-700/50 border border-slate-500/30 px-4 py-3 min-w-[80px]">
                            <p class="text-xs text-slate-400">{{ __('dashboard.day') }}</p>
                            <p class="text-lg font-bold text-white">{{ $dailyCost ?? 0 }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-700/50 border border-slate-500/30 px-4 py-3 min-w-[80px]">
                            <p class="text-xs text-slate-400">{{ __('dashboard.month') }}</p>
                            <p class="text-lg font-bold text-white">{{ $monthlyCost ?? 0 }}</p>
                        </div>
                        <div class="relative w-14 h-14 shrink-0">
                            <svg class="w-14 h-14 -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="16" fill="none" stroke="rgba(100,116,139,0.3)" stroke-width="2"/>
                                <circle cx="18" cy="18" r="16" fill="none" stroke="rgba(56,189,248,0.6)" stroke-width="2" stroke-dasharray="{{ min(abs($sevenMonthPercent ?? 0) * 1.01, 100) }} 100" stroke-linecap="round"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-slate-300">{{ $sevenMonthPercent ?? 0 }}%</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl sm:text-3xl font-black text-white">{{ number_format($totalCost ?? 0, 0) }}</span>
                        @if(($sevenMonthPercent ?? 0) > 0)
                            <span class="text-red-500 text-lg" title="{{ __('company.above_average') }}"><i class="fa-solid fa-caret-down"></i></span>
                        @else
                            <span class="text-emerald-500 text-lg" title="{{ __('company.below_average') }}"><i class="fa-solid fa-caret-up"></i></span>
                        @endif
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-2 text-end">{{ __('dashboard.six_month_comparison') }}</p>
            </div>

            {{-- B) أعلى خمس سيارات تكلفة --}}
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <h2 class="text-base font-bold text-slate-300 mb-4 text-end">{{ __('company.top_5_vehicles') }}</h2>
                @php $top5 = ($topVehicles ?? collect())->take(5); @endphp
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    @for($i = 0; $i < 5; $i++)
                        @if(isset($top5[$i]))
                            @php $v = $top5[$i]; @endphp
                            <a href="{{ route('company.vehicles.show', $v->id) }}" class="block rounded-xl bg-slate-700/50 border border-slate-500/30 p-3 hover:border-slate-400/50 transition-colors">
                                <p class="text-xs text-slate-400 truncate">{{ $v->make ?? '' }} {{ $v->model ?? '' }}</p>
                                <p class="text-sm font-bold text-white truncate">{{ number_format($v->total_cost ?? $v->total_service_cost ?? 0, 0) }} {{ __('company.sar') }}</p>
                            </a>
                        @else
                            <div class="rounded-xl bg-slate-700/30 border border-slate-500/20 p-3 border-dashed">
                                <p class="text-xs text-slate-500">—</p>
                                <p class="text-sm text-slate-500">—</p>
                            </div>
                        @endif
                    @endfor
                </div>
                <p class="text-xs text-slate-500 mt-4 text-end">{{ __('company.top5_summary') }}: {{ number_format($top5Summary['top_total'] ?? 0, 2) }} {{ __('company.sar') }} ({{ $top5Summary['ui_percentage'] ?? 0 }}% {{ __('company.of_total_cost') }})</p>
            </div>

            {{-- C) مؤشرات الأسطول --}}
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <h2 class="text-base font-bold text-slate-300 mb-4 text-end">{{ __('company.fleet_indicators') }}</h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-3" dir="rtl">
                        <span class="w-6 h-4 rounded border flex-shrink-0 {{ ($maintenanceIndicator['direction'] ?? '') === 'down' || ($fuelIndicator['direction'] ?? '') === 'down' ? 'bg-emerald-500/40 border-emerald-400/60' : 'border-slate-500/50 bg-slate-700/30' }}"></span>
                        <span class="text-sm text-slate-300">{{ __('company.below_average') }}</span>
                    </div>
                    <div class="flex items-center gap-3" dir="rtl">
                        <span class="w-6 h-4 rounded border flex-shrink-0 {{ ($maintenanceIndicator['direction'] ?? '') === 'up' || ($fuelIndicator['direction'] ?? '') === 'up' ? 'bg-red-500/40 border-red-400/60' : 'border-slate-500/50 bg-slate-700/30' }}"></span>
                        <span class="text-sm text-slate-300">{{ __('company.above_average') }}</span>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-slate-400">{{ __('company.maintenance_cost') }}</span>
                            <span class="{{ $maintenanceUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $maintenanceUI['text'] }} <span>{{ $maintenanceUI['icon'] }}</span>
                            </span>
                        </div>
                        <div class="w-full bg-slate-700/50 rounded-full h-4">
                            <div class="{{ $maintenanceUI['barClass'] }} h-4 rounded-full" style="width: {{ min($maintenanceIndicator['percent'] ?? 0, 100) }}%"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 text-end">{{ $maintenanceUI['text'] }} {{ __('company.from_normal_at') }} {{ $maintenanceIndicator['percent'] ?? 0 }}%</p>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-slate-400">{{ __('company.fuel_consumption') }}</span>
                            <span class="{{ $fuelUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $fuelUI['text'] }} {{ $fuelUI['icon'] }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-700/50 rounded-full h-4">
                            <div class="{{ $fuelUI['barClass'] }} h-4 rounded-full" style="width: {{ min($fuelIndicator['percent'] ?? 0, 100) }}%"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 text-end">{{ $fuelUI['text'] }} {{ __('company.from_normal_at') }} {{ $fuelIndicator['percent'] ?? 0 }}%</p>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-slate-400">{{ __('company.total_operating_cost') }}</span>
                            <span class="{{ $operatingUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $operatingUI['text'] }} {{ $operatingUI['icon'] }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-700/50 rounded-full h-4">
                            <div class="{{ $operatingUI['barClass'] }} h-4 rounded-full" style="width: {{ min($operatingIndicator['percent'] ?? 0, 100) }}%"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 text-end">{{ $operatingUI['text'] }} {{ __('company.from_normal_at') }} {{ $operatingIndicator['percent'] ?? 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions & Recent Invoices (compact) --}}
        <div class="mt-8 grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <h2 class="text-base font-bold text-slate-300 mb-4 text-end">{{ __('company.quick_actions') }}</h2>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('company.orders.index') }}" class="px-4 py-2 rounded-xl bg-sky-600/80 hover:bg-sky-500 text-white text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-receipt me-2"></i>{{ __('company.orders') }}
                    </a>
                    <a href="{{ route('company.vehicles.index') }}" class="px-4 py-2 rounded-xl bg-slate-600/80 hover:bg-slate-500 text-white text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-car me-2"></i>{{ __('company.vehicles') }}
                    </a>
                    <a href="{{ route('company.fuel.index') }}" class="px-4 py-2 rounded-xl bg-amber-600/80 hover:bg-amber-500 text-white text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-gas-pump me-2"></i>{{ __('company.fuel_report') }}
                    </a>
                    <a href="{{ route('company.invoices.index') }}" class="px-4 py-2 rounded-xl bg-emerald-600/80 hover:bg-emerald-500 text-white text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-file-invoice me-2"></i>{{ __('company.invoices') }}
                    </a>
                </div>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-300 text-end">{{ __('company.recent_invoices') }}</h2>
                    <a href="{{ route('company.invoices.index') }}" class="text-sm text-sky-400 hover:text-sky-300">{{ __('common.view_all') }}</a>
                </div>
                @if(count($recentInvoices ?? []) > 0)
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach($recentInvoices->take(5) as $inv)
                            <a href="{{ route('company.invoices.show', $inv) }}" class="flex justify-between items-center py-2 border-b border-slate-600/50 last:border-0 text-sm hover:text-sky-300 transition-colors">
                                <span>{{ $inv->invoice_number ?? '#' . $inv->id }}</span>
                                <span class="font-semibold">{{ number_format((float)($inv->total ?? 0), 2) }} {{ __('company.sar') }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-500 text-sm py-4">{{ __('company.no_invoices_yet') }}</p>
                @endif
            </div>
        </div>

        <footer class="text-center text-slate-500 text-sm mt-8">
            {{ __('company.last_update') }}: {{ now()->format('Y-m-d') }}
        </footer>
    </div>
</div>
@endsection
