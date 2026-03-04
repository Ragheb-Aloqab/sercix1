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

        return round($fromMonthly, 2);
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
     */
    public function getCompanyMonthlyMileage(int $companyId, int $month, int $year): float
    {
        $vehicleIds = Vehicle::where('company_id', $companyId)->pluck('id');
        $total = 0.0;
        foreach ($vehicleIds as $vid) {
            $v = Vehicle::find($vid);
            if ($v) {
                $total += $this->getMonthlyMileage($v, $month, $year);
            }
        }
        return round($total, 2);
    }

    /**
     * Calculate estimated market cost: total_monthly_mileage * 0.37 SAR.
     */
    public function getEstimatedMarketCost(float $totalMonthlyMileageKm): float
    {
        $rate = (float) config('servx.market_avg_per_km', self::MARKET_COST_PER_KM);
        return round($totalMonthlyMileageKm * $rate, 2);
    }

    /**
     * Get market average cost card data for dashboard.
     * Value = Total Monthly Mileage (current month, all vehicles) * 0.37 SAR.
     * Trend = up if higher than last month, down if lower, stable if equal.
     * Sources: GPS (vehicle_locations), mobile trips (end_odometer - start_odometer).
     */
    public function getMarketAverageCostCardData(int $companyId): array
    {
        $cacheKey = "market_avg_cost_card_{$companyId}_" . now()->format('Y-m');
        return Cache::remember($cacheKey, self::MARKET_AVG_CARD_CACHE_TTL, function () use ($companyId) {
            $rate = (float) config('servx.market_avg_per_km', self::MARKET_COST_PER_KM);
            $currentMonth = (int) now()->month;
            $currentYear = (int) now()->year;
            $lastMonth = now()->subMonth();

            $currentMileage = $this->getCompanyMonthlyMileage($companyId, $currentMonth, $currentYear);
            $lastMileage = $this->getCompanyMonthlyMileage($companyId, (int) $lastMonth->month, (int) $lastMonth->year);

            $currentCost = round($currentMileage * $rate, 2);
            $lastCost = round($lastMileage * $rate, 2);

            $trend = 'stable';
            if ($currentCost > $lastCost) {
                $trend = 'up';
            } elseif ($currentCost < $lastCost) {
                $trend = 'down';
            }

            return [
                'value' => $currentCost,
                'trend' => $trend,
            ];
        });
    }

    /**
     * Get mileage history for a vehicle (structured history table).
     * Returns records with: vehicle_id, tracking_type, previous_reading, current_reading, calculated_difference, timestamp.
     */
    public function getMileageHistory(Vehicle $vehicle, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return VehicleMileageHistory::where('vehicle_id', $vehicle->id)
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get mileage summaries for multiple vehicles (batch, optimized for fleet table).
     */
    public function getVehicleMileageSummariesForVehicles(iterable $vehicles): array
    {
        $ids = collect($vehicles)->pluck('id')->filter()->unique()->values()->all();
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
                    $result[$vid] = ['current_mileage' => 0, 'previous_mileage' => null, 'total_distance' => 0];
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
