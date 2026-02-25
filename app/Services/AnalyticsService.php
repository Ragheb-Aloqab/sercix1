<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FuelRefill;
use App\Models\Order;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Safe division – returns 0 if divisor is 0.
     */
    private function safeDivide(?float $a, ?float $b, int $decimals = 2): float
    {
        $a = (float) ($a ?? 0);
        $b = (float) ($b ?? 0);
        return $b > 0 ? round($a / $b, $decimals) : 0.0;
    }

    /**
     * Maintenance analytics for a company (or admin with optional company filter).
     */
    public function getMaintenanceAnalytics(
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null,
        ?int $companyId = null,
        ?int $vehicleId = null,
        ?int $serviceTypeId = null
    ): array {
        $baseQ = function () use ($dateFrom, $dateTo, $companyId, $vehicleId, $serviceTypeId) {
            $q = DB::table('order_services')
                ->join('orders', 'orders.id', '=', 'order_services.order_id');
            if ($companyId) {
                $q->where('orders.company_id', $companyId);
            }
            if ($dateFrom) {
                $q->where('orders.created_at', '>=', $dateFrom->copy()->startOfDay());
            }
            if ($dateTo) {
                $q->where('orders.created_at', '<=', $dateTo->copy()->endOfDay());
            }
            if ($vehicleId) {
                $q->where('orders.vehicle_id', $vehicleId);
            }
            if ($serviceTypeId) {
                $q->where('order_services.service_id', $serviceTypeId);
            }
            return $q;
        };

        $totalCost = (float) ($baseQ()
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?? 0);

        $orderCount = (int) ($baseQ()
            ->selectRaw('COUNT(DISTINCT orders.id) as cnt')
            ->value('cnt') ?? 0);

        $vehicleCount = $vehicleId ? 1 : ($companyId
            ? Vehicle::where('company_id', $companyId)->count()
            : Vehicle::count());
        $vehicleCount = max(1, $vehicleCount);

        $companyCount = $companyId ? 1 : Company::count();
        $companyCount = max(1, $companyCount);

        $months = 1;
        if ($dateFrom && $dateTo) {
            $months = max(1, $dateFrom->diffInMonths($dateTo) ?: 1);
        }

        return [
            'total_cost' => round($totalCost, 2),
            'order_count' => $orderCount,
            'avg_per_vehicle' => $this->safeDivide($totalCost, $vehicleCount),
            'avg_per_company' => $this->safeDivide($totalCost, $companyCount),
            'avg_per_month' => $this->safeDivide($totalCost, $months),
            'avg_per_order' => $this->safeDivide($totalCost, $orderCount),
        ];
    }

    /**
     * Maintenance cost per service type.
     */
    public function getMaintenanceByServiceType(
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null,
        ?int $companyId = null,
        ?int $vehicleId = null
    ): array {
        $q = DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->leftJoin('services', 'services.id', '=', 'order_services.service_id')
            ->selectRaw('COALESCE(services.name, order_services.custom_service_name, "Custom") as service_name')
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->selectRaw('COUNT(DISTINCT orders.id) as order_count')
            ->groupByRaw('COALESCE(services.name, order_services.custom_service_name, "Custom")');

        if ($companyId) {
            $q->where('orders.company_id', $companyId);
        }
        if ($dateFrom) {
            $q->where('orders.created_at', '>=', $dateFrom->copy()->startOfDay());
        }
        if ($dateTo) {
            $q->where('orders.created_at', '<=', $dateTo->copy()->endOfDay());
        }
        if ($vehicleId) {
            $q->where('orders.vehicle_id', $vehicleId);
        }

        return $q->get()->map(fn ($r) => [
            'service_name' => $r->service_name ?? 'Custom',
            'total' => round((float) ($r->total ?? 0), 2),
            'order_count' => (int) ($r->order_count ?? 0),
            'avg_per_order' => $this->safeDivide((float) ($r->total ?? 0), (int) ($r->order_count ?? 1)),
        ])->toArray();
    }

    /**
     * Fuel analytics for a company (or admin with optional company filter).
     */
    public function getFuelAnalytics(
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null,
        ?int $companyId = null,
        ?int $vehicleId = null
    ): array {
        $baseQ = function () use ($dateFrom, $dateTo, $companyId, $vehicleId) {
            $q = DB::table('fuel_refills');
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
            if ($dateFrom) {
                $q->where('refilled_at', '>=', $dateFrom->copy()->startOfDay());
            }
            if ($dateTo) {
                $q->where('refilled_at', '<=', $dateTo->copy()->endOfDay());
            }
            if ($vehicleId) {
                $q->where('vehicle_id', $vehicleId);
            }
            return $q;
        };

        $totals = $baseQ()
            ->selectRaw('COALESCE(SUM(cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(liters), 0) as total_liters')
            ->selectRaw('COUNT(*) as refill_count')
            ->selectRaw('COALESCE(SUM(odometer_km), 0) as total_odometer')
            ->first();

        $totalCost = (float) ($totals->total_cost ?? 0);
        $totalLiters = (float) ($totals->total_liters ?? 0);
        $refillCount = (int) ($totals->refill_count ?? 0);
        $totalOdometer = (float) ($totals->total_odometer ?? 0);

        $vehicleCount = $vehicleId ? 1 : ($companyId
            ? Vehicle::where('company_id', $companyId)->count()
            : Vehicle::count());
        $vehicleCount = max(1, $vehicleCount);

        $months = 1;
        if ($dateFrom && $dateTo) {
            $months = max(1, $dateFrom->diffInMonths($dateTo) ?: 1);
        }

        $odometerRanges = $baseQ()
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->selectRaw('vehicle_id, MAX(odometer_km) - MIN(odometer_km) as km_driven')
            ->groupBy('vehicle_id')
            ->get();
        $totalKm = $odometerRanges->sum('km_driven');
        $totalKm = max(0, (float) $totalKm);

        return [
            'total_cost' => round($totalCost, 2),
            'total_liters' => round($totalLiters, 2),
            'refill_count' => $refillCount,
            'avg_per_vehicle' => $this->safeDivide($totalCost, $vehicleCount),
            'avg_liters_per_vehicle' => $this->safeDivide($totalLiters, $vehicleCount),
            'avg_per_month' => $this->safeDivide($totalCost, $months),
            'avg_per_transaction' => $this->safeDivide($totalCost, $refillCount),
            'cost_per_km' => $totalKm > 0 ? $this->safeDivide($totalCost, $totalKm) : null,
        ];
    }

    /**
     * Admin dashboard: combined analytics for date range (all companies or filtered).
     */
    public function getAdminDashboardAnalytics(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $companyId = null
    ): array {
        $maintenance = $this->getMaintenanceAnalytics($dateFrom, $dateTo, $companyId, null, null);
        $fuel = $this->getFuelAnalytics($dateFrom, $dateTo, $companyId, null);

        $vehicleCount = $companyId
            ? Vehicle::where('company_id', $companyId)->count()
            : Vehicle::count();
        $vehicleCount = max(1, $vehicleCount);

        $combinedCost = $maintenance['total_cost'] + $fuel['total_cost'];
        $months = max(1, $dateFrom->diffInMonths($dateTo) ?: 1);

        $prevFrom = $dateFrom->copy()->subMonths($months)->startOfDay();
        $prevTo = $dateFrom->copy()->subDay()->endOfDay();
        $prevMaintenance = $this->getMaintenanceAnalytics($prevFrom, $prevTo, $companyId, null, null);
        $prevFuel = $this->getFuelAnalytics($prevFrom, $prevTo, $companyId, null);

        $avgMaintPrev = $prevMaintenance['avg_per_vehicle'];
        $avgMaintCurr = $maintenance['avg_per_vehicle'];
        $avgFuelPrev = $prevFuel['avg_per_vehicle'];
        $avgFuelCurr = $fuel['avg_per_vehicle'];

        $maintTrend = $avgMaintPrev > 0 ? (($avgMaintCurr - $avgMaintPrev) / $avgMaintPrev) * 100 : 0;
        $fuelTrend = $avgFuelPrev > 0 ? (($avgFuelCurr - $avgFuelPrev) / $avgFuelPrev) * 100 : 0;

        $fuelVsMaintRatio = $maintenance['total_cost'] > 0
            ? round($fuel['total_cost'] / $maintenance['total_cost'], 2)
            : ($fuel['total_cost'] > 0 ? 999.99 : 0);

        return [
            'maintenance' => $maintenance,
            'fuel' => $fuel,
            'avg_maintenance_per_vehicle' => $maintenance['avg_per_vehicle'],
            'avg_fuel_per_vehicle' => $fuel['avg_per_vehicle'],
            'cost_per_vehicle_combined' => $this->safeDivide($combinedCost, $vehicleCount),
            'monthly_avg_per_vehicle' => $this->safeDivide($combinedCost, $vehicleCount * $months),
            'fuel_vs_maintenance_ratio' => $fuelVsMaintRatio,
            'maintenance_trend_pct' => round($maintTrend, 1),
            'fuel_trend_pct' => round($fuelTrend, 1),
        ];
    }

    /**
     * Monthly maintenance trend (for chart).
     */
    public function getMonthlyMaintenanceTrend(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $companyId = null
    ): array {
        $rows = DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->selectRaw('YEAR(orders.created_at) as year, MONTH(orders.created_at) as month')
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->selectRaw('COUNT(DISTINCT orders.id) as order_count')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->when($companyId, fn ($q) => $q->where('orders.company_id', $companyId))
            ->groupByRaw('YEAR(orders.created_at), MONTH(orders.created_at)')
            ->orderByRaw('year, month')
            ->get()
            ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

        $out = [];
        $current = $dateFrom->copy()->startOfMonth();
        while ($current <= $dateTo) {
            $key = "{$current->year}-{$current->month}";
            $row = $rows[$key] ?? null;
            $total = $row ? (float) $row->total : 0;
            $count = $row ? (int) $row->order_count : 0;
            $out[] = [
                'label' => $current->translatedFormat('M Y'),
                'total' => round($total, 2),
                'avg' => $count > 0 ? round($total / $count, 2) : 0,
                'order_count' => $count,
            ];
            $current->addMonth();
        }
        return $out;
    }

    /**
     * Monthly fuel trend (for chart).
     */
    public function getMonthlyFuelTrend(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $companyId = null
    ): array {
        $rows = DB::table('fuel_refills')
            ->selectRaw('YEAR(refilled_at) as year, MONTH(refilled_at) as month')
            ->selectRaw('COALESCE(SUM(cost), 0) as total')
            ->selectRaw('COUNT(*) as refill_count')
            ->whereBetween('refilled_at', [$dateFrom, $dateTo])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->groupByRaw('YEAR(refilled_at), MONTH(refilled_at)')
            ->orderByRaw('year, month')
            ->get()
            ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

        $out = [];
        $current = $dateFrom->copy()->startOfMonth();
        while ($current <= $dateTo) {
            $key = "{$current->year}-{$current->month}";
            $row = $rows[$key] ?? null;
            $total = $row ? (float) $row->total : 0;
            $count = $row ? (int) $row->refill_count : 0;
            $out[] = [
                'label' => $current->translatedFormat('M Y'),
                'total' => round($total, 2),
                'avg' => $count > 0 ? round($total / $count, 2) : 0,
                'refill_count' => $count,
            ];
            $current->addMonth();
        }
        return $out;
    }

    /**
     * Top 5 vehicles by combined operating cost (maintenance + fuel).
     */
    public function getTopVehiclesByOperatingCost(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $companyId = null,
        int $limit = 5
    ): array {
        $baseQ = Vehicle::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->with('company:id,company_name');

        $maintenanceByVehicle = DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->when($companyId, fn ($q) => $q->where('orders.company_id', $companyId))
            ->selectRaw('orders.vehicle_id')
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->groupBy('orders.vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $fuelByVehicle = DB::table('fuel_refills')
            ->whereBetween('refilled_at', [$dateFrom, $dateTo])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->selectRaw('vehicle_id')
            ->selectRaw('COALESCE(SUM(cost), 0) as total')
            ->groupBy('vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $vehicleIds = collect($maintenanceByVehicle->keys())->merge($fuelByVehicle->keys())->unique()->filter()->values()->all();
        if (empty($vehicleIds)) {
            return [];
        }
        $vehicles = $baseQ->whereIn('id', $vehicleIds)->get()->keyBy('id');

        $costs = [];
        foreach ($vehicleIds as $vid) {
            $v = $vehicles->get($vid);
            if (!$v) {
                continue;
            }
            $maintRow = $maintenanceByVehicle->get($vid);
            $fuelRow = $fuelByVehicle->get($vid);
            $maint = (float) ($maintRow->total ?? 0);
            $fuel = (float) ($fuelRow->total ?? 0);
            $costs[] = [
                'vehicle' => $v,
                'maintenance' => round($maint, 2),
                'fuel' => round($fuel, 2),
                'total' => round($maint + $fuel, 2),
                'avg_per_month' => round(($maint + $fuel) / max(1, $dateFrom->diffInMonths($dateTo)), 2),
            ];
        }

        usort($costs, fn ($a, $b) => $b['total'] <=> $a['total']);
        return array_slice($costs, 0, $limit);
    }

    /**
     * Maintenance vs Fuel cost distribution (for pie chart).
     */
    public function getMaintenanceVsFuelDistribution(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $companyId = null
    ): array {
        $maintenance = $this->getMaintenanceAnalytics($dateFrom, $dateTo, $companyId, null, null);
        $fuel = $this->getFuelAnalytics($dateFrom, $dateTo, $companyId, null);

        $total = $maintenance['total_cost'] + $fuel['total_cost'];
        if ($total <= 0) {
            return [
                ['label' => __('reports.maintenance'), 'value' => 0, 'percent' => 0],
                ['label' => __('reports.fuel'), 'value' => 0, 'percent' => 0],
            ];
        }

        return [
            ['label' => __('reports.maintenance'), 'value' => $maintenance['total_cost'], 'percent' => round(($maintenance['total_cost'] / $total) * 100, 1)],
            ['label' => __('reports.fuel'), 'value' => $fuel['total_cost'], 'percent' => round(($fuel['total_cost'] / $total) * 100, 1)],
        ];
    }
}
