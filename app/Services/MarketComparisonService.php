<?php

namespace App\Services;

use App\Models\Company;
use App\Models\MaintenanceRequest;
use App\Models\Quotation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MarketComparisonService
{
    private const CACHE_TTL = 300; // 5 minutes for real-time recalculation
    private const MONTHS_BACK = 6;
    private const INTERNAL_PRECISION = 4;

    /**
     * Get market comparison data for company dashboard.
     * Uses per 1,000 KM (per mille) comparison: Actual Cost vs Market Average per 1,000 KM.
     * Cached for 24 hours. Optional months parameter for date range (6 or 12).
     */
    public function getComparisonData(Company $company, int $months = 6): array
    {
        $months = in_array($months, [6, 12], true) ? $months : self::MONTHS_BACK;
        $cacheKey = "market_comparison_{$company->id}_{$months}";
        return Cache::remember($cacheKey, self::CACHE_TTL, fn () => $this->computeComparisonData($company, $months));
    }

    private function computeComparisonData(Company $company, int $months = 6): array
    {
        $since = now()->subMonths($months)->startOfDay();

        // Step 1 & 2: Total Kilometers + Total Expenses (aggregated queries)
        $totalKilometers = $this->getCompanyTotalKilometers($company->id, $since);
        $maintenanceTotal = $this->getCompanyMaintenanceTotal($company->id, $since);
        $fuelTotal = $this->getCompanyFuelTotal($company->id, $since);
        $totalExpenses = round($maintenanceTotal + $fuelTotal, self::INTERNAL_PRECISION);

        // Market Average = Total Fleet Mileage × 0.37 SAR (unified formula)
        $marketRatePerKm = (float) config('servx.market_avg_per_km', 0.37);
        $marketAverageTotalCost = round($totalKilometers * $marketRatePerKm, self::INTERNAL_PRECISION);

        $marketData = $this->getMarketAverageBySegment($since);

        // Step 4: Actual Cost Per KM (weighted fleet average)
        $actualCostPerKm = $totalKilometers > 0
            ? round($totalExpenses / $totalKilometers, self::INTERNAL_PRECISION)
            : 0.0;

        // Step 5: Difference Total = Total Expenses − Market Average Total Cost
        $totalDifference = round($totalExpenses - $marketAverageTotalCost, self::INTERNAL_PRECISION);

        // Difference Per KM = Actual Cost Per KM − Market Cost Per KM (effective)
        $marketCostPerKm = $totalKilometers > 0 ? ($marketAverageTotalCost / $totalKilometers) : $marketRatePerKm;
        $differencePerKm = round($actualCostPerKm - $marketCostPerKm, self::INTERNAL_PRECISION);

        // Deviation % = (Actual − Market) ÷ Market × 100 (for summary card: +67% above, -15% below)
        $deviationPercent = $marketAverageTotalCost > 0
            ? round((($totalExpenses - $marketAverageTotalCost) / $marketAverageTotalCost) * 100, self::INTERNAL_PRECISION)
            : 0.0;

        // Cost ratio = Actual ÷ Market × 100 (for gauge: 0–200% scale, 100% = equal to market)
        $costRatio = $marketAverageTotalCost > 0
            ? round(($totalExpenses / $marketAverageTotalCost) * 100, self::INTERNAL_PRECISION)
            : ($totalExpenses > 0 ? 100.0 : 0.0);

        // Top 3 expensive & top 3 saving service types (marketData already fetched above)
        $companyJobs = $this->getCompanyMaintenanceJobs($company->id, $since);
        $serviceTypeAnalysis = $this->getServiceTypeAnalysis($company->id, $since, $marketData);

        return [
            'company_total' => round($totalExpenses, 2),
            'market_average' => round($marketAverageTotalCost, 2),
            'market_avg_per_km' => round($totalKilometers > 0 ? ($marketAverageTotalCost / $totalKilometers) : $marketRatePerKm, 2),
            'actual_cost_per_km' => round($actualCostPerKm, 2),
            'difference_per_km' => round($differencePerKm, 2),
            'total_difference' => round($totalDifference, 2),
            'deviation_percent' => round($deviationPercent, 2),
            'percent_difference' => round($deviationPercent, 2),
            'cost_ratio' => round($costRatio, 2),
            'total_kilometers' => round($totalKilometers, 2),
            'company_jobs' => $companyJobs,
            'top3_expensive' => $serviceTypeAnalysis['expensive'],
            'top3_saving' => $serviceTypeAnalysis['saving'],
        ];
    }

    private function getCompanyFuelTotal(int $companyId, $since): float
    {
        return (float) DB::table('fuel_refills')
            ->where('company_id', $companyId)
            ->where('refilled_at', '>=', $since)
            ->sum('cost');
    }

    /**
     * Total Kilometers = Sum of vehicle mileage in date range (unified mileage system).
     * Primary: vehicle_monthly_mileage (SUM of daily differences from vehicle_mileage_history).
     * Fallback: fuel_refills, then vehicle_locations.
     */
    private function getCompanyTotalKilometers(int $companyId, $since): float
    {
        $snapshotService = app(\App\Services\MonthlyMileageSnapshotService::class);
        $totalKm = $snapshotService->getCompanyTotalKilometersFromSnapshots($companyId, $since);
        if ($totalKm > 0) {
            return round($totalKm, self::INTERNAL_PRECISION);
        }

        $fuelRanges = DB::table('fuel_refills')
            ->where('company_id', $companyId)
            ->where('refilled_at', '>=', $since)
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->selectRaw('vehicle_id, MAX(odometer_km) - MIN(odometer_km) as km_driven')
            ->groupBy('vehicle_id')
            ->get();
        $totalKm = max(0, (float) $fuelRanges->sum('km_driven'));
        if ($totalKm > 0) {
            return round($totalKm, self::INTERNAL_PRECISION);
        }

        $locationRanges = DB::table('vehicle_locations')
            ->join('vehicles', 'vehicles.id', '=', 'vehicle_locations.vehicle_id')
            ->where('vehicles.company_id', $companyId)
            ->where('vehicle_locations.created_at', '>=', $since)
            ->whereNotNull('vehicle_locations.odometer')
            ->where('vehicle_locations.odometer', '>', 0)
            ->selectRaw('vehicle_locations.vehicle_id, MAX(vehicle_locations.odometer) - MIN(vehicle_locations.odometer) as km_driven')
            ->groupBy('vehicle_locations.vehicle_id')
            ->get();

        return max(0, round((float) $locationRanges->sum('km_driven'), self::INTERNAL_PRECISION));
    }

    private function getCompanyMaintenanceTotal(int $companyId, $since): float
    {
        // From MaintenanceRequest (aggregated SUM)
        $mrTotal = (float) MaintenanceRequest::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')
                    ->orWhereNotNull('final_invoice_amount');
            })
            ->selectRaw('COALESCE(SUM(COALESCE(final_invoice_amount, approved_quote_amount)), 0) as total')
            ->value('total');

        // From Orders (order_services)
        $orderTotal = (float) DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $companyId)
            ->where('orders.created_at', '>=', $since)
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?? 0;

        return $mrTotal + $orderTotal;
    }

    private function getCompanyMaintenanceJobs(int $companyId, $since): int
    {
        $mrCount = MaintenanceRequest::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')
                    ->orWhereNotNull('final_invoice_amount');
            })
            ->count();

        $orderCount = (int) DB::table('orders')
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('order_services')
                    ->whereColumn('order_services.order_id', 'orders.id');
            })
            ->count();

        return $mrCount + $orderCount;
    }

    /**
     * Public: get market segment data (segments + global avg) for vehicle-level use.
     */
    public function getMarketSegmentData($since): array
    {
        return [
            'segments' => $this->getMarketAverageBySegment($since),
            'global_avg' => $this->getGlobalMarketAvgPerJob($since),
        ];
    }

    private function getMarketAverageBySegment($since): array
    {
        $sql = "
            SELECT
                COALESCE(v.type, '') as vehicle_type,
                TRIM(CONCAT(COALESCE(v.make,''), ' ', COALESCE(v.model,''))) as vehicle_model,
                mr.maintenance_type as service_type,
                IFNULL(NULLIF(TRIM(mr.city), ''), 'unknown') as city,
                AVG(q.price) as avg_price
            FROM quotations q
            INNER JOIN maintenance_requests mr ON mr.id = q.maintenance_request_id
            INNER JOIN vehicles v ON v.id = mr.vehicle_id
            WHERE q.submitted_at IS NOT NULL AND q.submitted_at >= ?
            GROUP BY v.type, v.make, v.model, mr.maintenance_type, mr.city
        ";
        $rows = DB::select($sql, [$since]);

        $segments = [];
        foreach ($rows as $r) {
            $key = $this->segmentKey($r->vehicle_type, $r->vehicle_model, $r->service_type, $r->city);
            $segments[$key] = (float) $r->avg_price;
        }
        return $segments;
    }

    private function getGlobalMarketAvgPerJob($since): float
    {
        $result = Quotation::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $since)
            ->selectRaw('AVG(price) as avg_price')
            ->first();
        return (float) ($result->avg_price ?? 0);
    }

    private function getMarketTotalForCompanyJobs(int $companyId, $since, array $marketData): float
    {
        $requests = MaintenanceRequest::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')
                    ->orWhereNotNull('final_invoice_amount');
            })
            ->with('vehicle')
            ->get();

        $globalAvg = $this->getGlobalMarketAvgPerJob($since);
        $total = 0.0;
        foreach ($requests as $r) {
            $vehicle = $r->vehicle;
            $type = $vehicle?->type ?? '';
            $model = trim(($vehicle?->make ?? '') . ' ' . ($vehicle?->model ?? ''));
            $city = $r->city ? trim($r->city) : 'unknown';
            $key = $this->segmentKey($type, $model, $r->maintenance_type, $city);
            $segmentAvg = $marketData[$key] ?? $globalAvg;
            $total += $segmentAvg;
        }

        // Add order-based jobs with global fallback
        $orderCount = (int) DB::table('orders')
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('order_services')
                    ->whereColumn('order_services.order_id', 'orders.id');
            })
            ->count();

        if ($orderCount > 0) {
            $globalAvg = $this->getGlobalMarketAvgPerJob($since);
            $total += $orderCount * $globalAvg;
        }

        return $total;
    }

    private function segmentKey(string $type, string $model, string $serviceType, string $city): string
    {
        return implode('|', [strtolower($type), strtolower(trim($model)), strtolower($serviceType), strtolower(trim($city))]);
    }

    private function getServiceTypeAnalysis(int $companyId, $since, array $marketData): array
    {
        $requests = MaintenanceRequest::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $since)
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')
                    ->orWhereNotNull('final_invoice_amount');
            })
            ->with('vehicle')
            ->get();

        $byType = [];
        foreach ($requests as $r) {
            $type = $r->maintenance_type ?? 'other';
            if (!isset($byType[$type])) {
                $byType[$type] = ['company' => 0, 'market' => 0];
            }
            $cost = (float) ($r->final_invoice_amount ?? $r->approved_quote_amount ?? 0);
            $byType[$type]['company'] += $cost;
            $v = $r->vehicle;
            $key = $this->segmentKey($v?->type ?? '', trim(($v?->make ?? '') . ' ' . ($v?->model ?? '')), $r->maintenance_type, $r->city ? trim($r->city) : 'unknown');
            $byType[$type]['market'] += $marketData[$key] ?? 0;
        }

        $analysis = [];
        foreach ($byType as $serviceType => $data) {
            $diff = $data['company'] - $data['market'];
            $analysis[] = [
                'service_type' => $serviceType,
                'company_total' => $data['company'],
                'market_total' => $data['market'],
                'difference' => $diff,
            ];
        }

        usort($analysis, fn ($a, $b) => $b['difference'] <=> $a['difference']);
        $expensive = array_slice($analysis, 0, 3);
        $saving = array_slice(array_reverse($analysis), 0, 3);

        return ['expensive' => $expensive, 'saving' => $saving];
    }

    /**
     * Monthly comparison: company vs market cost per month.
     * Returns array of [year, month, company_total, market_total] for chart.
     */
    public function getMonthlyComparisonData(Company $company, int $months = 6): array
    {
        $cacheKey = "market_monthly_{$company->id}_{$months}";
        return Cache::remember($cacheKey, self::CACHE_TTL, fn () => $this->computeMonthlyComparison($company, $months));
    }

    private function computeMonthlyComparison(Company $company, int $months): array
    {
        $marketData = $this->getMarketAverageBySegment(now()->subMonths($months)->startOfDay());
        $globalAvg = $this->getGlobalMarketAvgPerJob(now()->subMonths($months)->startOfDay());

        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $companyTotal = $this->getCompanyMaintenanceTotalForPeriod($company->id, $monthStart, $monthEnd);
            $marketTotal = $this->getMarketTotalForCompanyJobsInPeriod($company->id, $monthStart, $monthEnd, $marketData, $globalAvg);

            $out[] = [
                'year' => $date->year,
                'month' => $date->month,
                'month_label' => $date->translatedFormat('M'),
                'company_total' => round($companyTotal, 2),
                'market_total' => round($marketTotal, 2),
            ];
        }
        return $out;
    }

    private function getCompanyMaintenanceTotalForPeriod(int $companyId, $from, $to): float
    {
        $mrTotal = MaintenanceRequest::query()
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
            })
            ->get()
            ->sum(fn ($r) => (float) ($r->final_invoice_amount ?? $r->approved_quote_amount ?? 0));

        $orderTotal = (float) DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $companyId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?? 0;

        return $mrTotal + $orderTotal;
    }

    private function getMarketTotalForCompanyJobsInPeriod(int $companyId, $from, $to, array $marketData, float $globalAvg): float
    {
        $requests = MaintenanceRequest::query()
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($q) {
                $q->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
            })
            ->with('vehicle')
            ->get();

        $total = 0.0;
        foreach ($requests as $r) {
            $v = $r->vehicle;
            $type = $v?->type ?? '';
            $model = trim(($v?->make ?? '') . ' ' . ($v?->model ?? ''));
            $city = $r->city ? trim($r->city) : 'unknown';
            $key = $this->segmentKey($type, $model, $r->maintenance_type, $city);
            $total += $marketData[$key] ?? $globalAvg;
        }

        $orderCount = (int) DB::table('orders')
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('order_services')->whereColumn('order_services.order_id', 'orders.id');
            })
            ->count();

        if ($orderCount > 0) {
            $total += $orderCount * $globalAvg;
        }

        return $total;
    }

    /**
     * Top service center by job count for this company (last 6 months).
     */
    public function getTopServiceCenter(Company $company): ?array
    {
        $sixMonthsAgo = now()->subMonths(self::MONTHS_BACK)->startOfDay();

        $row = MaintenanceRequest::query()
            ->where('company_id', $company->id)
            ->where('created_at', '>=', $sixMonthsAgo)
            ->whereNotNull('approved_center_id')
            ->selectRaw('approved_center_id, COUNT(*) as jobs, COALESCE(SUM(COALESCE(final_invoice_amount, approved_quote_amount)), 0) as total_amount')
            ->groupBy('approved_center_id')
            ->orderByDesc('jobs')
            ->first();

        if (!$row) {
            return null;
        }

        $center = \App\Models\MaintenanceCenter::find($row->approved_center_id);
        return [
            'name' => $center?->name ?? __('company.unknown_center'),
            'jobs' => (int) $row->jobs,
            'total_amount' => round((float) $row->total_amount, 2),
        ];
    }
}
