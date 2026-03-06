@extends('admin.layouts.app')

@section('title', __('company.dashboard_title') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', auth('company')->check() ? (auth('company')->user()->company_name ?? __('company.dashboard_title')) : __('company.dashboard_title'))
@section('subtitle', __('dashboard.dashboard_v1'))

@section('content')
{{-- Mobile: 3x3 grid (visible when viewport < lg) --}}
<div class="lg:hidden pb-4">
    <div class="grid grid-cols-3 gap-3 sm:gap-4">
        @php
            $mobileGridItems = [
                ['href' => route('company.vehicles.index'), 'icon' => 'fa-car', 'label' => __('fleet.my_vehicles')],
                ['href' => route('company.maintenance-requests.index'), 'icon' => 'fa-screwdriver-wrench', 'label' => __('fleet.maintenance_requests')],
                ['href' => route('company.maintenance-offers.index'), 'icon' => 'fa-tags', 'label' => __('fleet.maintenance_offers')],
                ['href' => route('company.maintenance-invoices.index'), 'icon' => 'fa-file-invoice', 'label' => __('fleet.maintenance_invoices')],
                ['href' => route('company.fuel-balance'), 'icon' => 'fa-gas-pump', 'label' => __('fleet.fuel')],
                ['href' => route('company.tracking.index'), 'icon' => 'fa-location-dot', 'label' => __('fleet.tracking')],
                ['href' => route('company.reports.index'), 'icon' => 'fa-chart-pie', 'label' => __('fleet.reports')],
                ['href' => route('company.insurances.index'), 'icon' => 'fa-shield-halved', 'label' => __('fleet.my_insurance')],
                ['href' => route('company.settings'), 'icon' => 'fa-gear', 'label' => __('fleet.settings')],
            ];
        @endphp
        @foreach ($mobileGridItems as $item)
            <a href="{{ $item['href'] }}" wire:navigate class="company-mobile-grid-card flex flex-col items-center justify-center gap-2 p-4 sm:p-5 rounded-2xl bg-slate-100 dark:bg-white/5 backdrop-blur-sm border border-slate-200 dark:border-white/10 hover:bg-slate-200 dark:hover:bg-white/10 hover:border-sky-400 dark:hover:border-sky-500/30 active:scale-[0.98] transition-all duration-200 min-h-[100px] sm:min-h-[120px]">
                <span class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center bg-sky-500/20 text-sky-600 dark:text-sky-400">
                    <i class="fa-solid {{ $item['icon'] }} text-xl sm:text-2xl"></i>
                </span>
                <span class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white text-center leading-tight">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
    @if(($expiringDocumentsCount ?? 0) > 0 || ($inspectionPendingCount ?? 0) > 0 || ($pendingInvoiceApprovalsCount ?? 0) > 0)
        <div class="mt-4 space-y-3">
            @if(($expiringDocumentsCount ?? 0) > 0)
                <a href="{{ route('company.vehicles.index') }}" class="block p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-400 text-sm font-semibold">
                    <i class="fa-solid fa-file-circle-exclamation me-2"></i>{{ __('vehicles.expiring_documents') }} ({{ $expiringDocumentsCount }})
                </a>
            @endif
            @if(($inspectionPendingCount ?? 0) > 0)
                <a href="{{ route('company.inspections.index') }}" class="block p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-400 text-sm font-semibold">
                    <i class="fa-solid fa-camera me-2"></i>{{ __('inspections.vehicles_pending') }} ({{ $inspectionPendingCount }})
                </a>
            @endif
            @if(($pendingInvoiceApprovalsCount ?? 0) > 0)
                <a href="{{ route('company.maintenance-requests.index', ['status' => 'waiting_for_invoice_approval']) }}" class="block p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-400 text-sm font-semibold">
                    <i class="fa-solid fa-file-invoice me-2"></i>{{ __('maintenance.invoice_approval') ?? 'Invoices Pending Approval' }} ({{ $pendingInvoiceApprovalsCount }})
                </a>
            @endif
        </div>
    @endif
</div>

{{-- Desktop: Executive Fleet Analytics Dashboard --}}
<div class="hidden lg:block dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6 sm:space-y-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-start w-full sm:w-auto">
                <h1 class="dash-page-title">{{ __('dashboard.data_board') }}</h1>
                <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
            </div>
            <div class="flex flex-wrap gap-2 justify-center sm:justify-end">
                <a href="{{ route('company.maintenance-requests.create') }}" class="dash-btn dash-btn-primary">
                    <i class="fa-solid fa-plus"></i>{{ __('fleet.create_request') }}
                </a>
                <a href="{{ route('company.maintenance-requests.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-screwdriver-wrench"></i>{{ __('maintenance.maintenance_requests') }}
                </a>
                <a href="{{ route('company.vehicles.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-car"></i>{{ __('company.vehicles') }}
                </a>
                <a href="{{ route('company.fuel-balance') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-gas-pump"></i>{{ __('fleet.fuel') }}
                </a>
                <a href="{{ route('company.reports.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-chart-pie"></i>{{ __('fleet.reports') }}
                </a>
            </div>
        </div>

        @php
            $mc = $marketComparison ?? null;
            $percentDiff = $mc['percent_difference'] ?? 0;
        @endphp

        {{-- Smart Alert Banner
        @if($mc && $percentDiff > 10)
            <div class="dash-card border-red-500/40 bg-red-500/5">
                <p class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 dark:text-red-400"></i>
                    {{ __('company.alert_exceeds_market') }}
                </p>
            </div>
        @elseif($mc && $percentDiff < -10)
            <div class="dash-card border-emerald-500/40 bg-emerald-500/5">
                <p class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                    {{ __('company.alert_saving_market') }}
                </p>
            </div>
        @endif --}}

        {{-- Document expiry, inspection, invoice alerts (only when data exists) --}}
        @if(($expiringDocumentsCount ?? 0) > 0)
            <div class="dash-card border-amber-500/40 bg-amber-500/5">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-file-circle-exclamation text-amber-600 dark:text-amber-400"></i>
                    {{ __('vehicles.expiring_documents') }} ({{ $expiringDocumentsCount }})
                </h2>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @foreach($expiringDocuments ?? [] as $item)
                        <a href="{{ route('company.vehicles.show', $item->vehicle) }}" class="flex items-center gap-3 p-3 rounded-xl bg-servx-inner hover:bg-servx-inner-hover border border-servx-border transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $item->status === 'expired' ? 'bg-red-500/20 text-red-600 dark:text-red-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' }}">
                                <i class="fa-solid fa-car text-xs"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $item->vehicle->plate_number ?? $item->vehicle->display_name }} — {{ $item->type === \App\Services\ExpiryMonitoringService::DOC_REGISTRATION ? __('vehicles.registration') : __('vehicles.insurance') }}</p>
                                <p class="text-xs text-servx-silver">{{ $item->date?->translatedFormat('d M Y') }} · {{ $item->days_remaining !== null ? ($item->days_remaining < 0 ? abs($item->days_remaining) . ' ' . __('vehicles.days_ago') : $item->days_remaining . ' ' . __('vehicles.days_remaining')) : '-' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-bold border {{ $expiryService->getStatusBadgeClass($item->status) }}">{{ __('vehicles.' . $item->status) }}</span>
                            <i class="fa-solid fa-arrow-left text-sky-600 dark:text-sky-400 shrink-0 rtl:rotate-180"></i>
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('company.vehicles.index') }}" class="inline-block mt-3 text-sm text-sky-600 dark:text-sky-400 hover:text-sky-500 dark:hover:text-sky-300 font-bold">{{ __('common.view_all') }}</a>
            </div>
        @endif

        @if(($pendingInvoiceApprovalsCount ?? 0) > 0)
            <div class="dash-card border-amber-500/40 bg-amber-500/5">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-amber-600 dark:text-amber-400"></i>
                    {{ __('maintenance.invoice_approval') ?? 'Invoices Pending Approval' }} ({{ $pendingInvoiceApprovalsCount }})
                </h2>
                <p class="text-sm text-servx-silver mb-3">{{ __('maintenance.invoice_approval_desc') ?? 'Maintenance invoices uploaded by centers — approve or reject' }}</p>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @foreach($pendingInvoiceApprovals ?? [] as $req)
                        <a href="{{ route('company.maintenance-requests.show', $req) }}" class="flex items-center gap-3 p-3 rounded-xl bg-servx-inner hover:bg-servx-inner-hover border border-servx-border transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-amber-500/20 text-amber-600 dark:text-amber-400">
                                <i class="fa-solid fa-file-invoice text-xs"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ __('maintenance.request') }} #{{ $req->id }} — {{ $req->approvedCenter?->name ?? '-' }}</p>
                                <p class="text-xs text-servx-silver">{{ $req->vehicle?->plate_number ?? '-' }} · {{ $req->final_invoice_amount ? number_format($req->final_invoice_amount, 2) . ' ' . __('company.sar') : '-' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-bold border border-amber-400/50 text-amber-300 bg-amber-500/20">{{ __('maintenance.status_waiting_for_invoice_approval') ?? 'Pending' }}</span>
                            <i class="fa-solid fa-arrow-left text-sky-600 dark:text-sky-400 shrink-0 rtl:rotate-180"></i>
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('company.maintenance-requests.index', ['status' => 'waiting_for_invoice_approval']) }}" class="inline-block mt-3 text-sm text-sky-600 dark:text-sky-400 hover:text-sky-500 dark:hover:text-sky-300 font-bold">{{ __('common.view_all') }}</a>
            </div>
        @endif

        @if(($inspectionPendingCount ?? 0) > 0)
            <div class="dash-card {{ ($inspectionOverdueCount ?? 0) > 0 ? 'border-red-500/40 bg-red-500/5' : 'border-amber-500/40 bg-amber-500/5' }}">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-camera text-amber-600 dark:text-amber-400"></i>
                    {{ __('inspections.vehicles_pending') }}
                    @if(($inspectionOverdueCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-500/30 text-red-300 border border-red-400/50">{{ $inspectionOverdueCount }} {{ __('inspections.overdue') }}</span>
                    @endif
                </h2>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @foreach($inspectionPendingVehicles ?? [] as $item)
                        <a href="{{ route('company.vehicles.show', $item->vehicle) }}" class="flex items-center gap-3 p-3 rounded-xl bg-servx-inner hover:bg-servx-inner-hover border border-servx-border transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $item->status === 'overdue' ? 'bg-red-500/20 text-red-600 dark:text-red-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' }}">
                                <i class="fa-solid fa-car text-xs"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $item->vehicle->plate_number ?? $item->vehicle->display_name }}</p>
                                <p class="text-xs text-servx-silver">{{ __('inspections.due_date') }}: {{ $item->due_date?->translatedFormat('d M Y') ?? '—' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-bold border {{ $item->status === 'overdue' ? 'border-red-400/50 text-red-300 bg-red-500/20' : 'border-amber-400/50 text-amber-300 bg-amber-500/20' }}">{{ __('inspections.' . $item->status) }}</span>
                            <i class="fa-solid fa-arrow-left text-sky-600 dark:text-sky-400 shrink-0 rtl:rotate-180"></i>
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('company.inspections.index') }}" class="inline-block mt-3 text-sm text-sky-600 dark:text-sky-400 hover:text-sky-500 dark:hover:text-sky-300 font-bold">{{ __('inspections.view_all') }}</a>
            </div>
        @endif

        @if(count($announcements ?? []) > 0)
            <div class="dash-card border-sky-500/40">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-sky-600 dark:text-sky-400"></i>
                    {{ __('admin_dashboard.announcements') }}
                </h2>
                <div class="space-y-3 max-h-32 overflow-y-auto">
                    @foreach($announcements as $ann)
                        <div class="p-3 rounded-xl bg-servx-inner border border-servx-border">
                            <p class="font-bold text-white">{{ $ann->title }}</p>
                            <p class="text-sm text-servx-silver mt-1 line-clamp-2">{{ Str::limit(strip_tags($ann->body), 120) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @php $mcData = $mc ?? []; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('company.total_company_maintenance') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ number_format($mcData['company_total'] ?? 0, 0) }} {{ __('company.sar') }}</p>
                    <span class="dash-trend dash-trend-stable">
                        <i class="fa-solid fa-minus"></i>
                    </span>
                </div>
            </div>
            @php $mac = $marketAverageCostCard ?? ['value' => 0, 'trend' => 'stable', 'total_mileage_km' => 0]; $marketRate = $mcData['market_rate_per_km'] ?? config('servx.market_avg_per_km', 0.37); @endphp
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('company.market_average_cost') }} <span class="text-xs font-normal opacity-75">({{ $chartMonths ?? 6 }}m)</span></p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ number_format($mac['value'] ?? 0, 0) }} {{ __('company.sar') }}</p>
                    <span class="dash-trend dash-trend-{{ $mac['trend'] ?? 'stable' }}">
                        @if(($mac['trend'] ?? 'stable') === 'up')<i class="fa-solid fa-caret-up"></i>
                        @elseif(($mac['trend'] ?? 'stable') === 'down')<i class="fa-solid fa-caret-down"></i>
                        @else<i class="fa-solid fa-minus"></i>@endif
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('company.difference_saving_over') }}</p>
                @php $diff = $mcData['total_difference'] ?? (($mcData['company_total'] ?? 0) - ($mcData['market_average'] ?? 0)); @endphp
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value {{ $diff > 0 ? 'text-red-400' : ($diff < 0 ? 'text-emerald-400' : '') }}">
                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 0) }} {{ __('company.sar') }}
                    </p>
                    <span class="dash-trend dash-trend-{{ $diff > 0 ? 'down' : ($diff < 0 ? 'up' : 'stable') }}">
                        @if($diff > 0)<i class="fa-solid fa-caret-up"></i>
                        @elseif($diff < 0)<i class="fa-solid fa-caret-down"></i>
                        @else<i class="fa-solid fa-minus"></i>@endif
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('company.total_active_vehicles') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ $vehiclesCount ?? 0 }}</p>
                    <span class="dash-trend dash-trend-up">
                        <i class="fa-solid fa-caret-up"></i>
                    </span>
                </div>
            </div>
        </div>
        {{-- Row 2: Visual Analysis (Gauge + Chart) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            {{-- Left: Performance Gauge --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.market_comparison') }}</h2>
                <div class="dash-gauge-wrap">
                    @php
                        $ratio = $mcData['cost_ratio'] ?? 0;
                        $gaugeColor = $ratio < 95 ? 'var(--servx-green)' : ($ratio > 105 ? 'var(--servx-red-trend)' : 'var(--servx-amber)');
                        $arcLen = 126;
                        $normalized = min(1, $ratio / 200);
                        $strokeDash = $normalized * $arcLen;
                        $strokeOffset = $arcLen - $strokeDash;
                        $gaugeLabel = $ratio < 95 ? __('company.below_average') : ($ratio > 105 ? __('company.above_average') : __('company.average'));
                    @endphp
                    <svg class="dash-gauge-svg" viewBox="0 0 100 55" preserveAspectRatio="xMidYMin meet">
                        <path class="dash-gauge-bg" d="M 10 50 A 40 40 0 0 1 90 50" />
                        <path class="dash-gauge-fill" d="M 10 50 A 40 40 0 0 1 90 50" stroke="{{ $gaugeColor }}" stroke-dasharray="{{ $arcLen }}" stroke-dashoffset="{{ $strokeOffset }}" />
                    </svg>
                    <div class="dash-gauge-center">
                        <span class="dash-gauge-value">{{ number_format($ratio, 1) }}%</span>
                        <span class="dash-gauge-label {{ $ratio < 95 ? 'text-emerald-400' : ($ratio > 105 ? 'text-red-400' : 'text-amber-400') }}">{{ $gaugeLabel }}</span>
                    </div>
                </div>
                <div class="space-y-2 text-sm border-t border-slate-600/50 pt-4">
                    @php $totalKm = $mcData['total_kilometers'] ?? 0; $rate = $mcData['market_rate_per_km'] ?? config('servx.market_avg_per_km', 0.37); @endphp
                    <div class="flex justify-between">
                        <span class="text-servx-silver">{{ __('company.total_mileage_period') }} ({{ $chartMonths ?? 6 }}m)</span>
                        <span class="font-bold text-white">{{ number_format($totalKm, 1) }} {{ __('common.km') }}</span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-slate-600/30">
                        <span class="text-servx-silver">{{ __('company.company_total') }}</span>
                        <span class="font-bold text-white">{{ number_format($mcData['company_total'] ?? 0, 0) }} {{ __('company.sar') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-servx-silver">{{ __('company.market_average') }}</span>
                        <span class="font-bold text-white">{{ number_format($mcData['market_average'] ?? 0, 0) }} {{ __('company.sar') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-servx-silver">{{ __('company.difference') }}</span>
                        <span class="font-bold {{ $diff >= 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 0) }} {{ __('company.sar') }}</span>
                    </div>
                </div>
                @if($mcData)
                    <button type="button" onclick="document.getElementById('marketComparisonModal').classList.remove('hidden')" class="mt-4 text-sm text-sky-400 hover:text-sky-300 font-semibold">
                        {{ __('company.view_comparison_details') }}
                    </button>
                @endif
            </div>

            {{-- Right: Monthly Comparison Chart --}}
            <div class="dash-card dash-card-interactive">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h2 class="dash-section-title">{{ __('company.monthly_comparison') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ request()->fullUrlWithQuery(['chart_months' => 6]) }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ ($chartMonths ?? 6) === 6 ? 'bg-sky-500/30 text-sky-300 border border-sky-400/50' : 'bg-slate-700/50 text-servx-silver border border-slate-600/50 hover:border-sky-400/30' }}">
                            {{ __('company.last_6_months') }}
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['chart_months' => 12]) }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ ($chartMonths ?? 6) === 12 ? 'bg-sky-500/30 text-sky-300 border border-sky-400/50' : 'bg-slate-700/50 text-servx-silver border border-slate-600/50 hover:border-sky-400/30' }}">
                            {{ __('company.last_12_months') }}
                        </a>
                        <a href="{{ route('company.reports.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-slate-700/50 text-servx-silver border border-slate-600/50 hover:border-sky-400/30">
                            {{ __('company.custom_range') }}
                        </a>
                    </div>
                </div>
                <div class="dash-chart-container">
                    @php
                        $chartData = $monthlyChartData ?? [];
                        $maxVal = 1;
                        foreach ($chartData as $cm) {
                            $maxVal = max($maxVal, $cm['company_total'] ?? 0, $cm['market_total'] ?? 0);
                        }
                    @endphp
                    <div class="dash-chart-bars flex-1 dash-chart-bars--interactive" style="display: grid; grid-template-columns: repeat({{ count($chartData) ?: 6 }}, 1fr); gap: 4px; min-height: 120px; align-items: flex-end;">
                        @foreach($chartData as $m)
                            <div class="dash-chart-bar-group flex flex-col gap-1 items-center group">
                                <div class="w-full flex gap-0.5 items-end" style="height: 80px;">
                                    <div class="dash-chart-bar dash-chart-bar--company flex-1 bg-sky-500 rounded-t transition-all duration-300 ease-out hover:bg-sky-400" style="height: {{ max(4, ($m['company_total'] / $maxVal) * 100) }}%; min-height: 4px; animation: dashBarGrow 0.6s ease-out {{ $loop->index * 0.05 }}s both;"></div>
                                    <div class="dash-chart-bar dash-chart-bar--market flex-1 bg-slate-500/70 rounded-t transition-all duration-300 ease-out hover:bg-slate-500" style="height: {{ max(4, ($m['market_total'] / $maxVal) * 100) }}%; min-height: 4px; animation: dashBarGrow 0.6s ease-out {{ $loop->index * 0.05 + 0.03 }}s both;"></div>
                                </div>
                                <span class="text-xs text-servx-silver group-hover:text-slate-900 dark:group-hover:text-white transition-colors">{{ $m['month_label'] }}</span>
                            </div>
                        @endforeach
                        @if(empty($chartData))
                            <p class="col-span-full text-servx-silver text-sm py-8 text-center">{{ __('company.no_maintenance_data') }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex gap-4 mt-3 text-xs">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-sky-500"></span> {{ __('company.company_total') }}</span>
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-slate-500/70"></span> {{ __('company.market_average') }}</span>
                </div>
            </div>
        </div>

        {{-- Row 3: Operational Insights --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
            {{-- Top 3 Most Expensive Vehicles --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.top_3_expensive_vehicles') }}</h2>
                @php $top3 = ($topVehicles ?? collect())->take(3); @endphp
                <div class="space-y-2">
                    @forelse($top3 as $v)
                        <a href="{{ route('company.vehicles.show', $v->id) }}" class="flex items-center justify-between p-2 rounded-lg bg-servx-inner border border-servx-border hover:border-sky-500/30 transition-colors">
                            <span class="text-sm text-white truncate">{{ $v->plate_number ?? ($v->make . ' ' . $v->model) }}</span>
                            <span class="text-sm font-bold text-sky-400 shrink-0">{{ number_format($v->total_cost ?? 0, 0) }} {{ __('company.sar') }}</span>
                        </a>
                    @empty
                        <p class="text-servx-silver text-sm py-4">{{ __('company.no_maintenance_data') }}</p>
                    @endforelse
                </div>
            </div>
            {{-- Top Service Center --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.top_service_center') }}</h2>
                @if($topServiceCenter ?? null)
                    <div class="space-y-2">
                        <p class="font-bold text-white">{{ $topServiceCenter['name'] }}</p>
                        <p class="text-sm text-servx-silver">{{ $topServiceCenter['jobs'] }} {{ __('company.jobs_count') }}</p>
                        <p class="text-sm font-semibold text-sky-400">{{ number_format($topServiceCenter['total_amount'], 0) }} {{ __('company.sar') }}</p>
                    </div>
                @else
                    <p class="text-servx-silver text-sm py-4">{{ __('company.no_maintenance_data') }}</p>
                @endif
            </div>
            {{-- Average Cost Per Vehicle --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.avg_cost_per_vehicle_label') }}</h2>
                <p class="dash-card-value dash-card-value-lg">{{ number_format($avgCostPerVehicle ?? 0, 0) }} {{ __('company.sar') }}</p>
                <p class="text-xs text-servx-silver">{{ __('company.company_total') }} ÷ {{ __('company.vehicles') }}</p>
            </div>
            {{-- Total Yearly Savings --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.total_yearly_savings') }}</h2>
                <p class="dash-card-value dash-card-value-lg text-emerald-400">{{ number_format($totalYearlySavings ?? 0, 0) }} {{ __('company.sar') }}</p>
                <p class="text-xs text-servx-silver">{{ __('company.market_average') }} − {{ __('company.company_total') }}</p>
            </div>
        </div>

        {{-- Row 4: Order & Activity KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <a href="{{ route('company.orders.index') }}" class="dash-card dash-card-compact dash-card-interactive">
                <p class="dash-card-title">{{ __('dashboard.today_orders') }}</p>
                <p class="dash-card-value">{{ $todayOrders ?? 0 }}</p>
            </a>
            <a href="{{ route('company.orders.index') }}" class="dash-card dash-card-compact dash-card-interactive">
                <p class="dash-card-title">{{ __('dashboard.in_progress') }}</p>
                <p class="dash-card-value">{{ $inProgress ?? 0 }}</p>
            </a>
            <a href="{{ route('company.orders.index') }}" class="dash-card dash-card-compact dash-card-interactive">
                <p class="dash-card-title">{{ __('dashboard.completed') }}</p>
                <p class="dash-card-value">{{ $completed ?? 0 }}</p>
            </a>
            <a href="{{ route('company.maintenance-requests.index', ['status' => 'waiting_for_invoice_approval']) }}" class="dash-card dash-card-compact dash-card-interactive">
                <p class="dash-card-title">{{ __('maintenance.invoices_pending_action') ?? 'Invoices Pending Action' }}</p>
                <p class="dash-card-value">{{ $pendingInvoiceApprovalsCount ?? 0 }}</p>
            </a>
        </div>

        {{-- Row 5: Maintenance Requests Summary + Fuel Summary + Cost Overview --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Maintenance Requests Summary --}}
            <div class="dash-card dash-card-interactive">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="dash-section-title">{{ __('fleet.maintenance_requests') }}</h2>
                    <a href="{{ route('company.maintenance-requests.index') }}" class="dash-link">{{ __('common.view_all') }}</a>
                </div>
                @php $mrCounts = $maintenanceRequestCounts ?? []; @endphp
                <div class="space-y-2">
                    @if(($mrCounts['waiting_quotes'] ?? 0) > 0)
                        <a href="{{ route('company.maintenance-requests.index', ['status' => 'waiting_for_quotes']) }}" class="flex justify-between items-center p-2 rounded-lg bg-servx-inner border border-servx-border hover:border-sky-500/30">
                            <span class="text-sm text-servx-silver">{{ __('maintenance.status_waiting_for_quotes') }}</span>
                            <span class="font-bold text-amber-400">{{ $mrCounts['waiting_quotes'] }}</span>
                        </a>
                    @endif
                    @if(($mrCounts['quote_submitted'] ?? 0) > 0)
                        <a href="{{ route('company.maintenance-requests.index', ['status' => 'quote_submitted']) }}" class="flex justify-between items-center p-2 rounded-lg bg-servx-inner border border-servx-border hover:border-sky-500/30">
                            <span class="text-sm text-servx-silver">{{ __('maintenance.status_quote_submitted') }}</span>
                            <span class="font-bold text-sky-400">{{ $mrCounts['quote_submitted'] }}</span>
                        </a>
                    @endif
                    @if(($mrCounts['in_progress'] ?? 0) > 0)
                        <a href="{{ route('company.maintenance-requests.index', ['status' => 'in_progress']) }}" class="flex justify-between items-center p-2 rounded-lg bg-servx-inner border border-servx-border hover:border-sky-500/30">
                            <span class="text-sm text-servx-silver">{{ __('maintenance.status_in_progress') }}</span>
                            <span class="font-bold text-emerald-400">{{ $mrCounts['in_progress'] }}</span>
                        </a>
                    @endif
                    @if(($mrCounts['total_active'] ?? 0) === 0)
                        <p class="text-servx-silver text-sm py-4">{{ __('company.no_maintenance_data') }}</p>
                    @endif
                </div>
            </div>
            {{-- Fuel Summary --}}
            <a href="{{ route('company.fuel-balance') }}" class="dash-card dash-card-interactive block">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-gas-pump text-sky-600 dark:text-sky-400"></i>
                    {{ __('fleet.fuel') }}
                </h2>
                <p class="dash-card-value dash-card-value-lg">{{ number_format($fuelBalanceTotal ?? 0, 0) }} {{ __('company.sar') }}</p>
                <p class="text-xs text-servx-silver">{{ __('fleet.total_fuel_balance') }}</p>
                <div class="mt-4 pt-4 border-t border-slate-600/50 space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-servx-silver">{{ __('company.total_fuel_cost') }}</span>
                        <span class="font-bold text-white">{{ number_format($fuelSummary['total'] ?? 0, 0) }} {{ __('company.sar') }}</span>
                    </div>
                </div>
            </a>
            {{-- Cost Overview --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.total_cost') }}</h2>
                <p class="dash-card-value dash-card-value-lg">{{ number_format($totalCost ?? 0, 0) }} {{ __('company.sar') }}</p>
                <p class="text-xs text-servx-silver">{{ __('company.maintenance_cost') }} + {{ __('company.total_fuel_cost') }}</p>
                <div class="flex flex-wrap gap-3 mt-4">
                    <div class="dash-stat-mini">
                        <p class="dash-stat-mini-label">{{ __('dashboard.day') }}</p>
                        <p class="dash-stat-mini-value">{{ $dailyCost ?? 0 }}</p>
                    </div>
                    <div class="dash-stat-mini">
                        <p class="dash-stat-mini-label">{{ __('dashboard.month') }}</p>
                        <p class="dash-stat-mini-value">{{ $monthlyCost ?? 0 }}k</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 6: Latest Orders + Quick Links + Enabled Services --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            {{-- Latest Orders --}}
            <div class="dash-card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="dash-section-title">{{ __('dashboard.latest_orders') }}</h2>
                    <a href="{{ route('company.orders.index') }}" class="dash-link">{{ __('common.view_all') }}</a>
                </div>
                <div class="space-y-2 max-h-44 overflow-y-auto">
                    @forelse($latestOrders ?? [] as $o)
                        <a href="{{ route('company.orders.show', $o) }}" class="dash-order-row">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-white truncate">{{ __('dashboard.order') }} #{{ $o->id }} — {{ $o->status }}</p>
                                <p class="text-xs text-servx-silver truncate">{{ $o->city ?? '-' }} · {{ \Illuminate\Support\Str::limit($o->address ?? '', 35) }}</p>
                            </div>
                            <span class="text-sky-600 dark:text-sky-400 text-sm shrink-0"><i class="fa-solid fa-arrow-left ms-1"></i></span>
                        </a>
                    @empty
                        <p class="text-servx-silver text-sm py-6 text-center">{{ __('common.no_orders') }}</p>
                    @endforelse
                </div>
            </div>
            {{-- Quick Links + Enabled Services --}}
            <div class="dash-card">
                <h2 class="dash-section-title mb-4">{{ __('company.quick_actions') }}</h2>
                <div class="flex flex-wrap gap-2 mb-6">
                    <a href="{{ route('company.maintenance-requests.create') }}" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-plus"></i>{{ __('fleet.create_request') }}
                    </a>
                    <a href="{{ route('company.maintenance-centers.index') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-warehouse"></i>{{ __('maintenance.maintenance_centers') }}
                    </a>
                    <a href="{{ route('company.fuel-balance') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-gas-pump"></i>{{ __('fleet.fuel') }}
                    </a>
                    <a href="{{ route('company.inspections.index') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-camera"></i>{{ __('inspections.title') }}
                    </a>
                    <a href="{{ route('company.invoices.index') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-file-invoice"></i>{{ __('company.invoices') }}
                    </a>
                </div>
                <h3 class="dash-section-title text-sm mb-2">{{ __('dashboard.enabled_services') }}</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($enabledServices ?? [] as $s)
                        <span class="dash-service-tag">
                            <span class="text-white font-medium">{{ $s->name }}</span>
                            <span class="text-servx-silver">{{ $s->pivot->base_price ?? $s->base_price }} {{ __('company.sar') }}</span>
                        </span>
                    @empty
                        <p class="text-servx-silver text-sm">{{ __('dashboard.no_services_enabled') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Market Comparison Details Modal --}}
        <div id="marketComparisonModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/60" onclick="document.getElementById('marketComparisonModal').classList.add('hidden')"></div>
                <div class="relative z-10 w-full max-w-lg rounded-2xl bg-slate-800 border border-slate-600/50 p-6 shadow-xl">
                    <h3 class="dash-section-title mb-4">{{ __('company.market_comparison') }}</h3>
                    @if($marketComparison ?? null)
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 rounded-xl bg-servx-inner border border-servx-border">
                                <span class="text-servx-silver">{{ __('company.company_spending') }}</span>
                                <span class="font-bold text-white">{{ number_format($marketComparison['company_total'] ?? 0, 0) }} {{ __('company.sar') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 rounded-xl bg-servx-inner border border-servx-border">
                                <span class="text-servx-silver">{{ __('company.market_avg_spending') }}</span>
                                <span class="font-bold text-white">{{ number_format($marketComparison['market_average'] ?? 0, 0) }} {{ __('company.sar') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 rounded-xl bg-servx-inner border border-servx-border">
                                <span class="text-servx-silver">{{ __('company.percentage_difference') }}</span>
                                <span class="font-bold {{ ($marketComparison['percent_difference'] ?? 0) >= 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ ($marketComparison['percent_difference'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($marketComparison['percent_difference'] ?? 0, 2) }}%</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-servx-silver-light mb-2">{{ __('company.top3_expensive_services') }}</p>
                                <div class="space-y-2">
                                    @forelse($marketComparison['top3_expensive'] ?? [] as $item)
                                        <div class="flex justify-between text-sm p-2 rounded-lg bg-red-500/10 border border-red-500/30">
                                            <span class="text-servx-silver">{{ \App\Enums\MaintenanceType::tryFrom($item['service_type'])?->label() ?? $item['service_type'] }}</span>
                                            <span class="text-red-400 font-semibold">+{{ number_format($item['difference'], 0) }} {{ __('company.sar') }}</span>
                                        </div>
                                    @empty
                                        <p class="text-sm text-servx-silver">{{ __('company.stable_indicator') }}</p>
                                    @endforelse
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-servx-silver-light mb-2">{{ __('company.top3_saving_services') }}</p>
                                <div class="space-y-2">
                                    @forelse($marketComparison['top3_saving'] ?? [] as $item)
                                        <div class="flex justify-between text-sm p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/30">
                                            <span class="text-servx-silver">{{ \App\Enums\MaintenanceType::tryFrom($item['service_type'])?->label() ?? $item['service_type'] }}</span>
                                            <span class="text-emerald-400 font-semibold">{{ number_format($item['difference'], 0) }} {{ __('company.sar') }}</span>
                                        </div>
                                    @empty
                                        <p class="text-sm text-servx-silver">{{ __('company.stable_indicator') }}</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                    <button type="button" onclick="document.getElementById('marketComparisonModal').classList.add('hidden')" class="mt-4 w-full px-4 py-3 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">
                        {{ __('company.close') }}
                    </button>
                </div>
            </div>
        </div>

        <footer class="dash-footer">
            {{ __('company.last_update') }}: {{ now()->format('Y-m-d') }}
        </footer>
    </div>
</div>
@endsection
