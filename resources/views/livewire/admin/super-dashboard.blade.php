<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6 sm:space-y-8">
        {{-- Header + Date Filter + Export --}}
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-start w-full sm:w-auto">
                    <h1 class="dash-page-title">{{ __('admin_dashboard.super_admin_dashboard') }}</h1>
                    <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
                </div>
                <div class="flex flex-wrap gap-2 justify-center sm:justify-end">
                    <a href="{{ route('admin.customers.create') }}" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-plus"></i>{{ __('admin_dashboard.quick_add_company') }}
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-receipt"></i>{{ __('admin_dashboard.quick_view_orders') }}
                    </a>
                    <a href="{{ route('admin.companies.index') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-building"></i>{{ __('admin_dashboard.all_companies') }}
                    </a>
                    <a href="{{ route('admin.vehicles.index') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-car"></i>{{ __('admin_dashboard.all_vehicles') }}
                    </a>
                    @if(($stats['pending_quota_requests'] ?? 0) > 0)
                    <a href="{{ route('admin.quota-requests.index', ['status' => 'pending']) }}" class="dash-btn bg-amber-500/20 text-amber-400 border-amber-500/30 hover:bg-amber-500/30">
                        <i class="fa-solid fa-clipboard-list"></i>{{ __('admin_dashboard.quota_requests') }} ({{ $stats['pending_quota_requests'] }})
                    </a>
                    @endif
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="dash-btn dash-btn-secondary">
                            <i class="fa-solid fa-download"></i>{{ __('admin_dashboard.export_data') ?? 'Export' }}
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute end-0 top-full mt-2 py-2 w-64 rounded-xl bg-slate-800 border border-slate-600 shadow-xl z-10">
                            <div class="px-4 py-1 text-xs text-slate-500 border-b border-slate-700">{{ __('dashboard.orders') }}</div>
                            <a href="{{ route('admin.export.orders') }}?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">CSV</a>
                            <a href="{{ route('admin.export.orders.excel') }}?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">Excel</a>
                            <div class="px-4 py-1 text-xs text-slate-500 border-b border-slate-700">{{ __('admin_dashboard.companies_overview') }}</div>
                            <a href="{{ route('admin.export.companies') }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">CSV</a>
                            <a href="{{ route('admin.export.companies.excel') }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">Excel</a>
                            <div class="px-4 py-1 text-xs text-slate-500 border-b border-slate-700">{{ __('admin_dashboard.vehicles_overview') }}</div>
                            <a href="{{ route('admin.export.vehicles') }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">CSV</a>
                            <a href="{{ route('admin.export.vehicles.excel') }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">Excel</a>
                            <div class="px-4 py-1 text-xs text-slate-500 border-b border-slate-700">{{ __('dashboard.services') }}</div>
                            <a href="{{ route('admin.export.services') }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">CSV</a>
                            <a href="{{ route('admin.export.services.excel') }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">Excel</a>
                            <div class="px-4 py-1 text-xs text-slate-500 border-b border-slate-700">{{ __('dashboard.activity_log') }}</div>
                            <a href="{{ route('admin.export.activities') }}?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">CSV</a>
                            <a href="{{ route('admin.export.activities.excel') }}?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">Excel</a>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Date range filter --}}
            <div class="dash-card flex flex-wrap items-center gap-4">
                <label class="flex items-center gap-2">
                    <span class="text-sm text-slate-400">{{ __('admin_dashboard.date_from') ?? 'From' }}</span>
                    <input type="date" wire:model.live="dateFrom" class="px-3 py-2 rounded-lg bg-slate-800/50 border border-slate-600 text-white text-sm">
                </label>
                <label class="flex items-center gap-2">
                    <span class="text-sm text-slate-400">{{ __('admin_dashboard.date_to') ?? 'To' }}</span>
                    <input type="date" wire:model.live="dateTo" class="px-3 py-2 rounded-lg bg-slate-800/50 border border-slate-600 text-white text-sm">
                </label>
            </div>
        </div>

        {{-- Alerts --}}
        @if(count($alerts ?? []) > 0)
            <div class="dash-card border-amber-500/30 bg-amber-500/5">
                <h2 class="dash-section-title flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
                    {{ __('admin_dashboard.alerts') }} ({{ count($alerts) }})
                </h2>
                <div class="space-y-2">
                    @foreach($alerts as $alert)
                        <a href="{{ $alert['url'] ?? '#' }}" wire:navigate
                            class="flex items-center gap-3 p-3 rounded-xl {{ $alert['severity'] === 'warning' ? 'bg-amber-500/10 hover:bg-amber-500/20' : 'bg-slate-700/50 hover:bg-slate-700' }} transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                {{ $alert['severity'] === 'warning' ? 'bg-amber-500/20 text-amber-400' : 'bg-sky-500/20 text-sky-400' }}">
                                <i class="fa-solid {{ $alert['type'] === 'stuck_order' ? 'fa-clock' : ($alert['type'] === 'low_fleet_utilization' ? 'fa-car' : ($alert['type'] === 'document_expiry' ? 'fa-file-circle-exclamation' : 'fa-building')) }} text-xs"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $alert['title'] }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $alert['description'] }}</p>
                            </div>
                            <i class="fa-solid fa-arrow-left text-sky-400 shrink-0 rtl:rotate-180"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Stats KPI Cards --}}
        <div wire:loading.class="opacity-60 pointer-events-none" class="transition-opacity">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.total_companies') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ $stats['companies'] ?? 0 }}</p>
                    <span class="w-10 h-10 rounded-xl bg-sky-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-building text-sky-400"></i>
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.total_vehicles') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ $stats['vehicles'] ?? 0 }}</p>
                    <span class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-car text-emerald-400"></i>
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.total_orders') ?? 'Total Orders' }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ $stats['orders'] ?? 0 }}</p>
                    <span class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-receipt text-amber-400"></i>
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.orders_growth_rate') ?? 'Orders Growth' }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ $stats['orders_growth_rate'] ?? 0 }}%</p>
                    <span class="dash-trend dash-trend-{{ ($stats['orders_growth_rate'] ?? 0) >= 0 ? 'up' : 'down' }}">
                        <i class="fa-solid fa-caret-{{ ($stats['orders_growth_rate'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.active_companies') ?? 'Active Companies' }}</p>
                <p class="dash-card-value">{{ $stats['active_companies'] ?? 0 }}</p>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.inactive_companies') ?? 'Inactive' }}</p>
                <p class="dash-card-value">{{ $stats['inactive_companies'] ?? 0 }}</p>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.new_customers') }}</p>
                <p class="dash-card-value">{{ $stats['new_customers'] ?? 0 }}</p>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.fleet_utilization') ?? 'Fleet Utilization' }}</p>
                <p class="dash-card-value">{{ $stats['fleet_utilization'] ?? 0 }}%</p>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.vehicle_downtime') ?? 'Vehicle Downtime' }}</p>
                <p class="dash-card-value">{{ $stats['vehicle_downtime'] ?? 0 }}%</p>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.new_customers_monthly') ?? 'New (Period)' }}</p>
                <p class="dash-card-value">{{ $stats['new_customers_monthly'] ?? 0 }}</p>
            </div>
        </div>

        {{-- Order lifecycle metrics --}}
        @if(isset($averageResolutionHours) && $averageResolutionHours !== null)
        <div class="dash-card flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-indigo-400"></i>
                </span>
                <div>
                    <p class="text-sm text-slate-400">{{ __('admin_dashboard.avg_resolution_time') ?? 'Avg. Resolution Time' }}</p>
                    <p class="text-xl font-bold text-white">{{ $averageResolutionHours }} {{ __('admin_dashboard.hours') ?? 'hours' }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Fleet Analytics KPIs --}}
        @if(isset($fleetAnalytics) && !empty($fleetAnalytics))
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.avg_maintenance_per_vehicle') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ number_format($fleetAnalytics['avg_maintenance_per_vehicle'] ?? 0, 0) }}</p>
                    <span class="dash-trend dash-trend-{{ ($fleetAnalytics['maintenance_trend_pct'] ?? 0) <= 0 ? 'up' : 'down' }}" title="{{ ($fleetAnalytics['maintenance_trend_pct'] ?? 0) }}% vs prev">
                        <i class="fa-solid fa-caret-{{ ($fleetAnalytics['maintenance_trend_pct'] ?? 0) <= 0 ? 'down' : 'up' }}"></i>
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.avg_fuel_per_vehicle') }}</p>
                <div class="flex items-center justify-between gap-2">
                    <p class="dash-card-value">{{ number_format($fleetAnalytics['avg_fuel_per_vehicle'] ?? 0, 0) }}</p>
                    <span class="dash-trend dash-trend-{{ ($fleetAnalytics['fuel_trend_pct'] ?? 0) <= 0 ? 'up' : 'down' }}" title="{{ ($fleetAnalytics['fuel_trend_pct'] ?? 0) }}% vs prev">
                        <i class="fa-solid fa-caret-{{ ($fleetAnalytics['fuel_trend_pct'] ?? 0) <= 0 ? 'down' : 'up' }}"></i>
                    </span>
                </div>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.fuel_vs_maintenance_ratio') }}</p>
                <p class="dash-card-value">{{ number_format($fleetAnalytics['fuel_vs_maintenance_ratio'] ?? 0, 2) }}</p>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.cost_per_vehicle_combined') }}</p>
                <p class="dash-card-value">{{ number_format($fleetAnalytics['cost_per_vehicle_combined'] ?? 0, 0) }}</p>
            </div>
            <div class="dash-card dash-card-kpi group">
                <p class="dash-card-title">{{ __('admin_dashboard.monthly_avg_per_vehicle') }}</p>
                <p class="dash-card-value">{{ number_format($fleetAnalytics['monthly_avg_per_vehicle'] ?? 0, 0) }}</p>
            </div>
        </div>
        @endif

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Orders per company (bar) --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('admin_dashboard.orders_per_company') ?? 'Orders per Company' }}</h2>
                <div class="dash-chart-container">
                    <div class="dash-chart-bars flex gap-2 items-end" style="min-height: 120px;">
                        @php $maxOrders = max(1, collect($ordersPerCompany)->max('count') ?? 1); @endphp
                        @foreach($ordersPerCompany as $item)
                            <div class="flex-1 flex flex-col items-center gap-1" title="{{ $item['name'] }}: {{ $item['count'] }}">
                                <div class="w-full bg-slate-700 rounded-t flex items-end" style="height: 100px;">
                                    <div class="w-full bg-sky-500 rounded-t transition-all" style="height: {{ max(4, ($item['count'] / $maxOrders) * 100) }}%"></div>
                                </div>
                                <span class="text-xs text-slate-400 truncate max-w-full">{{ \Illuminate\Support\Str::limit($item['name'], 8) }}</span>
                            </div>
                        @endforeach
                        @if(empty($ordersPerCompany))
                            <p class="text-slate-500 text-sm py-4">{{ __('admin_dashboard.no_data') ?? 'No data' }}</p>
                        @endif
                    </div>
                </div>
            </div>
            {{-- Order status distribution (Pie Chart) --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('admin_dashboard.order_status_distribution') ?? 'Order Status' }}</h2>
                @php
                    $totalStatus = collect($orderStatusDistribution)->sum('count') ?: 1;
                    $pieColors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4', '#EC4899'];
                    $pieSegments = [];
                    $cumulative = 0;
                    foreach ($orderStatusDistribution as $i => $item) {
                        $pct = ($item['count'] / $totalStatus) * 100;
                        $start = $cumulative;
                        $end = $cumulative + $pct;
                        $pieSegments[] = $pieColors[$i % count($pieColors)] . ' ' . round($start, 1) . '% ' . round($end, 1) . '%';
                        $cumulative = $end;
                    }
                    $conicGradient = 'conic-gradient(' . implode(', ', $pieSegments) . ')';
                @endphp
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    @if(!empty($orderStatusDistribution))
                        <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full flex-shrink-0" style="background: {{ $conicGradient }};"></div>
                        <div class="flex-1 space-y-1.5 min-w-0">
                            @foreach($orderStatusDistribution as $i => $item)
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background: {{ $pieColors[$i % count($pieColors)] }};"></span>
                                    <span class="text-sm text-slate-300 truncate">{{ $item['status'] }}</span>
                                    <span class="text-sm font-bold text-white ms-auto">{{ $item['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-sm py-4">{{ __('admin_dashboard.no_data') ?? 'No data' }}</p>
                    @endif
                </div>
            </div>
            {{-- Orders per vehicle --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('admin_dashboard.orders_per_vehicle') ?? 'Orders per Vehicle' }}</h2>
                <div class="space-y-2">
                    @forelse($ordersPerVehicle ?? [] as $item)
                        <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-700/30">
                            <span class="text-sm text-slate-300 truncate min-w-0">{{ $item['name'] }}</span>
                            <span class="text-xs text-slate-500 shrink-0">{{ $item['company'] }}</span>
                            <span class="font-bold text-white shrink-0">{{ $item['count'] }}</span>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm py-4">{{ __('admin_dashboard.no_data') ?? 'No data' }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Monthly trend: Line Chart (responsive) --}}
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('admin_dashboard.monthly_orders_trend') ?? 'Monthly Orders Trend' }}</h2>
            <div class="dash-chart-container">
                @php $maxMonthly = max(1, collect($monthlyOrders)->max('count') ?? 1); @endphp
                @if(!empty($monthlyOrders))
                <div class="relative h-36">
                    <svg viewBox="0 0 400 120" class="w-full h-full" preserveAspectRatio="xMidYMid meet">
                        @php
                            $points = [];
                            foreach ($monthlyOrders as $i => $m) {
                                $x = count($monthlyOrders) > 1 ? 20 + ($i / (count($monthlyOrders) - 1)) * 360 : 200;
                                $y = 100 - (($m['count'] / $maxMonthly) * 85);
                                $points[] = "{$x},{$y}";
                            }
                        @endphp
                        <polyline fill="none" stroke="rgb(16, 185, 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="{{ implode(' ', $points) }}" />
                        @foreach($monthlyOrders as $i => $m)
                            @php
                                $x = count($monthlyOrders) > 1 ? 20 + ($i / (count($monthlyOrders) - 1)) * 360 : 200;
                                $y = 100 - (($m['count'] / $maxMonthly) * 85);
                            @endphp
                            <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="rgb(16, 185, 129)" />
                        @endforeach
                    </svg>
                    <div class="flex justify-between mt-2 text-[10px] text-slate-400 gap-1">
                        @foreach($monthlyOrders as $m)
                            <span class="flex-1 text-center truncate" title="{{ $m['label'] }}: {{ $m['count'] }}">{{ $m['label'] }}</span>
                        @endforeach
                    </div>
                </div>
                @else
                    <p class="text-slate-500 text-sm py-4 w-full text-center">{{ __('admin_dashboard.no_data') }}</p>
                @endif
            </div>
        </div>

        {{-- Orders per Vehicle over time (Trend Analysis) --}}
        @if(!empty($ordersPerVehicleOverTime ?? []))
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('admin_dashboard.orders_per_vehicle_trend') ?? 'Orders per Vehicle (Trend)' }}</h2>
            <div class="dash-chart-container">
                @php
                    $maxRatio = max(0.01, collect($ordersPerVehicleOverTime)->max('ratio') ?? 1);
                @endphp
                <div class="flex gap-2 items-end" style="min-height: 80px;">
                    @foreach($ordersPerVehicleOverTime as $i => $pt)
                        <div class="flex-1 flex flex-col items-center" title="{{ $pt['label'] }}: {{ $pt['ratio'] }} {{ __('admin_dashboard.orders_per_vehicle_unit') ?? 'orders/vehicle' }}">
                            <div class="w-full bg-slate-700 rounded-t flex items-end" style="height: 60px;">
                                <div class="w-full bg-indigo-500 rounded-t transition-all" style="height: {{ max(4, ($pt['ratio'] / $maxRatio) * 100) }}%"></div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1">{{ $pt['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Fleet Analytics Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            {{-- Monthly Maintenance Average Trend --}}
            @if(!empty($monthlyMaintenanceTrend ?? []))
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('reports.monthly_maintenance_trend') }}</h2>
                <div class="dash-chart-container">
                    @php $maxMaint = max(1, collect($monthlyMaintenanceTrend)->max('avg') ?? 1); @endphp
                    <div class="flex gap-2 items-end" style="min-height: 80px;">
                        @foreach($monthlyMaintenanceTrend as $m)
                            <div class="flex-1 flex flex-col items-center" title="{{ $m['label'] }}: {{ number_format($m['avg'], 0) }} {{ __('company.sar') }}">
                                <div class="w-full bg-slate-700 rounded-t flex items-end" style="height: 60px;">
                                    <div class="w-full bg-emerald-500 rounded-t transition-all" style="height: {{ max(4, ($m['avg'] / $maxMaint) * 100) }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1">{{ $m['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Monthly Fuel Average Trend --}}
            @if(!empty($monthlyFuelTrend ?? []))
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('reports.monthly_fuel_trend') }}</h2>
                <div class="dash-chart-container">
                    @php $maxFuel = max(1, collect($monthlyFuelTrend)->max('avg') ?? 1); @endphp
                    <div class="flex gap-2 items-end" style="min-height: 80px;">
                        @foreach($monthlyFuelTrend as $m)
                            <div class="flex-1 flex flex-col items-center" title="{{ $m['label'] }}: {{ number_format($m['avg'], 0) }} {{ __('company.sar') }}">
                                <div class="w-full bg-slate-700 rounded-t flex items-end" style="height: 60px;">
                                    <div class="w-full bg-amber-500 rounded-t transition-all" style="height: {{ max(4, ($m['avg'] / $maxFuel) * 100) }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1">{{ $m['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            {{-- Top 5 Vehicles by Operating Cost --}}
            @if(!empty($topVehiclesByCost ?? []))
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('reports.top_vehicles_by_cost') }}</h2>
                <div class="space-y-2">
                    @foreach($topVehiclesByCost as $item)
                        <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-slate-700/30">
                            <span class="text-sm text-slate-300 truncate min-w-0">{{ $item['vehicle']->display_name ?? $item['vehicle']->plate_number }}</span>
                            <span class="text-xs text-slate-500 shrink-0">{{ $item['vehicle']->company?->company_name ?? '-' }}</span>
                            <span class="font-bold text-white shrink-0">{{ number_format($item['total'], 0) }} {{ __('company.sar') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Maintenance vs Fuel Distribution --}}
            @if(!empty($maintenanceVsFuel ?? []) && (($maintenanceVsFuel[0]['value'] ?? 0) + ($maintenanceVsFuel[1]['value'] ?? 0)) > 0)
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('reports.maintenance_vs_fuel') }}</h2>
                @php
                    $maintPct = $maintenanceVsFuel[0]['percent'] ?? 0;
                    $fuelPct = $maintenanceVsFuel[1]['percent'] ?? 0;
                    $conic = 'conic-gradient(#10B981 ' . $maintPct . '%, #F59E0B ' . $maintPct . '% ' . ($maintPct + $fuelPct) . '%)';
                @endphp
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full flex-shrink-0" style="background: {{ $conic }};"></div>
                    <div class="flex-1 space-y-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-sm text-slate-300">{{ $maintenanceVsFuel[0]['label'] ?? 'Maintenance' }}</span>
                            <span class="text-sm font-bold text-white ms-auto">{{ $maintPct }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="text-sm text-slate-300">{{ $maintenanceVsFuel[1]['label'] ?? 'Fuel' }}</span>
                            <span class="text-sm font-bold text-white ms-auto">{{ $fuelPct }}%</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Companies + Activity --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
            {{-- Companies List --}}
            <div class="xl:col-span-2 dash-card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="dash-section-title">{{ __('admin_dashboard.companies_overview') }}</h2>
                    <a href="{{ route('admin.companies.index') }}" class="dash-link">{{ __('common.view_all') }}</a>
                </div>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($companies as $company)
                        <a href="{{ route('admin.companies.show', $company) }}" class="dash-order-row flex items-center justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-white truncate">{{ $company->company_name }}</p>
                                <p class="text-xs text-slate-400 truncate">
                                    {{ __('admin_dashboard.vehicles_count') }}: {{ $company->vehicles_count ?? 0 }}
                                    · {{ __('admin_dashboard.drivers_count') }}: {{ $company->drivers_count ?? 0 }}
                                    · {{ __('admin_dashboard.orders_count') }}: {{ $company->orders_count ?? 0 }}
                                </p>
                            </div>
                            <span class="text-sky-400 text-sm shrink-0">
                                <i class="fa-solid fa-arrow-left ms-1 rtl:rotate-180"></i>
                            </span>
                        </a>
                    @empty
                        <p class="text-slate-500 text-sm py-6 text-center">{{ __('admin_dashboard.no_companies') }}</p>
                    @endforelse
                </div>
                @if($companies->hasPages())
                    <div class="mt-4 pt-4 border-t border-slate-700">
                        {{ $companies->links() }}
                    </div>
                @endif
            </div>

            {{-- Recent Activity --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('admin_dashboard.recent_activity') }}</h2>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($recentActivity as $activity)
                        @php $hasUrl = !empty($activity['url']); @endphp
                        <{{ $hasUrl ? 'a' : 'div' }} href="{{ $hasUrl ? $activity['url'] : '#' }}" {{ $hasUrl ? 'wire:navigate' : '' }} class="dash-activity-item flex gap-3 p-2 rounded-lg hover:bg-slate-800/50 transition-colors {{ $hasUrl ? 'cursor-pointer' : '' }}">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                @if($activity['type'] === 'company_added') bg-sky-500/20 text-sky-400
                                @elseif($activity['type'] === 'vehicle_added') bg-emerald-500/20 text-emerald-400
                                @elseif($activity['type'] === 'order_created') bg-amber-500/20 text-amber-400
                                @elseif($activity['type'] === 'invoice_uploaded') bg-indigo-500/20 text-indigo-400
                                @else bg-slate-500/20 text-slate-400 @endif">
                                @if($activity['type'] === 'company_added') <i class="fa-solid fa-building text-xs"></i>
                                @elseif($activity['type'] === 'vehicle_added') <i class="fa-solid fa-car text-xs"></i>
                                @elseif($activity['type'] === 'order_created') <i class="fa-solid fa-receipt text-xs"></i>
                                @elseif($activity['type'] === 'invoice_uploaded') <i class="fa-solid fa-file-invoice text-xs"></i>
                                @else <i class="fa-solid fa-circle-info text-xs"></i> @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $activity['title'] }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $activity['description'] }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                            @if($hasUrl)
                                <span class="text-sky-400 shrink-0">
                                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>
                                </span>
                            @endif
                        </{{ $hasUrl ? 'a' : 'div' }}>
                    @empty
                        <p class="text-slate-500 text-sm py-6 text-center">{{ __('admin_dashboard.no_activity') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
        </div>

        {{-- System Health --}}
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('admin_dashboard.system_health') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @if(isset($systemHealth['queue_pending']) && $systemHealth['queue_pending'] !== null)
                    <div class="p-3 rounded-xl bg-slate-700/50">
                        <p class="text-xs text-slate-400">{{ __('admin_dashboard.queue_pending') }}</p>
                        <p class="text-lg font-bold text-white">{{ $systemHealth['queue_pending'] }}</p>
                    </div>
                @endif
                @if(isset($systemHealth['queue_failed']) && $systemHealth['queue_failed'] !== null)
                    <div class="p-3 rounded-xl {{ ($systemHealth['queue_failed'] ?? 0) > 0 ? 'bg-rose-500/20' : 'bg-slate-700/50' }}">
                        <p class="text-xs text-slate-400">{{ __('admin_dashboard.queue_failed') }}</p>
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-lg font-bold {{ ($systemHealth['queue_failed'] ?? 0) > 0 ? 'text-rose-400' : 'text-white' }}">{{ $systemHealth['queue_failed'] ?? 0 }}</p>
                            @if(($systemHealth['queue_failed'] ?? 0) > 0)
                                <button type="button" wire:click="retryFailedJobs" wire:loading.attr="disabled"
                                        class="px-2 py-1 rounded-lg bg-rose-500/30 hover:bg-rose-500/50 text-rose-300 text-xs font-bold disabled:opacity-70">
                                    <i class="fa-solid fa-rotate-right me-1"></i>{{ __('admin_dashboard.retry_failed_jobs') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
                @if(isset($systemHealth['storage_mb']) && $systemHealth['storage_mb'] !== null)
                    <div class="p-3 rounded-xl bg-slate-700/50">
                        <p class="text-xs text-slate-400">{{ __('admin_dashboard.storage_usage') }}</p>
                        <p class="text-lg font-bold text-white">{{ $systemHealth['storage_mb'] }} MB</p>
                    </div>
                @endif
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" wire:click="clearCache" wire:loading.attr="disabled"
                        class="px-3 py-2 rounded-xl bg-slate-700/50 hover:bg-slate-700 text-slate-300 text-sm font-semibold disabled:opacity-70">
                    <i class="fa-solid fa-broom me-1"></i>{{ __('admin_dashboard.clear_cache') }}
                </button>
            </div>
            @if(empty(array_filter($systemHealth ?? [])))
                <p class="text-slate-500 text-sm py-4">{{ __('admin_dashboard.no_data') }}</p>
            @endif
        </div>

        <footer class="dash-footer">
            {{ __('company.last_update') }}: {{ now()->format('Y-m-d H:i') }}
        </footer>
    </div>
</div>
