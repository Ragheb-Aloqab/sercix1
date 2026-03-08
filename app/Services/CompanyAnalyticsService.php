<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyFuelInvoice;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\FuelRefill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyAnalyticsService
{
    private const CACHE_TTL = 600; // 10 minutes

    public function __construct(
        private readonly Company $company
    ) {}

    /** SQLite uses strftime; MySQL uses YEAR/MONTH. Returns [selectRaw, groupByRaw] for the given column (e.g. "orders.created_at"). */
    private function yearMonthRaw(string $column): array
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return [
                "CAST(strftime('%Y', {$column}) AS INTEGER) as year, CAST(strftime('%m', {$column}) AS INTEGER) as month",
                "strftime('%Y', {$column}), strftime('%m', {$column})",
            ];
        }
        return [
            "YEAR({$column}) as year, MONTH({$column}) as month",
            "YEAR({$column}), MONTH({$column})",
        ];
    }

    public function maintenanceCost(): float
    {
        $orderTotal = (float) DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $this->company->id)
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?: 0;

        $companyInvoiceTotal = (float) CompanyMaintenanceInvoice::where('company_id', $this->company->id)
            ->sum('amount');

        return $orderTotal + $companyInvoiceTotal;
    }

    public function fuelsCost(): float
    {
        $refillTotal = (float) FuelRefill::where('company_id', $this->company->id)->sum('cost');
        $invoiceTotal = (float) CompanyFuelInvoice::where('company_id', $this->company->id)->sum('amount');

        return $refillTotal + $invoiceTotal;
    }

    public function otherCost(): float
    {
        return 0.0;
    }

    public function totalActualCost(): float
    {
        return $this->maintenanceCost() + $this->fuelsCost() + $this->otherCost();
    }

    public function getFuelCostsSummary(
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null,
        ?int $vehicleId = null
    ): array {
        $q = DB::table('fuel_refills')->where('company_id', $this->company->id);

        if ($dateFrom) {
            $q->where('refilled_at', '>=', $dateFrom->copy()->startOfDay());
        }
        if ($dateTo) {
            $q->where('refilled_at', '<=', $dateTo->copy()->endOfDay());
        }
        if ($vehicleId) {
            $q->where('vehicle_id', $vehicleId);
        }

        $row = $q->selectRaw('COALESCE(SUM(cost), 0) as total, COALESCE(AVG(cost), 0) as avg, COUNT(*) as count')
            ->first();

        $refillTotal = (float) ($row->total ?? 0);
        $refillCount = (int) ($row->count ?? 0);

        $invQ = CompanyFuelInvoice::where('company_id', $this->company->id);
        if ($dateFrom) {
            $invQ->where('created_at', '>=', $dateFrom->copy()->startOfDay());
        }
        if ($dateTo) {
            $invQ->where('created_at', '<=', $dateTo->copy()->endOfDay());
        }
        if ($vehicleId) {
            $invQ->where('vehicle_id', $vehicleId);
        }
        $invoiceTotal = (float) (clone $invQ)->sum('amount');
        $invoiceCount = (int) (clone $invQ)->count();

        $total = $refillTotal + $invoiceTotal;
        $count = $refillCount + $invoiceCount;

        return [
            'total' => round($total, 2),
            'avg' => $count > 0 ? round($total / $count, 2) : 0,
            'count' => $count,
        ];
    }

    public function getMaintenanceCostsSummary(
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null,
        ?int $vehicleId = null
    ): array {
        $baseQuery = fn () => DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $this->company->id)
            ->when($dateFrom, fn ($q) => $q->where('orders.created_at', '>=', $dateFrom->copy()->startOfDay()))
            ->when($dateTo, fn ($q) => $q->where('orders.created_at', '<=', $dateTo->copy()->endOfDay()))
            ->when($vehicleId, fn ($q) => $q->where('orders.vehicle_id', $vehicleId));

        $orderTotal = (float) (clone $baseQuery())
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?? 0;

        $orderCount = (int) (clone $baseQuery())
            ->selectRaw('COUNT(DISTINCT orders.id) as cnt')
            ->value('cnt') ?? 0;

        $invoiceQ = CompanyMaintenanceInvoice::where('company_id', $this->company->id)
            ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom->copy()->startOfDay()))
            ->when($dateTo, fn ($q) => $q->where('created_at', '<=', $dateTo->copy()->endOfDay()))
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId));
        $invoiceTotal = (float) (clone $invoiceQ)->sum('amount');
        $invoiceCount = (int) (clone $invoiceQ)->count();

        $total = $orderTotal + $invoiceTotal;
        $count = $orderCount + $invoiceCount;

        return [
            'total' => round($total, 2),
            'avg' => $count > 0 ? round($total / $count, 2) : 0,
            'count' => $count,
        ];
    }

    public function dailyCost(): float
    {
        $total = $this->totalActualCost();
        return round($total / 30, 2);
    }

    public function monthlyCost(): float
    {
        $total = $this->totalActualCost();
        $months = max(1, $this->company->orders()->where('created_at', '>=', now()->subMonths(12))->count() ? 12 : 1);
        return round($total / 1000 / $months, 2);
    }

    public function dailyProgressPercentage(): float
    {
        $target = 500;
        return min(100, max(0, ($this->dailyCost() / $target) * 100));
    }

    public function monthlyProgressPercentage(): float
    {
        $target = 50;
        return min(100, max(0, ($this->monthlyCost() / $target) * 100));
    }

    public function lastSevenMonthsComparison(): array
    {
        return Cache::remember(
            "company_{$this->company->id}_last_seven_months",
            self::CACHE_TTL,
            fn () => $this->computeLastSevenMonthsComparison()
        );
    }

    private function computeLastSevenMonthsComparison(): array
    {
        $start = now()->subMonths(6)->startOfMonth();
        $end = now()->endOfMonth();

        [$orderSelect, $orderGroup] = $this->yearMonthRaw('orders.created_at');
        $rows = DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $this->company->id)
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw($orderSelect)
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total_cost')
            ->groupByRaw($orderGroup)
            ->orderByRaw('year, month')
            ->get()
            ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

        [$invSelect, $invGroup] = $this->yearMonthRaw('created_at');
        $invoiceRows = CompanyMaintenanceInvoice::where('company_id', $this->company->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw($invSelect)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_cost')
            ->groupByRaw($invGroup)
            ->get()
            ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

        $out = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = "{$date->year}-{$date->month}";
            $orderCost = (float) (($rows[$key] ?? null)?->total_cost ?? 0);
            $invoiceCost = (float) (($invoiceRows[$key] ?? null)?->total_cost ?? 0);
            $out[] = [
                'month' => $date->month,
                'year' => $date->year,
                'total_cost' => round($orderCost + $invoiceCost, 2),
            ];
        }
        return $out;
    }

    public function lastSevenMonthsPercentage(): float
    {
        $rows = $this->lastSevenMonthsComparison();
        if (count($rows) < 2) {
            return 0.0;
        }
        $current = (float) ($rows[6]['total_cost'] ?? 0);
        $prevSum = array_sum(array_column(array_slice($rows, 0, 6), 'total_cost'));
        $prevAvg = $prevSum / 6;
        if ($prevAvg == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $prevAvg) / $prevAvg) * 100, 2);
    }

    public function fuelCostByMonth(): array
    {
        return Cache::remember(
            "company_{$this->company->id}_fuel_by_month",
            self::CACHE_TTL,
            fn () => $this->computeFuelCostByMonth()
        );
    }

    private function computeFuelCostByMonth(): array
    {
        $start = now()->subMonths(6)->startOfMonth();
        $end = now()->endOfMonth();

        [$refillSelect, $refillGroup] = $this->yearMonthRaw('refilled_at');
        $refillRows = DB::table('fuel_refills')
            ->where('company_id', $this->company->id)
            ->whereBetween('refilled_at', [$start, $end])
            ->selectRaw($refillSelect)
            ->selectRaw('COALESCE(SUM(cost), 0) as total_cost')
            ->groupByRaw($refillGroup)
            ->orderByRaw('year, month')
            ->get()
            ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

        [$invSelect, $invGroup] = $this->yearMonthRaw('created_at');
        $invoiceRows = CompanyFuelInvoice::where('company_id', $this->company->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw($invSelect)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_cost')
            ->groupByRaw($invGroup)
            ->get()
            ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

        $out = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = "{$date->year}-{$date->month}";
            $refillCost = (float) (($refillRows[$key] ?? null)?->total_cost ?? 0);
            $invoiceCost = (float) (($invoiceRows[$key] ?? null)?->total_cost ?? 0);
            $out[] = [
                'month' => $date->month,
                'year' => $date->year,
                'total_cost' => round($refillCost + $invoiceCost, 2),
            ];
        }
        return $out;
    }

    public function getTopVehiclesByServiceConsumptionAndCost()
    {
        return Cache::remember(
            "company_{$this->company->id}_top_vehicles",
            self::CACHE_TTL,
            fn () => $this->computeTopVehicles()
        );
    }

    private function computeTopVehicles()
    {
        $totalCompany = $this->totalActualCost();
        $serviceRows = DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $this->company->id)
            ->whereNotNull('orders.vehicle_id')
            ->select('orders.vehicle_id')
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->selectRaw('COUNT(*) as services_count')
            ->groupBy('orders.vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $invoiceRows = CompanyMaintenanceInvoice::where('company_id', $this->company->id)
            ->whereNotNull('vehicle_id')
            ->selectRaw('vehicle_id, COALESCE(SUM(amount), 0) as total, COUNT(*) as invoice_count')
            ->groupBy('vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $fuelRefillRows = FuelRefill::where('company_id', $this->company->id)
            ->selectRaw('vehicle_id, COALESCE(SUM(cost), 0) as total')
            ->groupBy('vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $fuelInvoiceRows = CompanyFuelInvoice::where('company_id', $this->company->id)
            ->whereNotNull('vehicle_id')
            ->selectRaw('vehicle_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $vehicles = $this->company->vehicles()->get(['id', 'make', 'model', 'plate_number']);
        $list = $vehicles->map(function ($v) use ($serviceRows, $invoiceRows, $fuelRefillRows, $fuelInvoiceRows, $totalCompany) {
            $sRow = $serviceRows[$v->id] ?? null;
            $iRow = $invoiceRows[$v->id] ?? null;
            $fRow = $fuelRefillRows[$v->id] ?? null;
            $fiRow = $fuelInvoiceRows[$v->id] ?? null;
            $serviceCost = ($sRow ? (float) $sRow->total : 0) + ($iRow ? (float) $iRow->total : 0);
            $fuelCost = ($fRow ? (float) $fRow->total : 0) + ($fiRow ? (float) $fiRow->total : 0);
            $total = $serviceCost + $fuelCost;
            $servicesCount = ($sRow ? (int) $sRow->services_count : 0) + ($iRow ? (int) $iRow->invoice_count : 0);
            $percentage = $totalCompany > 0 ? ($total / $totalCompany) * 100 : 0;
            return (object) [
                'id' => $v->id,
                'make' => $v->make,
                'model' => $v->model,
                'plate_number' => $v->plate_number,
                'total_service_cost' => round($serviceCost, 2),
                'total_fuel_cost' => round($fuelCost, 2),
                'total_cost' => round($total, 2),
                'services_count' => $servicesCount,
                'percentage' => round($percentage, 1),
            ];
        })->filter(fn ($i) => $i->total_cost > 0)->sortByDesc('total_cost')->values();

        return $list;
    }

    public function getTop5VehiclesSummary(): array
    {
        $top = $this->getTopVehiclesByServiceConsumptionAndCost()->take(5);
        $topTotal = $top->sum('total_cost');
        $grand = $this->totalActualCost();
        $ui_percentage = $grand > 0 ? round(($topTotal / $grand) * 100, 1) : 0;
        return [
            'top_total' => round($topTotal, 2),
            'ui_percentage' => $ui_percentage,
        ];
    }

    public function maintenanceCostIndicator(): array
    {
        $rows = $this->lastSevenMonthsComparison();
        $current = (float) ($rows[6]['total_cost'] ?? 0);
        $prevAvg = count($rows) >= 6 ? array_sum(array_column(array_slice($rows, 0, 6), 'total_cost')) / 6 : $current;
        if ($prevAvg == 0) {
            return ['direction' => 'stable', 'percent' => 0];
        }
        $pct = (($current - $prevAvg) / $prevAvg) * 100;
        $direction = $pct > 5 ? 'up' : ($pct < -5 ? 'down' : 'stable');
        return ['direction' => $direction, 'percent' => round(abs($pct), 1)];
    }

    public function fuelConsumptionIndicator(): array
    {
        $rows = $this->fuelCostByMonth();
        if (count($rows) < 2) {
            return ['direction' => 'stable', 'percent' => 0];
        }
        $current = (float) ($rows[6]['total_cost'] ?? 0);
        $prevSum = array_sum(array_column(array_slice($rows, 0, 6), 'total_cost'));
        $prevAvg = $prevSum / 6;
        if ($prevAvg == 0) {
            return ['direction' => $current > 0 ? 'up' : 'stable', 'percent' => $current > 0 ? 100 : 0];
        }
        $pct = (($current - $prevAvg) / $prevAvg) * 100;
        $direction = $pct > 5 ? 'up' : ($pct < -5 ? 'down' : 'stable');
        return ['direction' => $direction, 'percent' => round(abs($pct), 1)];
    }

    public function operatingCostIndicator(): array
    {
        return $this->maintenanceCostIndicator();
    }

    public function totalInvoices(): int
    {
        return $this->company->invoices()->count();
    }

    public function totalVehicleDistance(): float
    {
        return (float) DB::table('vehicle_monthly_mileage')
            ->join('vehicles', 'vehicles.id', '=', 'vehicle_monthly_mileage.vehicle_id')
            ->where('vehicles.company_id', $this->company->id)
            ->sum('vehicle_monthly_mileage.total_km');
    }

    /** Full analytics DTO for dashboard/reports/API */
    public function getAnalytics(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->startOfMonth();
        $to = $to ?? now()->endOfMonth();

        return [
            'maintenance_cost' => $this->maintenanceCost(),
            'fuels_cost' => $this->fuelsCost(),
            'total_actual_cost' => $this->totalActualCost(),
            'daily_cost' => $this->dailyCost(),
            'monthly_cost' => $this->monthlyCost(),
            'fuel_summary' => $this->getFuelCostsSummary($from, $to),
            'maintenance_summary' => $this->getMaintenanceCostsSummary($from, $to),
            'last_seven_months' => $this->lastSevenMonthsComparison(),
            'fuel_by_month' => $this->fuelCostByMonth(),
            'top_vehicles' => $this->getTopVehiclesByServiceConsumptionAndCost()->take(10)->values()->all(),
            'top_5_summary' => $this->getTop5VehiclesSummary(),
            'maintenance_indicator' => $this->maintenanceCostIndicator(),
            'fuel_indicator' => $this->fuelConsumptionIndicator(),
            'total_invoices' => $this->totalInvoices(),
            'total_vehicle_distance' => $this->totalVehicleDistance(),
        ];
    }

    public static function for(Company $company): self
    {
        return new self($company);
    }

    private const DASHBOARD_STATS_TTL = 300; // 5 minutes
    private const DASHBOARD_STATS_KEY = 'company:%d:dashboard_stats';

    /**
     * Cached dashboard aggregates for company dashboard (Phase 1).
     * Invalidated by VehicleObserver, OrderObserver, FuelRefillObserver.
     */
    public function getDashboardStats(): array
    {
        $key = sprintf(self::DASHBOARD_STATS_KEY, $this->company->id);

        return Cache::remember($key, self::DASHBOARD_STATS_TTL, function () {
            $company = $this->company;
            $company->loadCount(['orders', 'invoices', 'branches']);
            $today = now()->toDateString();
            $orderStats = \App\Models\Order::query()
                ->where('company_id', $company->id)
                ->selectRaw("
                    SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_orders,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                ", [$today])
                ->first();
            $recentInvoices = $company->invoices()->with('order')->latest()->limit(10)->get();
            $latestOrders = $company->orders()->with(['vehicle:id,company_id,make,model,plate_number', 'services'])->latest()->limit(6)->get();
            $enabledServices = $company->services()->wherePivot('is_enabled', true)->take(8)->get(['services.id', 'services.name', 'services.base_price']);
            $topVehicles = $this->getTopVehiclesByServiceConsumptionAndCost()->take(5);
            $totalCost = $this->maintenanceCost() + $this->fuelsCost();
            $top5Summary = [
                'top_total' => round($topVehicles->sum('total_cost'), 2),
                'ui_percentage' => $totalCost > 0 ? round(($topVehicles->sum('total_cost') / $totalCost) * 100, 1) : 0,
            ];
            return [
                'todayOrders' => (int) ($orderStats?->today_orders ?? 0),
                'inProgress' => (int) ($orderStats?->in_progress ?? 0),
                'completed' => (int) ($orderStats?->completed ?? 0),
                'latestOrders' => $latestOrders,
                'enabledServices' => $enabledServices,
                'recentInvoices' => $recentInvoices,
                'topVehicles' => $topVehicles,
                'top5Summary' => $top5Summary,
                'maintenanceIndicator' => $this->maintenanceCostIndicator(),
                'fuelIndicator' => $this->fuelConsumptionIndicator(),
                'operatingIndicator' => $this->operatingCostIndicator(),
                'fuelSummary' => $this->getFuelCostsSummary(null, null, null),
                'maintenanceSummary' => $this->getMaintenanceCostsSummary(null, null, null),
                'vehiclesCount' => $company->vehicles()->count(),
                'totalCost' => $totalCost,
                'dailyCost' => round($this->dailyCost(), 0),
                'monthlyCost' => round($this->monthlyCost(), 1),
                'sevenMonthPercent' => $this->lastSevenMonthsPercentage(),
                'lastSevenMonths' => $this->lastSevenMonthsComparison(),
            ];
        });
    }

    public static function invalidateDashboardCache(int $companyId): void
    {
        Cache::forget(sprintf(self::DASHBOARD_STATS_KEY, $companyId));
        Cache::forget("company_dashboard_{$companyId}");
    }
}
