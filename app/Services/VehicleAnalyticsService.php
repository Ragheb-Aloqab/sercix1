<?php

namespace App\Services;

use App\Models\CompanyFuelInvoice;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\MaintenanceRequest;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VehicleAnalyticsService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get vehicle-level analytics for the details page.
     */
    public function getVehicleAnalytics(Vehicle $vehicle): array
    {
        $cacheKey = "vehicle_analytics_{$vehicle->id}";
        return Cache::remember($cacheKey, self::CACHE_TTL, fn () => $this->computeVehicleAnalytics($vehicle));
    }

    private function computeVehicleAnalytics(Vehicle $vehicle): array
    {
        $maintenanceCost = $this->getVehicleMaintenanceCost($vehicle->id);
        $fuelRefillCost = (float) $vehicle->fuelRefills()->sum('cost');
        $fuelInvoiceCost = (float) CompanyFuelInvoice::where('vehicle_id', $vehicle->id)->sum('amount');
        $fuelCost = $fuelRefillCost + $fuelInvoiceCost;
        $totalCost = $maintenanceCost + $fuelCost;

        // Cost breakdown: Maintenance, Fuel, Parts, Other (parts/other = 0 when not separately tracked)
        $breakdown = [
            'maintenance' => round($maintenanceCost, 2),
            'fuel' => round($fuelCost, 2),
            'parts' => 0,
            'other' => 0,
        ];

        // Current month cost
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $currentMonthCost = $this->getVehicleCostForPeriod($vehicle->id, $currentMonthStart, $now);
        $lastMonthCost = $this->getVehicleCostForPeriod($vehicle->id, $lastMonthStart, $lastMonthEnd);

        $percentChange = $lastMonthCost > 0
            ? round((($currentMonthCost - $lastMonthCost) / $lastMonthCost) * 100, 1)
            : ($currentMonthCost > 0 ? 100 : 0);

        // Total mileage (latest odometer from fuel refills)
        $latestOdometer = $vehicle->fuelRefills()->whereNotNull('odometer_km')->orderByDesc('refilled_at')->value('odometer_km');
        $totalMileage = (int) ($latestOdometer ?? 0);

        // Operational health score (0-100): documents OK + inspection OK + cost reasonable
        $healthScore = $this->computeHealthScore($vehicle);

        // Annual cost (last 12 months)
        $yearStart = $now->copy()->subYear()->startOfMonth();
        $yearlyCost = $this->getVehicleCostForPeriod($vehicle->id, $yearStart, $now);
        $avgMonthlyCost = 12 > 0 ? round($yearlyCost / 12, 0) : 0;

        // Cost per kilometer (useful fleet efficiency metric)
        $costPerKm = $totalMileage > 0 ? round($totalCost / $totalMileage, 2) : 0.0;

        return [
            'current_month_cost' => round($currentMonthCost, 2),
            'last_month_cost' => round($lastMonthCost, 2),
            'percent_change' => $percentChange,
            'total_cost' => round($totalCost, 2),
            'maintenance_cost' => round($maintenanceCost, 2),
            'fuel_cost' => round($fuelCost, 2),
            'cost_breakdown' => $breakdown,
            'total_mileage' => $totalMileage,
            'health_score' => $healthScore,
            'yearly_cost' => round($yearlyCost, 2),
            'avg_monthly_cost' => $avgMonthlyCost,
            'cost_per_km' => $costPerKm,
        ];
    }

    private function getVehicleMaintenanceCost(int $vehicleId): float
    {
        $mrTotal = (float) MaintenanceRequest::query()
            ->where('vehicle_id', $vehicleId)
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
            })
            ->selectRaw('COALESCE(SUM(COALESCE(final_invoice_amount, approved_quote_amount)), 0) as total')
            ->value('total');

        $orderTotal = (float) DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.vehicle_id', $vehicleId)
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?? 0;

        $invoiceTotal = (float) CompanyMaintenanceInvoice::where('vehicle_id', $vehicleId)->sum('amount');

        return $mrTotal + $orderTotal + $invoiceTotal;
    }

    private function getVehicleCostForPeriod(int $vehicleId, $from, $to): float
    {
        $mrTotal = (float) MaintenanceRequest::query()
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
            })
            ->selectRaw('COALESCE(SUM(COALESCE(final_invoice_amount, approved_quote_amount)), 0) as total')
            ->value('total');

        $orderTotal = (float) DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.vehicle_id', $vehicleId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?? 0;

        $invoiceTotal = (float) CompanyMaintenanceInvoice::where('vehicle_id', $vehicleId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $fuelRefillTotal = (float) DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('refilled_at', [$from, $to])
            ->sum('cost');

        $fuelInvoiceTotal = (float) CompanyFuelInvoice::where('vehicle_id', $vehicleId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        return $mrTotal + $orderTotal + $invoiceTotal + $fuelRefillTotal + $fuelInvoiceTotal;
    }

    private function computeHealthScore(Vehicle $vehicle): int
    {
        $score = 80; // Base
        $expiryService = app(ExpiryMonitoringService::class);
        $docStatus = $expiryService->getVehicleDocumentStatus($vehicle);
        if (($docStatus['registration']['status'] ?? '') === 'expired' || ($docStatus['insurance']['status'] ?? '') === 'expired') {
            $score -= 25;
        } elseif (($docStatus['registration']['status'] ?? '') === 'expiring_soon' || ($docStatus['insurance']['status'] ?? '') === 'expiring_soon') {
            $score -= 10;
        }
        $inspectionService = app(VehicleInspectionService::class);
        $inspStatus = $inspectionService->getVehicleInspectionStatus($vehicle);
        if (($inspStatus['status'] ?? '') === 'overdue') {
            $score -= 20;
        } elseif (($inspStatus['status'] ?? '') === 'pending') {
            $score -= 5;
        }
        return max(0, min(100, $score));
    }

    /**
     * Cost vs Market chart data for this vehicle.
     */
    public function getVehicleCostVsMarketChart(Vehicle $vehicle, int $months = 6): array
    {
        $cacheKey = "vehicle_chart_{$vehicle->id}_{$months}";
        return Cache::remember($cacheKey, self::CACHE_TTL, fn () => $this->computeVehicleCostVsMarket($vehicle, $months));
    }

    private function computeVehicleCostVsMarket(Vehicle $vehicle, int $months): array
    {
        $marketService = app(MarketComparisonService::class);
        $marketData = $marketService->getMarketSegmentData(now()->subMonths($months)->startOfDay());
        $globalAvg = $marketData['global_avg'];

        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $vehicleCost = $this->getVehicleCostForPeriod($vehicle->id, $monthStart, $monthEnd);

            $mrCount = MaintenanceRequest::query()
                ->where('vehicle_id', $vehicle->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->where(function ($q) {
                    $q->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
                })
                ->count();

            $orderCount = (int) DB::table('orders')
                ->where('vehicle_id', $vehicle->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))->from('order_services')->whereColumn('order_services.order_id', 'orders.id');
                })
                ->count();

            $jobsCount = $mrCount + $orderCount;
            $segmentAvg = $this->getSegmentAvgForVehicle($vehicle, $marketData['segments'], $globalAvg);
            $marketCost = $jobsCount > 0 ? $jobsCount * $segmentAvg : $segmentAvg;

            $out[] = [
                'year' => $date->year,
                'month' => $date->month,
                'month_label' => $date->translatedFormat('M'),
                'vehicle_cost' => round($vehicleCost, 2),
                'market_cost' => round($marketCost, 2),
            ];
        }
        return $out;
    }

    /**
     * Vehicle vs market comparison for annual summary.
     */
    public function getVehicleMarketComparison(Vehicle $vehicle): ?array
    {
        $yearStart = now()->subYear()->startOfDay();
        $vehicleTotal = $this->getVehicleCostForPeriod($vehicle->id, $yearStart, now());

        $marketService = app(MarketComparisonService::class);
        $marketData = $marketService->getMarketSegmentData($yearStart);
        $globalAvg = $marketData['global_avg'];

        $mrCount = MaintenanceRequest::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('created_at', '>=', $yearStart)
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
            })
            ->count();

        $orderCount = (int) DB::table('orders')
            ->where('vehicle_id', $vehicle->id)
            ->where('created_at', '>=', $yearStart)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('order_services')->whereColumn('order_services.order_id', 'orders.id');
            })
            ->count();

        $jobsCount = $mrCount + $orderCount;
        if ($jobsCount === 0) {
            return null;
        }

        $segmentAvg = $this->getSegmentAvgForVehicle($vehicle, $marketData['segments'], $globalAvg);
        $marketTotal = $jobsCount * $segmentAvg;

        $percentDiff = $marketTotal > 0 ? round((($vehicleTotal - $marketTotal) / $marketTotal) * 100, 1) : 0;
        $yearlySaving = max(0, $marketTotal - $vehicleTotal);

        return [
            'vehicle_total' => round($vehicleTotal, 2),
            'market_total' => round($marketTotal, 2),
            'percent_difference' => $percentDiff,
            'yearly_saving' => round($yearlySaving, 2),
        ];
    }

    private function getSegmentAvgForVehicle(Vehicle $vehicle, array $segments, float $globalAvg): float
    {
        $type = strtolower($vehicle->type ?? '');
        $model = strtolower(trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')));
        foreach ($segments as $key => $avg) {
            $parts = explode('|', $key);
            if (($parts[0] ?? '') === $type && ($parts[1] ?? '') === $model) {
                return (float) $avg;
            }
        }
        return $globalAvg;
    }
}
