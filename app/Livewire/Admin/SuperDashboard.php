<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\Order;
use App\Models\Vehicle;
use App\Models\Invoice;
use App\Services\AnalyticsService;
use App\Services\ExpiryMonitoringService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SuperDashboard extends Component
{
    use WithPagination;

    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->subMonths(6)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getStatsProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_super_dashboard_stats_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        $ttl = 120;

        return Cache::remember($cacheKey, $ttl, function () {
            $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
            $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfDay() : now()->endOfDay();

            $companiesCount = Company::count();
            $vehiclesCount = Vehicle::count();
            $driversCount = (int) Vehicle::whereNotNull('driver_phone')
                ->selectRaw('COUNT(DISTINCT driver_phone) as cnt')
                ->value('cnt') ?? 0;
            $newCustomersCount = Company::where('created_at', '>=', now()->subDays(30))->count();
            $newCustomersMonthly = Company::whereBetween('created_at', [$from, $to])->count();
            $ordersCount = Order::count();
            $activeCompanies = Company::where('status', 'active')->count();
            $inactiveCompanies = $companiesCount - $activeCompanies;

            $ordersInRange = Order::whereBetween('created_at', [$from, $to])->count();
            $prevFrom = $from->copy()->subMonths(6)->startOfDay();
            $prevTo = $from->copy()->subDay()->endOfDay();
            $ordersPrevRange = Order::whereBetween('created_at', [$prevFrom, $prevTo])->count();
            $growthRate = $ordersPrevRange > 0
                ? round((($ordersInRange - $ordersPrevRange) / $ordersPrevRange) * 100, 1)
                : ($ordersInRange > 0 ? 100 : 0);

            // Fleet utilization: vehicles with at least one order / total vehicles
            $vehiclesWithOrders = Vehicle::whereHas('orders')->count();
            $fleetUtilization = $vehiclesCount > 0
                ? round(($vehiclesWithOrders / $vehiclesCount) * 100, 1)
                : 0;

            // Vehicle downtime: vehicles with NO orders in date range / total vehicles (underutilized)
            $vehiclesWithOrdersInRange = Vehicle::whereHas('orders', fn ($q) => $q->whereBetween('created_at', [$from, $to]))->count();
            $vehicleDowntime = $vehiclesCount > 0
                ? round((($vehiclesCount - $vehiclesWithOrdersInRange) / $vehiclesCount) * 100, 1)
                : 0;

            $pendingQuotaRequests = \App\Models\VehicleQuotaRequest::where('status', \App\Models\VehicleQuotaRequest::STATUS_PENDING)->count();

            return [
                'companies' => $companiesCount,
                'vehicles' => $vehiclesCount,
                'pending_quota_requests' => $pendingQuotaRequests,
                'drivers' => $driversCount,
                'new_customers' => $newCustomersCount,
                'active_subscriptions' => 0,
                'orders' => $ordersCount,
                'orders_in_range' => $ordersInRange,
                'orders_growth_rate' => $growthRate,
                'active_companies' => $activeCompanies,
                'inactive_companies' => $inactiveCompanies,
                'fleet_utilization' => $fleetUtilization,
                'vehicle_downtime' => $vehicleDowntime,
                'new_customers_monthly' => $newCustomersMonthly,
            ];
        });
    }

    public function getOrdersPerCompanyProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_orders_per_company_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        return Cache::remember($cacheKey, 120, function () {
            return Company::query()
                ->withCount('orders')
                ->having('orders_count', '>', 0)
                ->orderByDesc('orders_count')
                ->limit(10)
                ->get(['id', 'company_name'])
                ->map(fn ($c) => ['name' => $c->company_name, 'count' => $c->orders_count])
                ->toArray();
        });
    }

    public function getMonthlyOrdersProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_monthly_orders_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        return Cache::remember($cacheKey, 120, function () {
            $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfMonth() : now()->subMonths(6)->startOfMonth();
            $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfMonth() : now()->endOfMonth();

            $rows = Order::query()
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
                ->selectRaw('COUNT(*) as count')
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('year, month')
                ->get()
                ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

            $out = [];
            $current = $from->copy();
            while ($current <= $to) {
                $key = "{$current->year}-{$current->month}";
                $row = $rows[$key] ?? null;
                $out[] = [
                    'label' => $current->translatedFormat('M Y'),
                    'count' => $row ? (int) $row->count : 0,
                ];
                $current->addMonth();
            }
            return $out;
        });
    }

    /** Average resolution time in hours (created → completed) for orders in date range */
    public function getAverageResolutionHoursProperty(): ?float
    {
        $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
        $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfDay() : now()->endOfDay();

        $row = Order::query()
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$from, $to])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours');

        return $row !== null ? round((float) $row, 1) : null;
    }

    /** Orders per vehicle (top vehicles by order count) */
    public function getOrdersPerVehicleProperty(): array
    {
        $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfDay() : null;
        $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfDay() : null;

        $query = Vehicle::query()
            ->withCount(['orders' => fn ($q) => $q->when($from, fn ($qq) => $qq->where('created_at', '>=', $from))->when($to, fn ($qq) => $qq->where('created_at', '<=', $to))])
            ->having('orders_count', '>', 0)
            ->orderByDesc('orders_count')
            ->limit(5)
            ->with('company:id,company_name');

        return $query->get()->map(fn ($v) => [
            'name' => ($v->make . ' ' . $v->model . ' - ' . $v->plate_number) ?: $v->plate_number,
            'company' => $v->company?->company_name ?? '-',
            'count' => $v->orders_count,
        ])->toArray();
    }

    /** Orders per vehicle over time: for each month, orders_count / vehicles_at_month_end */
    public function getOrdersPerVehicleOverTimeProperty(): array
    {
        $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfMonth() : now()->subMonths(6)->startOfMonth();
        $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfMonth() : now()->endOfMonth();

        $orderRows = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
            ->selectRaw('COUNT(*) as count')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('year, month')
            ->get()
            ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

        $out = [];
        $current = $from->copy();
        while ($current <= $to) {
            $key = "{$current->year}-{$current->month}";
            $row = $orderRows[$key] ?? null;
            $orderCount = $row ? (int) $row->count : 0;
            $vehiclesAtMonthEnd = Vehicle::where('created_at', '<=', $current->copy()->endOfMonth())->count();
            $ratio = $vehiclesAtMonthEnd > 0 ? round($orderCount / $vehiclesAtMonthEnd, 2) : 0;
            $out[] = [
                'label' => $current->translatedFormat('M Y'),
                'orders' => $orderCount,
                'vehicles' => $vehiclesAtMonthEnd,
                'ratio' => $ratio,
            ];
            $current->addMonth();
        }
        return $out;
    }

    /** Fleet analytics: maintenance & fuel averages (cached 120s) */
    public function getFleetAnalyticsProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_fleet_analytics_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        return Cache::remember($cacheKey, 120, function () {
            $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
            $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfDay() : now()->endOfDay();
            return app(AnalyticsService::class)->getAdminDashboardAnalytics($from, $to, null);
        });
    }

    public function getMonthlyMaintenanceTrendProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_monthly_maint_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        return Cache::remember($cacheKey, 120, function () {
            $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfMonth() : now()->subMonths(6)->startOfMonth();
            $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfMonth() : now()->endOfMonth();
            return app(AnalyticsService::class)->getMonthlyMaintenanceTrend($from, $to, null);
        });
    }

    public function getMonthlyFuelTrendProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_monthly_fuel_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        return Cache::remember($cacheKey, 120, function () {
            $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfMonth() : now()->subMonths(6)->startOfMonth();
            $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfMonth() : now()->endOfMonth();
            return app(AnalyticsService::class)->getMonthlyFuelTrend($from, $to, null);
        });
    }

    public function getTopVehiclesByCostProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_top_vehicles_cost_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        return Cache::remember($cacheKey, 120, function () {
            $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
            $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfDay() : now()->endOfDay();
            return app(AnalyticsService::class)->getTopVehiclesByOperatingCost($from, $to, null, 5);
        });
    }

    public function getMaintenanceVsFuelProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_maint_vs_fuel_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        return Cache::remember($cacheKey, 120, function () {
            $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
            $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfDay() : now()->endOfDay();
            return app(AnalyticsService::class)->getMaintenanceVsFuelDistribution($from, $to, null);
        });
    }

    public function getOrderStatusDistributionProperty(): array
    {
        $version = Cache::get('admin_stats_version', 1);
        $cacheKey = 'admin_order_status_dist_' . $this->dateFrom . '_' . $this->dateTo . '_v' . $version;
        $ttl = 120;

        return Cache::remember($cacheKey, $ttl, function () {
            $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfDay() : null;
            $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfDay() : null;

            $query = Order::query()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status');

            if ($from) $query->where('created_at', '>=', $from);
            if ($to) $query->where('created_at', '<=', $to);

            return $query->get()->map(fn ($r) => ['status' => $r->status, 'count' => (int) $r->count])->toArray();
        });
    }

    public function getCompaniesProperty()
    {
        return Company::query()
            ->withCount(['vehicles', 'orders'])
            ->selectRaw('(SELECT COALESCE(COUNT(DISTINCT driver_phone), 0) FROM vehicles WHERE vehicles.company_id = companies.id AND driver_phone IS NOT NULL) as drivers_count')
            ->latest()
            ->paginate(8, ['id', 'company_name', 'status', 'created_at']);
    }

    public function getAlertsProperty(): array
    {
        $alerts = [];

        // Stuck orders: pending_approval or in_progress for 7+ days
        $stuckDays = (int) config('servx.stuck_order_days', 7);
        $stuckOrders = Order::whereIn('status', ['pending_approval', 'in_progress', 'pending_confirmation'])
            ->where('created_at', '<=', now()->subDays($stuckDays))
            ->with('company:id,company_name')
            ->limit(10)
            ->get();

        foreach ($stuckOrders as $o) {
            $alerts[] = [
                'type' => 'stuck_order',
                'severity' => 'warning',
                'title' => __('admin_dashboard.alert_stuck_order'),
                'description' => __('dashboard.order') . ' #' . $o->id . ' — ' . ($o->company?->company_name ?? '-'),
                'url' => route('admin.orders.show', $o),
                'count' => null,
            ];
        }

        // Inactive companies: no orders in 90 days
        $inactiveDays = (int) config('servx.inactive_company_days', 90);
        $inactiveCompanies = Company::query()
            ->where('status', 'active')
            ->whereDoesntHave('orders', fn ($q) => $q->where('created_at', '>=', now()->subDays($inactiveDays)))
            ->where('created_at', '<', now()->subDays($inactiveDays))
            ->limit(10)
            ->get(['id', 'company_name']);

        foreach ($inactiveCompanies as $c) {
            $alerts[] = [
                'type' => 'inactive_company',
                'severity' => 'info',
                'title' => __('admin_dashboard.alert_inactive_company'),
                'description' => $c->company_name,
                'url' => route('admin.companies.show', $c),
                'count' => null,
            ];
        }

        // Low fleet utilization
        $threshold = (int) config('servx.low_fleet_utilization_threshold', 30);
        $stats = $this->stats;
        if (($stats['fleet_utilization'] ?? 100) < $threshold && ($stats['vehicles'] ?? 0) > 0) {
            $alerts[] = [
                'type' => 'low_fleet_utilization',
                'severity' => 'warning',
                'title' => __('admin_dashboard.alert_low_fleet_utilization'),
                'description' => __('admin_dashboard.fleet_utilization') . ': ' . ($stats['fleet_utilization'] ?? 0) . '%',
                'url' => route('admin.vehicles.index'),
                'count' => null,
            ];
        }

        // Vehicle document expiry
        $expiryService = app(ExpiryMonitoringService::class);
        $expiringCount = $expiryService->countExpiringForAdmin();
        if ($expiringCount > 0) {
            $alerts[] = [
                'type' => 'document_expiry',
                'severity' => $expiringCount > 5 ? 'warning' : 'info',
                'title' => __('admin_dashboard.alert_document_expiry'),
                'description' => $expiringCount . ' ' . __('vehicles.expiring_documents'),
                'url' => route('admin.vehicles.expiring-documents'),
                'count' => $expiringCount,
            ];
        }

        return array_slice($alerts, 0, 12);
    }

    public function getRecentActivityProperty(): array
    {
        $activities = [];

        // Recent companies
        Company::latest()->take(5)->get(['id', 'company_name', 'created_at'])->each(function ($c) use (&$activities) {
            $activities[] = [
                'type' => 'company_added',
                'title' => __('admin_dashboard.activity_company_added'),
                'description' => $c->company_name,
                'time' => $c->created_at,
                'url' => route('admin.companies.show', $c),
            ];
        });

        // Recent vehicles
        Vehicle::with('company:id,company_name')
            ->latest()
            ->take(5)
            ->get(['id', 'company_id', 'plate_number', 'make', 'model', 'created_at'])
            ->each(function ($v) use (&$activities) {
                $activities[] = [
                    'type' => 'vehicle_added',
                    'title' => __('admin_dashboard.activity_vehicle_added'),
                    'description' => ($v->make . ' ' . $v->model . ' - ' . $v->plate_number) . ' (' . ($v->company?->company_name ?? '-') . ')',
                    'time' => $v->created_at,
                    'url' => $v->company_id ? route('admin.companies.show', $v->company_id) : null,
                ];
            });

        // Recent orders
        Order::with('company:id,company_name')
            ->latest()
            ->take(5)
            ->get(['id', 'company_id', 'status', 'created_at'])
            ->each(function ($o) use (&$activities) {
                $activities[] = [
                    'type' => 'order_created',
                    'title' => __('admin_dashboard.activity_order_created'),
                    'description' => __('dashboard.order') . ' #' . $o->id . ' - ' . ($o->company?->company_name ?? '-'),
                    'time' => $o->created_at,
                    'url' => route('admin.orders.show', $o),
                ];
            });

        // Recent invoices (from Invoice or Attachment driver_invoice)
        Invoice::with('company:id,company_name')
            ->latest()
            ->take(5)
            ->get(['id', 'company_id', 'invoice_number', 'created_at'])
            ->each(function ($inv) use (&$activities) {
                $activities[] = [
                    'type' => 'invoice_uploaded',
                    'title' => __('admin_dashboard.activity_invoice_uploaded'),
                    'description' => ($inv->invoice_number ?? '#' . $inv->id) . ' - ' . ($inv->company?->company_name ?? '-'),
                    'time' => $inv->created_at,
                    'url' => $inv->company_id ? route('admin.companies.show', $inv->company_id) : null,
                ];
            });

        // Merge and sort by time
        usort($activities, fn ($a, $b) => $b['time']->timestamp <=> $a['time']->timestamp);

        return array_slice($activities, 0, 15);
    }

    public function getSystemHealthProperty(): array
    {
        $health = [];

        // Queue: check if jobs table has failed jobs
        try {
            $failedCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $health['queue_pending'] = $failedCount;
        } catch (\Throwable $e) {
            $health['queue_pending'] = null;
        }

        try {
            $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
            $health['queue_failed'] = $failedCount;
        } catch (\Throwable $e) {
            $health['queue_failed'] = null;
        }

        // Storage usage (public disk)
        try {
            $size = 0;
            $path = storage_path('app/public');
            if (is_dir($path)) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                    $size += $file->getSize();
                }
            }
            $health['storage_mb'] = round($size / 1024 / 1024, 2);
        } catch (\Throwable $e) {
            $health['storage_mb'] = null;
        }

        return $health;
    }

    public function retryFailedJobs(): void
    {
        Artisan::call('queue:retry', ['id' => 'all']);
        Cache::forget('admin_stats_version');
        session()->flash('success', __('admin_dashboard.retry_failed_jobs') . ' — ' . __('common.done'));
    }

    public function clearCache(): void
    {
        Artisan::call('cache:clear');
        Cache::forget('admin_stats_version');
        session()->flash('success', __('admin_dashboard.clear_cache') . ' — ' . __('common.done'));
    }

    /** Safely get a computed property, returning fallback on exception. */
    private function safeGet(string $property, mixed $fallback = []): mixed
    {
        try {
            return $this->{$property};
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SuperDashboard property failed: ' . $property, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $fallback;
        }
    }

    public function render()
    {
        return view('livewire.admin.super-dashboard', [
            'stats' => $this->safeGet('stats', ['companies' => 0, 'vehicles' => 0, 'orders' => 0, 'pending_quota_requests' => 0]),
            'alerts' => $this->safeGet('alerts'),
            'companies' => $this->safeGet('companies', new \Illuminate\Pagination\LengthAwarePaginator([], 0, 8)),
            'recentActivity' => $this->safeGet('recentActivity'),
            'ordersPerCompany' => $this->safeGet('ordersPerCompany'),
            'ordersPerVehicle' => $this->safeGet('ordersPerVehicle'),
            'ordersPerVehicleOverTime' => $this->safeGet('ordersPerVehicleOverTime'),
            'monthlyOrders' => $this->safeGet('monthlyOrders'),
            'orderStatusDistribution' => $this->safeGet('orderStatusDistribution'),
            'averageResolutionHours' => $this->safeGet('averageResolutionHours'),
            'systemHealth' => $this->safeGet('systemHealth'),
            'fleetAnalytics' => $this->safeGet('fleetAnalytics'),
            'monthlyMaintenanceTrend' => $this->safeGet('monthlyMaintenanceTrend'),
            'monthlyFuelTrend' => $this->safeGet('monthlyFuelTrend'),
            'topVehiclesByCost' => $this->safeGet('topVehiclesByCost'),
            'maintenanceVsFuel' => $this->safeGet('maintenanceVsFuel'),
        ]);
    }
}
