<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleDailyOdometer;
use App\Models\VehicleMonthlyMileage;
use App\Models\VehicleMileageHistory;
use App\Models\MobileTrackingTrip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VehicleMileageService
{
    private const MARKET_COST_PER_KM = 0.37;

    private const MARKET_AVG_CARD_CACHE_TTL = 300; // 5 minutes

    /**
     * Get total accumulated mileage for a vehicle (never reset, historical sum).
     * Source: vehicle_monthly_mileage.total_km (closed months) + current month from trips/snapshots.
     * For mobile: also sum mobile_tracking_trips for months not yet in vehicle_monthly_mileage.
     */
    public function getAccumulatedMileage(Vehicle $vehicle): float
    {
        $fromMonthly = (float) VehicleMonthlyMileage::where('vehicle_id', $vehicle->id)
            ->sum('total_km');

        if ($vehicle->usesMobileTracking() && $fromMonthly <= 0) {
            $fromMonthly = (float) MobileTrackingTrip::where('vehicle_id', $vehicle->id)
                ->whereNotNull('ended_at')
                ->sum('trip_distance_km');
        }

        // Fallback: sum monthly mileage from all sources (vehicle_locations, fuel_refills, etc.)
        if ($fromMonthly <= 0) {
            $fromMonthly = $this->getAccumulatedMileageFromRawSources($vehicle->id);
        }

        return round($fromMonthly, 2);
    }

    /**
     * Compute accumulated mileage from raw sources when vehicle_monthly_mileage is empty.
     * Uses vehicle_locations (GPS) and fuel_refills - takes the larger of the two.
     */
    private function getAccumulatedMileageFromRawSources(int $vehicleId): float
    {
        $fromLocations = DB::table('vehicle_locations')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->selectRaw('MIN(odometer) as min_odo, MAX(odometer) as max_odo')
            ->first();

        $locKm = 0.0;
        if ($fromLocations && $fromLocations->min_odo !== null) {
            $locKm = max(0, (float) $fromLocations->max_odo - (float) $fromLocations->min_odo);
        }

        $fromFuel = DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->selectRaw('MIN(odometer_km) as min_odo, MAX(odometer_km) as max_odo')
            ->first();

        $fuelKm = 0.0;
        if ($fromFuel && $fromFuel->min_odo !== null) {
            $fuelKm = max(0, (float) $fromFuel->max_odo - (float) $fromFuel->min_odo);
        }

        return max($locKm, $fuelKm);
    }

    /**
     * Get latest odometer from vehicle_locations or fuel_refills when no mileage history exists.
     */
    private function getLatestOdometerFromRawSources(int $vehicleId): ?array
    {
        $loc = DB::table('vehicle_locations')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->orderByDesc('created_at')
            ->first(['odometer', 'created_at']);

        $fuel = DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->orderByDesc('refilled_at')
            ->first(['odometer_km', 'refilled_at']);

        $current = null;
        if ($loc && $fuel) {
            $current = Carbon::parse($loc->created_at)->gte(Carbon::parse($fuel->refilled_at))
                ? (float) $loc->odometer
                : (float) $fuel->odometer_km;
        } elseif ($loc) {
            $current = (float) $loc->odometer;
        } elseif ($fuel) {
            $current = (float) $fuel->odometer_km;
        }

        if ($current === null) {
            return null;
        }

        return [
            'current' => $current,
            'previous' => null,
            'total' => 0,
        ];
    }

    /**
     * Get monthly mileage for a vehicle (specific month/year).
     * Unified: SUM of daily differences from vehicle_mileage_history.
     * Falls back to vehicle_monthly_mileage, then mobile_tracking_trips for backward compatibility.
     */
    public function getMonthlyMileage(Vehicle $vehicle, int $month, int $year): float
    {
        $fromSnapshot = VehicleMonthlyMileage::where('vehicle_id', $vehicle->id)
            ->where('month', $month)
            ->where('year', $year)
            ->value('total_km');

        if ($fromSnapshot !== null && (float) $fromSnapshot > 0) {
            return round((float) $fromSnapshot, 2);
        }

        $start = Carbon::createFromDate($year, $month, 1)->startOfDay()->toDateString();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
        $fromHistory = (float) VehicleMileageHistory::where('vehicle_id', $vehicle->id)
            ->whereBetween('recorded_date', [$start, $end])
            ->sum('calculated_difference');

        if ($fromHistory > 0) {
            return round($fromHistory, 2);
        }

        if ($vehicle->usesMobileTracking()) {
            $startDt = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDt = $startDt->copy()->endOfMonth();
            $tripSum = (float) MobileTrackingTrip::where('vehicle_id', $vehicle->id)
                ->whereNotNull('ended_at')
                ->whereBetween('ended_at', [$startDt, $endDt])
                ->sum('trip_distance_km');
            if ($tripSum > 0) {
                return round($tripSum, 2);
            }
        }

        // Fallback: compute from vehicle_daily_odometer (daily stored odometer for all cars)
        $fromDaily = $this->getMonthlyMileageFromDailyOdometer($vehicle->id, $month, $year);
        if ($fromDaily > 0) {
            return round($fromDaily, 2);
        }

        // Fallback: vehicle_locations (GPS odometer) - MIN/MAX in month
        $fromLocations = $this->getMonthlyMileageFromVehicleLocations($vehicle->id, $month, $year);
        if ($fromLocations > 0) {
            return round($fromLocations, 2);
        }

        // Fallback: fuel_refills (odometer at refill) - MIN/MAX in month
        $fromFuel = $this->getMonthlyMileageFromFuelRefills($vehicle->id, $month, $year);
        if ($fromFuel > 0) {
            return round($fromFuel, 2);
        }

        return 0.0;
    }

    /**
     * Compute monthly mileage from vehicle_daily_odometer: SUM of (current - previous) per day.
     * Uses the odometer values stored every day for all cars.
     */
    private function getMonthlyMileageFromDailyOdometer(int $vehicleId, int $month, int $year): float
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay()->toDateString();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $records = VehicleDailyOdometer::where('vehicle_id', $vehicleId)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get(['date', 'odometer_km']);

        if ($records->count() < 2) {
            return 0.0;
        }

        $total = 0.0;
        $prev = (float) $records->first()->odometer_km;
        foreach ($records->skip(1) as $r) {
            $curr = (float) $r->odometer_km;
            $total += max(0, $curr - $prev);
            $prev = $curr;
        }
        return $total;
    }

    /**
     * Compute monthly mileage from vehicle_locations (GPS): MAX(odometer) - MIN(odometer) in month.
     */
    private function getMonthlyMileageFromVehicleLocations(int $vehicleId, int $month, int $year): float
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $range = DB::table('vehicle_locations')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->selectRaw('MIN(odometer) as min_odo, MAX(odometer) as max_odo')
            ->first();

        if (!$range || $range->min_odo === null) {
            return 0.0;
        }

        return max(0, (float) $range->max_odo - (float) $range->min_odo);
    }

    /**
     * Compute monthly mileage from fuel_refills: MAX(odometer_km) - MIN(odometer_km) in month.
     */
    private function getMonthlyMileageFromFuelRefills(int $vehicleId, int $month, int $year): float
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $range = DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('refilled_at', [$monthStart, $monthEnd])
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->selectRaw('MIN(odometer_km) as min_odo, MAX(odometer_km) as max_odo')
            ->first();

        if (!$range || $range->min_odo === null) {
            return 0.0;
        }

        return max(0, (float) $range->max_odo - (float) $range->min_odo);
    }

    /**
     * Get current month mileage for vehicle.
     */
    public function getCurrentMonthMileage(Vehicle $vehicle): float
    {
        return $this->getMonthlyMileage($vehicle, (int) now()->month, (int) now()->year);
    }

    /**
     * Get monthly mileage history for vehicle (last 12 months).
     */
    public function getMonthlyHistory(Vehicle $vehicle, int $months = 12): array
    {
        $result = [];
        for ($i = 0; $i < $months; $i++) {
            $date = now()->subMonths($i);
            $result[] = [
                'month' => $date->month,
                'year' => $date->year,
                'month_label' => $date->translatedFormat('M Y'),
                'total_km' => $this->getMonthlyMileage($vehicle, $date->month, $date->year),
            ];
        }
        return $result;
    }

    /**
     * Get company total accumulated mileage (all vehicles).
     */
    public function getCompanyAccumulatedMileage(int $companyId): float
    {
        $vehicleIds = Vehicle::where('company_id', $companyId)->pluck('id');
        $total = 0.0;
        foreach ($vehicleIds as $vid) {
            $v = Vehicle::find($vid);
            if ($v) {
                $total += $this->getAccumulatedMileage($v);
            }
        }
        return round($total, 2);
    }

    /**
     * Get company total monthly mileage (all vehicles, for a given month).
     *
     * Sums odometer distance traveled by all company vehicles in the given month.
     * Uses vehicle_monthly_mileage when available, with fallbacks to vehicle_mileage_history,
     * vehicle_daily_odometer, vehicle_locations, fuel_refills per vehicle.
     *
     * @return float Total kilometers (0 if no vehicles)
     */
    public function getCompanyMonthlyMileage(int $companyId, int $month, int $year): float
    {
        $vehicles = Vehicle::where('company_id', $companyId)->get();
        if ($vehicles->isEmpty()) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($vehicles as $vehicle) {
            $total += $this->getMonthlyMileage($vehicle, $month, $year);
        }

        return round($total, 2);
    }

    /**
     * Calculate estimated market cost: actual_distance × 0.37 SAR.
     * Uses mileage from vehicle_mileage_history.calculated_difference (baseline = 0).
     */
    public function getEstimatedMarketCost(float $totalMonthlyMileageKm): float
    {
        $rate = (float) config('servx.market_avg_per_km', self::MARKET_COST_PER_KM);
        return round($totalMonthlyMileageKm * $rate, 2);
    }

    /**
     * Get market average cost card data for dashboard.
     *
     * Formula: Market Average Cost = Total Vehicle Mileage × 0.37 (SAR/km)
     *
     * - Total Vehicle Mileage = SUM(odometer distance for all company vehicles in the month)
     * - Rate 0.37 is configurable via config('servx.market_avg_per_km')
     * - Returns 0 when company has no vehicles
     *
     * @param  int  $companyId  Company ID
     * @param  int|null  $month  Selected month (1-12). Default: current month
     * @param  int|null  $year  Selected year. Default: current year
     * @return array{value: float, trend: string, total_mileage_km: float}
     */
    public function getMarketAverageCostCardData(int $companyId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? (int) now()->month;
        $year = $year ?? (int) now()->year;
        $cacheKey = "market_avg_cost_card_{$companyId}_{$year}-{$month}";

        return Cache::remember($cacheKey, self::MARKET_AVG_CARD_CACHE_TTL, function () use ($companyId, $month, $year) {
            $rate = (float) config('servx.market_avg_per_km', self::MARKET_COST_PER_KM);

            // Total Vehicle Mileage = SUM(odometer distance for all cars in the month)
            $totalMileageKm = $this->getCompanyMonthlyMileage($companyId, $month, $year);

            // Market Average Cost = Total Vehicle Mileage × 0.37 (edge case: no vehicles => 0)
            $marketAverageCost = round($totalMileageKm * $rate, 2);

            // Trend: compare with previous month
            $lastMonth = Carbon::createFromDate($year, $month, 1)->subMonth();
            $lastMonthMileage = $this->getCompanyMonthlyMileage($companyId, (int) $lastMonth->month, (int) $lastMonth->year);
            $lastMonthCost = round($lastMonthMileage * $rate, 2);

            $trend = 'stable';
            if ($marketAverageCost > $lastMonthCost) {
                $trend = 'up';
            } elseif ($marketAverageCost < $lastMonthCost) {
                $trend = 'down';
            }

            return [
                'value' => $marketAverageCost,
                'trend' => $trend,
                'total_mileage_km' => $totalMileageKm,
            ];
        });
    }

    /**
     * Get mileage history for a vehicle (structured history table).
     * Returns records with: vehicle_id, tracking_type, previous_reading, current_reading, calculated_difference, timestamp.
     * Falls back to fuel_refills and vehicle_locations when vehicle_mileage_history is empty.
     */
    public function getMileageHistory(Vehicle $vehicle, int $limit = 50): \Illuminate\Support\Collection
    {
        $history = VehicleMileageHistory::where('vehicle_id', $vehicle->id)
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        if ($history->isNotEmpty()) {
            return $history;
        }

        return $this->getRawMileageHistoryFromSources($vehicle->id, $limit);
    }

    /**
     * Build mileage history from fuel_refills and vehicle_locations when vehicle_mileage_history is empty.
     */
    private function getRawMileageHistoryFromSources(int $vehicleId, int $limit): \Illuminate\Support\Collection
    {
        $entries = collect();

        $fuelRefills = DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->orderBy('refilled_at')
            ->get(['refilled_at', 'odometer_km']);

        $prevOdo = null;
        foreach ($fuelRefills as $f) {
            $curr = (float) $f->odometer_km;
            $entries->push((object) [
                'recorded_date' => Carbon::parse($f->refilled_at),
                'tracking_type' => 'fuel_refill',
                'previous_reading' => $prevOdo,
                'current_reading' => $curr,
                'calculated_difference' => $prevOdo !== null ? max(0, $curr - $prevOdo) : 0,
            ]);
            $prevOdo = $curr;
        }

        $locations = DB::table('vehicle_locations')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->orderBy('created_at')
            ->get(['created_at', 'odometer']);

        $prevOdo = null;
        foreach ($locations as $loc) {
            $curr = (float) $loc->odometer;
            $entries->push((object) [
                'recorded_date' => Carbon::parse($loc->created_at),
                'tracking_type' => 'gps',
                'previous_reading' => $prevOdo,
                'current_reading' => $curr,
                'calculated_difference' => $prevOdo !== null ? max(0, $curr - $prevOdo) : 0,
            ]);
            $prevOdo = $curr;
        }

        return $entries
            ->sortByDesc(fn($e) => $e->recorded_date?->format('Y-m-d H:i:s'))
            ->take($limit)
            ->values();
    }

    /**
     * Get mileage summaries for multiple vehicles (batch, optimized for fleet table).
     */
    public function getVehicleMileageSummariesForVehicles(iterable $vehicles): array
    {
        $items = $vehicles instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
            ? $vehicles->getCollection()
            : collect($vehicles);
        $ids = $items->pluck('id')->filter()->unique()->values()->all();
        if (empty($ids)) {
            return [];
        }

        $latestByVehicle = VehicleMileageHistory::whereIn('vehicle_id', $ids)
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->get()
            ->unique('vehicle_id')
            ->keyBy('vehicle_id');

        $result = [];
        foreach ($ids as $vid) {
            $latest = $latestByVehicle->get($vid);
            if ($latest) {
                $result[$vid] = [
                    'current_mileage' => (float) $latest->current_reading,
                    'previous_mileage' => $latest->previous_reading !== null ? (float) $latest->previous_reading : null,
                    'total_distance' => (float) $latest->calculated_difference,
                ];
            } else {
                $lastDaily = \App\Models\VehicleDailyOdometer::where('vehicle_id', $vid)->orderByDesc('date')->first();
                if (!$lastDaily) {
                    $fromRaw = $this->getLatestOdometerFromRawSources($vid);
                    $result[$vid] = $fromRaw
                        ? ['current_mileage' => $fromRaw['current'], 'previous_mileage' => $fromRaw['previous'], 'total_distance' => $fromRaw['total']]
                        : ['current_mileage' => 0, 'previous_mileage' => null, 'total_distance' => 0];
                } else {
                    $current = (float) $lastDaily->odometer_km;
                    $prev = \App\Models\VehicleDailyOdometer::where('vehicle_id', $vid)->where('date', '<', $lastDaily->date)->orderByDesc('date')->value('odometer_km');
                    $prev = $prev !== null ? (float) $prev : null;
                    $result[$vid] = [
                        'current_mileage' => $current,
                        'previous_mileage' => $prev,
                        'total_distance' => $prev !== null ? max(0, $current - $prev) : 0,
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * Get current/previous/total for vehicle (unified for both GPS and manual).
     */
    public function getVehicleMileageSummary(Vehicle $vehicle): array
    {
        $latest = VehicleMileageHistory::where('vehicle_id', $vehicle->id)
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->first();

        if (!$latest) {
            $lastDaily = \App\Models\VehicleDailyOdometer::where('vehicle_id', $vehicle->id)
                ->orderByDesc('date')
                ->first();
            if (!$lastDaily) {
                $fromRaw = $this->getLatestOdometerFromRawSources($vehicle->id);
                if ($fromRaw) {
                    return [
                        'current_mileage' => $fromRaw['current'],
                        'previous_mileage' => $fromRaw['previous'],
                        'total_distance' => $fromRaw['total'],
                    ];
                }
                return ['current_mileage' => 0, 'previous_mileage' => null, 'total_distance' => 0];
            }
            $current = (float) $lastDaily->odometer_km;
            $prev = \App\Models\VehicleDailyOdometer::where('vehicle_id', $vehicle->id)
                ->where('date', '<', $lastDaily->date)
                ->orderByDesc('date')
                ->value('odometer_km');
            $prev = $prev !== null ? (float) $prev : null;
            return [
                'current_mileage' => $current,
                'previous_mileage' => $prev,
                'total_distance' => $prev !== null ? max(0, $current - $prev) : 0,
            ];
        }

        return [
            'current_mileage' => (float) $latest->current_reading,
            'previous_mileage' => $latest->previous_reading !== null ? (float) $latest->previous_reading : null,
            'total_distance' => (float) $latest->calculated_difference,
        ];
    }

    /**
     * Get company monthly summary (total mileage, estimated market cost).
     */
    public function getCompanyMonthlySummary(int $companyId, int $months = 6): array
    {
        $result = [];
        for ($i = 0; $i < $months; $i++) {
            $date = now()->subMonths($i);
            $monthlyKm = $this->getCompanyMonthlyMileage($companyId, $date->month, $date->year);
            $marketCost = $this->getEstimatedMarketCost($monthlyKm);
            $result[] = [
                'month' => $date->month,
                'year' => $date->year,
                'month_label' => $date->translatedFormat('M Y'),
                'total_monthly_mileage_km' => $monthlyKm,
                'estimated_market_cost_sar' => $marketCost,
                'average_market_operating_cost_sar' => config('servx.market_avg_per_km', self::MARKET_COST_PER_KM),
            ];
        }
        return $result;
    }
}
