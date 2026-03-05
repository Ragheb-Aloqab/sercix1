<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleDailyOdometer;
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

        // Raw difference: Current - Previous (can be negative when odometer reset or data error)
        $rawDifference = $previousMileage !== null
            ? $currentMileage - $previousMileage
            : 0.0;

        $hasAnomaly = $previousMileage !== null && $previousMileage > 0 && $rawDifference < 0;

        // For status and summary: use 0 when anomaly (negative distance is invalid for usage metrics)
        $totalDistanceForMetrics = $hasAnomaly ? 0.0 : max(0, $rawDifference);
        $status = $hasAnomaly
            ? self::STATUS_DATA_ANOMALY
            : $this->determineStatus($lastUpdate, $totalDistanceForMetrics, $from, $to);

        return [
            'vehicle_id' => $vehicle->id,
            'plate_number' => $vehicle->plate_number ?? '-',
            'vehicle_name' => $vehicle->display_name,
            'branch_name' => $vehicle->branch?->name ?? '-',
            'current_mileage' => $currentMileage,
            'previous_mileage' => $previousMileage ?? 0,
            'total_distance' => $rawDifference,
            'has_anomaly' => $hasAnomaly,
            'last_update_date' => $lastUpdate?->format('Y-m-d H:i') ?? '-',
            'status' => $status,
        ];
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
