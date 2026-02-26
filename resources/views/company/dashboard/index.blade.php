@extends('admin.layouts.app')

@section('title', __('company.dashboard_title') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('company.dashboard_title'))

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6 sm:space-y-8">
        {{-- 1. Header with blue accent underline --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-start w-full sm:w-auto">
                <h1 class="dash-page-title">{{ __('dashboard.data_board') }}</h1>
                <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
            </div>
            <div class="flex flex-wrap gap-2 justify-center sm:justify-end">
                <a href="{{ route('company.orders.create') }}" class="dash-btn dash-btn-primary">
                    <i class="fa-solid fa-plus"></i>{{ __('company.orders') }}
                </a>
                <a href="{{ route('company.vehicles.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-car"></i>{{ __('company.vehicles') }}
                </a>
                <a href="{{ route('company.fuel.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-gas-pump"></i>{{ __('company.fuel_report') }}
                </a>
                <a href="{{ route('company.invoices.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-file-invoice"></i>{{ __('company.invoices') }}
                </a>
                <a href="{{ route('company.inspections.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-camera"></i>{{ __('inspections.title') }}
                </a>
            </div>
        </div>

        {{-- Document expiry alerts --}}
        @if(($expiringDocumentsCount ?? 0) > 0)
            <div class="dash-card border-amber-500/40 bg-amber-500/5">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-file-circle-exclamation text-amber-400"></i>
                    {{ __('vehicles.expiring_documents') }} ({{ $expiringDocumentsCount }})
                </h2>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @foreach($expiringDocuments ?? [] as $item)
                        <a href="{{ route('company.vehicles.show', $item->vehicle) }}" class="flex items-center gap-3 p-3 rounded-xl bg-servx-inner hover:bg-servx-inner-hover border border-servx-border transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $item->status === 'expired' ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/20 text-amber-400' }}">
                                <i class="fa-solid fa-car text-xs"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $item->vehicle->plate_number ?? $item->vehicle->display_name }} — {{ $item->type === \App\Services\ExpiryMonitoringService::DOC_REGISTRATION ? __('vehicles.registration') : __('vehicles.insurance') }}</p>
                                <p class="text-xs text-servx-silver">{{ $item->date?->translatedFormat('d M Y') }} · {{ $item->days_remaining !== null ? ($item->days_remaining < 0 ? abs($item->days_remaining) . ' ' . __('vehicles.days_ago') : $item->days_remaining . ' ' . __('vehicles.days_remaining')) : '-' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-bold border {{ $expiryService->getStatusBadgeClass($item->status) }}">{{ __('vehicles.' . $item->status) }}</span>
                            <i class="fa-solid fa-arrow-left text-sky-400 shrink-0 rtl:rotate-180"></i>
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('company.vehicles.index') }}" class="inline-block mt-3 text-sm text-sky-400 hover:text-sky-300 font-bold">{{ __('common.view_all') }}</a>
            </div>
        @endif

        {{-- Vehicles pending inspection --}}
        @if(($inspectionPendingCount ?? 0) > 0)
            <div class="dash-card {{ ($inspectionOverdueCount ?? 0) > 0 ? 'border-red-500/40 bg-red-500/5' : 'border-amber-500/40 bg-amber-500/5' }}">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-camera text-amber-400"></i>
                    {{ __('inspections.vehicles_pending') }}
                    @if(($inspectionOverdueCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-500/30 text-red-300 border border-red-400/50">{{ $inspectionOverdueCount }} {{ __('inspections.overdue') }}</span>
                    @endif
                </h2>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @foreach($inspectionPendingVehicles ?? [] as $item)
                        <a href="{{ route('company.vehicles.show', $item->vehicle) }}" class="flex items-center gap-3 p-3 rounded-xl bg-servx-inner hover:bg-servx-inner-hover border border-servx-border transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $item->status === 'overdue' ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/20 text-amber-400' }}">
                                <i class="fa-solid fa-car text-xs"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $item->vehicle->plate_number ?? $item->vehicle->display_name }}</p>
                                <p class="text-xs text-servx-silver">{{ __('inspections.due_date') }}: {{ $item->due_date?->translatedFormat('d M Y') ?? '—' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-bold border {{ $item->status === 'overdue' ? 'border-red-400/50 text-red-300 bg-red-500/20' : 'border-amber-400/50 text-amber-300 bg-amber-500/20' }}">{{ __('inspections.' . $item->status) }}</span>
                            <i class="fa-solid fa-arrow-left text-sky-400 shrink-0 rtl:rotate-180"></i>
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('company.inspections.index') }}" class="inline-block mt-3 text-sm text-sky-400 hover:text-sky-300 font-bold">{{ __('inspections.view_all') }}</a>
            </div>
        @endif

        {{-- Announcements --}}
        @if(count($announcements ?? []) > 0)
            <div class="dash-card border-sky-500/40">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-sky-400"></i>
                    {{ __('admin_dashboard.announcements') }}
                </h2>
                <div class="space-y-3 max-h-32 overflow-y-auto">
                    @foreach($announcements as $ann)
                        <div class="p-3 rounded-xl bg-servx-inner border border-servx-border">
                            <p class="font-bold text-white">{{ $ann->title }}</p>
                            <p class="text-sm text-servx-silver mt-1 line-clamp-2">{{ Str::limit(strip_tags($ann->body), 120) }}</p>
                            <p class="text-xs text-servx-silver mt-1">{{ $ann->published_at?->format('Y-m-d H:i') ?? $ann->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 2. Top row: 3 KPI cards with trend indicators (reference layout) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('company.vehicles_count') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ $vehiclesCount ?? 0 }}</p>
                    <span class="dash-trend dash-trend-up" title="{{ __('company.above_normal') }}">
                        <i class="fa-solid fa-caret-up"></i>
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('company.total_maintenance_cost') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ number_format($maintenanceSummary['total'] ?? 0, 0) }} {{ __('company.sar') }}</p>
                    <span class="dash-trend dash-trend-{{ $maintenanceTrend }}" title="{{ $maintenanceUI['text'] }}">
                        @if($maintenanceTrend === 'up')
                            <i class="fa-solid fa-caret-up"></i>
                        @elseif($maintenanceTrend === 'down')
                            <i class="fa-solid fa-caret-down"></i>
                        @else
                            <i class="fa-solid fa-minus"></i>
                        @endif
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('company.total_fuel_cost') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ number_format($fuelSummary['total'] ?? 0, 0) }} {{ __('company.sar') }}</p>
                    <span class="dash-trend dash-trend-{{ $fuelTrend }}" title="{{ $fuelUI['text'] }}">
                        @if($fuelTrend === 'up')
                            <i class="fa-solid fa-caret-up"></i>
                        @elseif($fuelTrend === 'down')
                            <i class="fa-solid fa-caret-down"></i>
                        @else
                            <i class="fa-solid fa-minus"></i>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- 3. Bottom row: 4 sections (Total Cost, Seven Months, Top 5, Fleet Indicators) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
            {{-- Total Cost card --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.total_cost') }}</h2>
                <p class="dash-card-value dash-card-value-lg mb-4">{{ number_format($totalCost ?? 0, 0) }}</p>
                <div class="flex flex-wrap gap-3">
                    <div class="dash-stat-mini">
                        <p class="dash-stat-mini-label">{{ __('dashboard.day') }}</p>
                        <p class="dash-stat-mini-value">{{ $dailyCost ?? 0 }}</p>
                    </div>
                    <div class="dash-stat-mini">
                        <p class="dash-stat-mini-label">{{ __('dashboard.month') }}</p>
                        <p class="dash-stat-mini-value">{{ $monthlyCost ?? 0 }}</p>
                    </div>
                </div>
            </div>

            {{-- Seven Months Comparison --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('dashboard.six_month_comparison') }}</h2>
                <div class="dash-chart-container">
                    <div class="dash-chart-bars" role="img" aria-label="{{ __('dashboard.six_month_comparison') }}">
                        @php
                            $sevenMonths = $lastSevenMonths ?? [];
                            $maxCost = max(1, (float) collect($sevenMonths)->max('total_cost'));
                        @endphp
                        @foreach($sevenMonths as $m)
                            <div class="dash-chart-bar-wrap" title="{{ $m['year'] }}-{{ str_pad($m['month'], 2, '0', STR_PAD_LEFT) }}: {{ number_format($m['total_cost'], 0) }} {{ __('company.sar') }}">
                                <div class="dash-chart-bar" style="height: {{ max(8, ($m['total_cost'] / $maxCost) * 100) }}%"></div>
                            </div>
                        @endforeach
                        @if(empty($sevenMonths))
                            @for($i = 0; $i < 7; $i++)
                                <div class="dash-chart-bar-wrap"><div class="dash-chart-bar dash-chart-bar-empty" style="height: 20%"></div></div>
                            @endfor
                        @endif
                    </div>
                    <div class="dash-chart-trend">
                        <span class="dash-trend dash-trend-{{ ($sevenMonthPercent ?? 0) >= 0 ? 'up' : 'down' }} dash-trend-lg">
                            @if(($sevenMonthPercent ?? 0) >= 0)
                                <i class="fa-solid fa-caret-up"></i>
                            @else
                                <i class="fa-solid fa-caret-down"></i>
                            @endif
                        </span>
                        <span class="text-sm text-servx-silver">{{ number_format(abs($sevenMonthPercent ?? 0), 1) }}%</span>
                    </div>
                </div>
            </div>

            {{-- Top 5 Vehicles --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.top_5_vehicles') }}</h2>
                @php $top5 = ($topVehicles ?? collect())->take(5); @endphp
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 sm:gap-3">
                    @for($i = 0; $i < 5; $i++)
                        @if(isset($top5[$i]))
                            @php $v = $top5[$i]; @endphp
                            <a href="{{ route('company.vehicles.show', $v->id) }}" class="dash-vehicle-card block">
                                <p class="text-xs text-servx-silver truncate">{{ $v->make ?? '' }} {{ $v->model ?? '' }}</p>
                                <p class="text-sm font-bold text-white truncate">{{ number_format($v->total_cost ?? $v->total_service_cost ?? 0, 0) }}</p>
                            </a>
                        @else
                            <div class="dash-vehicle-card dash-vehicle-card-empty">
                                <span class="text-servx-silver text-sm">—</span>
                            </div>
                        @endif
                    @endfor
                </div>
            </div>

            {{-- Fleet Indicators --}}
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('company.fleet_indicators') }}</h2>
                <div class="space-y-4">
                    <label class="dash-indicator-row">
                        <span class="dash-indicator-check {{ str_contains($maintenanceUI['textClass'] ?? '', 'green') ? 'dash-indicator-check-active' : '' }}"></span>
                        <span class="text-sm {{ $maintenanceUI['textClass'] ?? 'text-servx-silver' }}">{{ $maintenanceUI['text'] ?? __('company.stable_indicator') }}</span>
                    </label>
                    <label class="dash-indicator-row">
                        <span class="dash-indicator-check {{ str_contains($fuelUI['textClass'] ?? '', 'green') ? 'dash-indicator-check-active' : '' }}"></span>
                        <span class="text-sm {{ $fuelUI['textClass'] ?? 'text-servx-silver' }}">{{ $fuelUI['text'] ?? __('company.stable_indicator') }}</span>
                    </label>
                    <label class="dash-indicator-row">
                        <span class="dash-indicator-check {{ str_contains($operatingUI['textClass'] ?? '', 'green') ? 'dash-indicator-check-active' : '' }}"></span>
                        <span class="text-sm {{ $operatingUI['textClass'] ?? 'text-servx-silver' }}">{{ $operatingUI['text'] ?? __('company.stable_indicator') }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- 4. Secondary: Order KPIs + Latest Orders --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="dash-card dash-card-compact">
                <p class="dash-card-title">{{ __('dashboard.today_orders') }}</p>
                <p class="dash-card-value">{{ $todayOrders ?? 0 }}</p>
            </div>
            <div class="dash-card dash-card-compact">
                <p class="dash-card-title">{{ __('dashboard.in_progress') }}</p>
                <p class="dash-card-value">{{ $inProgress ?? 0 }}</p>
            </div>
            <div class="dash-card dash-card-compact">
                <p class="dash-card-title">{{ __('dashboard.completed') }}</p>
                <p class="dash-card-value">{{ $completed ?? 0 }}</p>
            </div>
            <div class="dash-card dash-card-compact">
                <p class="dash-card-title">{{ __('company.recent_invoices') }}</p>
                <p class="dash-card-value text-base">{{ count($recentInvoices ?? []) }}</p>
            </div>
        </div>

        {{-- 5. Latest Orders (full width) --}}
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
                        <span class="text-sky-400 text-sm shrink-0"><i class="fa-solid fa-arrow-left ms-1"></i></span>
                    </a>
                @empty
                    <p class="text-servx-silver text-sm py-6 text-center">{{ __('orders.no_orders') }}</p>
                @endforelse
            </div>
        </div>

        {{-- 6. Recent Invoices + Enabled Services --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="dash-card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="dash-section-title">{{ __('company.recent_invoices') }}</h2>
                    <a href="{{ route('company.invoices.index') }}" class="dash-link">{{ __('common.view_all') }}</a>
                </div>
                @if(count($recentInvoices ?? []) > 0)
                    <div class="space-y-2 max-h-36 overflow-y-auto">
                        @foreach($recentInvoices->take(5) as $inv)
                            <a href="{{ route('company.invoices.show', $inv) }}" class="dash-invoice-row">
                                <span class="text-white font-medium">{{ $inv->invoice_number ?? '#' . $inv->id }}</span>
                                <span class="text-servx-silver-light font-semibold">{{ number_format((float)($inv->total ?? 0), 2) }} {{ __('company.sar') }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-servx-silver text-sm py-6">{{ __('company.no_invoices_yet') }}</p>
                @endif
            </div>
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('dashboard.enabled_services') }}</h2>
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

        <footer class="dash-footer">
            {{ __('company.last_update') }}: {{ now()->format('Y-m-d') }}
        </footer>
    </div>
</div>
@endsection
