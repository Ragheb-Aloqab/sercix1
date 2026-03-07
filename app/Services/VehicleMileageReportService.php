<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleDailyOdometer;
use App\Models\VehicleMileageHistory;
use App\Models\VehicleMonthlyMileage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VehicleMileageReportService
{
    public const STATUS_NORMAL = 'normal';
    public const STATUS_HIGH_USAGE = 'high_usage';
    public const STATUS_NO_UPDATE = 'no_update';
    public const STATUS_DATA_ANOMALY = 'data_anomaly';

    /** High usage threshold: km per month (configurable) */
    protected int $highUsageThresholdKm = 2000;

    /** No-update: days without reading to consider "No Update" */
    protected int $noUpdateDays = 30;

    /**
     * Get mileage report for all company vehicles.
     *
     * @return array{rows: array, summary: array}
     */
    public function getReport(
        int $companyId,
        ?Carbon $from = null,
        ?Carbon $to = null,
        ?int $vehicleId = null,
        ?int $branchId = null,
        string $sortBy = 'total_distance',
        string $sortDir = 'desc'
    ): array {
        $from = $from ?? now()->startOfMonth();
        $to = $to ?? now()->endOfDay();

        $query = Vehicle::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('branch');

        if ($vehicleId) {
            $query->where('id', $vehicleId);
        }
        if ($branchId) {
            $query->where('company_branch_id', $branchId);
        }

        $vehicles = $query->get();
        $rows = [];

        foreach ($vehicles as $vehicle) {
            $row = $this->getVehicleMileageRow($vehicle, $from, $to);
            $rows[] = $row;
        }

        // Sort
        usort($rows, function ($a, $b) use ($sortBy, $sortDir) {
            $va = $a[$sortBy] ?? 0;
            $vb = $b[$sortBy] ?? 0;
            if (is_numeric($va) && is_numeric($vb)) {
                $cmp = $va <=> $vb;
            } else {
                $cmp = strcmp((string) $va, (string) $vb);
            }
            return $sortDir === 'desc' ? -$cmp : $cmp;
        });

        $summary = $this->computeSummary($rows, $from, $to);

        return [
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    protected function getVehicleMileageRow(Vehicle $vehicle, Carbon $from, Carbon $to): array
    {
        $currentOdo = $this->getLatestOdometer($vehicle->id, $to);
        $previousOdo = $this->getLatestOdometer($vehicle->id, $from->copy()->subDay());
        $lastUpdate = $this->getLastOdometerDate($vehicle->id);

        $currentMileage = (float) ($currentOdo ?? 0);
        $previousMileage = $previousOdo !== null ? (float) $previousOdo : null;
        $lastDayStartOdo = $this->getLatestOdometer($vehicle->id, $to->copy()->subDay());

        // Period distance: current - previous when both exist; else try MIN/MAX within period
        $rawDifference = $previousMileage !== null
            ? $currentMileage - $previousMileage
            : $this->getPeriodDistanceFromSources($vehicle->id, $from, $to);

        $hasAnomaly = $previousMileage !== null && $previousMileage > 0 && $rawDifference < 0;

        // For status and summary: use 0 when anomaly (negative distance is invalid for usage metrics)
        $totalDistanceForMetrics = $hasAnomaly ? 0.0 : max(0, $rawDifference);
        $status = $hasAnomaly
            ? self::STATUS_DATA_ANOMALY
            : $this->determineStatus($lastUpdate, $totalDistanceForMetrics, $from, $to);

        // Daily distance: actual distance on the last day of the period
        // 1) Boundary: odometer at end of last day - odometer at start of last day
        // 2) Fallback: MIN/MAX within last day from vehicle_locations, fuel_refills, vehicle_mileage_history
        // 3) Fallback: avg daily when period has data but no last-day granularity
        $dailyDistance = 0.0;
        if ($lastDayStartOdo !== null) {
            $dailyDistance = max(0, $currentMileage - (float) $lastDayStartOdo);
        } else {
            $dailyDistance = $this->getLastDayDistanceFromSources($vehicle->id, $to);
            if ($dailyDistance <= 0 && $rawDifference > 0) {
                $daysInPeriod = max(1, $from->diffInDays($to));
                $dailyDistance = round($rawDifference / $daysInPeriod, 1);
            }
        }

        return [
            'vehicle_id' => $vehicle->id,
            'plate_number' => $vehicle->plate_number ?? '-',
            'vehicle_name' => $vehicle->display_name,
            'branch_name' => $vehicle->branch?->name ?? '-',
            'current_mileage' => $currentMileage,
            'daily_odometer' => round($dailyDistance, 1),
            'total_distance' => $rawDifference,
            'has_anomaly' => $hasAnomaly,
            'last_update_date' => $lastUpdate?->format('Y-m-d H:i') ?? '-',
            'status' => $status,
        ];
    }

    /**
     * Get period distance when boundary readings are missing.
     * Uses vehicle_mileage_history, then MIN/MAX within period from vehicle_locations and fuel_refills.
     */
    protected function getPeriodDistanceFromSources(int $vehicleId, Carbon $from, Carbon $to): float
    {
        $start = $from->toDateString();
        $end = $to->toDateString();
        $fromDt = $from->copy()->startOfDay();
        $toDt = $to->copy()->endOfDay();

        // vehicle_mileage_history: SUM of calculated_difference in period
        $fromHistory = (float) VehicleMileageHistory::where('vehicle_id', $vehicleId)
            ->whereBetween('recorded_date', [$start, $end])
            ->sum('calculated_difference');
        if ($fromHistory > 0) {
            return $fromHistory;
        }

        // vehicle_locations: MAX - MIN in period
        $locRange = DB::table('vehicle_locations')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->selectRaw('MIN(odometer) as min_odo, MAX(odometer) as max_odo')
            ->first();
        if ($locRange && $locRange->min_odo !== null) {
            return max(0, (float) $locRange->max_odo - (float) $locRange->min_odo);
        }

        // fuel_refills: MAX - MIN in period
        $fuelRange = DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('refilled_at', [$fromDt, $toDt])
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->selectRaw('MIN(odometer_km) as min_odo, MAX(odometer_km) as max_odo')
            ->first();
        if ($fuelRange && $fuelRange->min_odo !== null) {
            return max(0, (float) $fuelRange->max_odo - (float) $fuelRange->min_odo);
        }

        // vehicle_daily_odometer: SUM of day-to-day diffs within period
        $dailyKm = $this->getPeriodDistanceFromDailyOdometer($vehicleId, $from, $to);
        if ($dailyKm > 0) {
            return $dailyKm;
        }

        return 0.0;
    }

    /**
     * Get distance for the last day of the period when boundary readings are missing.
     * Uses MIN/MAX within the last day from vehicle_locations, fuel_refills, vehicle_mileage_history.
     */
    protected function getLastDayDistanceFromSources(int $vehicleId, Carbon $to): float
    {
        $lastDayStart = $to->copy()->startOfDay();
        $lastDayEnd = $to->copy()->endOfDay();
        $dateStr = $to->toDateString();

        // vehicle_mileage_history: SUM for the last day only
        $fromHistory = (float) VehicleMileageHistory::where('vehicle_id', $vehicleId)
            ->where('recorded_date', $dateStr)
            ->sum('calculated_difference');
        if ($fromHistory > 0) {
            return $fromHistory;
        }

        // vehicle_locations: MAX - MIN on the last day
        $locRange = DB::table('vehicle_locations')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('created_at', [$lastDayStart, $lastDayEnd])
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->selectRaw('MIN(odometer) as min_odo, MAX(odometer) as max_odo')
            ->first();
        if ($locRange && $locRange->min_odo !== null) {
            return max(0, (float) $locRange->max_odo - (float) $locRange->min_odo);
        }

        // fuel_refills: MAX - MIN on the last day
        $fuelRange = DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('refilled_at', [$lastDayStart, $lastDayEnd])
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->selectRaw('MIN(odometer_km) as min_odo, MAX(odometer_km) as max_odo')
            ->first();
        if ($fuelRange && $fuelRange->min_odo !== null) {
            return max(0, (float) $fuelRange->max_odo - (float) $fuelRange->min_odo);
        }

        return 0.0;
    }

    /**
     * Compute period distance from vehicle_daily_odometer: SUM of (current - previous) per day in period.
     */
    protected function getPeriodDistanceFromDailyOdometer(int $vehicleId, Carbon $from, Carbon $to): float
    {
        $start = $from->toDateString();
        $end = $to->toDateString();

        $records = VehicleDailyOdometer::where('vehicle_id', $vehicleId)
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
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
     * Get daily odometer from vehicle_daily_odometer for the given date or closest before.
     */
    protected function getDailyOdometerAtDate(int $vehicleId, Carbon $date): ?float
    {
        $dateStr = $date->toDateString();
        $rec = VehicleDailyOdometer::where('vehicle_id', $vehicleId)
            ->where('date', '<=', $dateStr)
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->orderByDesc('date')
            ->first();
        return $rec ? (float) $rec->odometer_km : null;
    }

    protected function getLatestOdometer(int $vehicleId, Carbon $before): ?float
    {
        $date = $before->toDateString();

        $fromDaily = VehicleDailyOdometer::where('vehicle_id', $vehicleId)
            ->where('date', '<=', $date)
            ->orderByDesc('date')
            ->value('odometer_km');

        if ($fromDaily !== null) {
            return (float) $fromDaily;
        }

        $y = (int) substr($date, 0, 4);
        $m = (int) substr($date, 5, 2);
        $monthlyRec = VehicleMonthlyMileage::where('vehicle_id', $vehicleId)
            ->where(function ($q) use ($y, $m) {
                $q->where('year', '<', $y)
                    ->orWhere(function ($q2) use ($y, $m) {
                        $q2->where('year', $y)->where('month', '<=', $m);
                    });
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();
        if ($monthlyRec) {
            $odo = $monthlyRec->end_odometer ?? $monthlyRec->start_odometer;
            if ($odo !== null) {
                return (float) $odo;
            }
        }

        $fromFuel = DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->where('refilled_at', '<=', $before->endOfDay())
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->orderByDesc('refilled_at')
            ->value('odometer_km');

        if ($fromFuel !== null) {
            return (float) $fromFuel;
        }

        $fromLocations = DB::table('vehicle_locations')
            ->where('vehicle_id', $vehicleId)
            ->where('created_at', '<=', $before->endOfDay())
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->orderByDesc('tracker_timestamp')
            ->value('odometer');

        if ($fromLocations !== null) {
            return (float) $fromLocations;
        }

        return null;
    }

    protected function getLastOdometerDate(int $vehicleId): ?Carbon
    {
        $fromDaily = VehicleDailyOdometer::where('vehicle_id', $vehicleId)
            ->orderByDesc('date')
            ->value('date');

        if ($fromDaily) {
            return Carbon::parse($fromDaily);
        }

        $fromFuel = DB::table('fuel_refills')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('odometer_km')
            ->orderByDesc('refilled_at')
            ->value('refilled_at');

        if ($fromFuel) {
            return Carbon::parse($fromFuel);
        }

        $fromLoc = DB::table('vehicle_locations')
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('odometer')
            ->orderByDesc('tracker_timestamp')
            ->value('tracker_timestamp');

        if ($fromLoc) {
            return Carbon::parse($fromLoc);
        }

        return null;
    }

    protected function determineStatus(?Carbon $lastUpdate, float $totalDistance, Carbon $from, Carbon $to): string
    {
        if (!$lastUpdate || $lastUpdate->diffInDays(now()) > $this->noUpdateDays) {
            return self::STATUS_NO_UPDATE;
        }

        $daysInPeriod = max(1, $from->diffInDays($to));
        $kmPerDay = $totalDistance / $daysInPeriod;
        $extrapolatedMonthly = $kmPerDay * 30;

        if ($extrapolatedMonthly >= $this->highUsageThresholdKm) {
            return self::STATUS_HIGH_USAGE;
        }

        return self::STATUS_NORMAL;
    }

    protected function computeSummary(array $rows, Carbon $from, Carbon $to): array
    {
        $totalVehicles = count($rows);
        // Exclude negative distances (anomalies) from total mileage
        $totalMileage = array_sum(array_map(fn ($r) => max(0, $r['total_distance'] ?? 0), $rows));
        $avgMileage = $totalVehicles > 0 ? round($totalMileage / $totalVehicles, 2) : 0;

        return [
            'total_vehicles' => $totalVehicles,
            'total_mileage_this_period' => round($totalMileage, 2),
            'average_mileage_per_vehicle' => $avgMileage,
        ];
    }
}
