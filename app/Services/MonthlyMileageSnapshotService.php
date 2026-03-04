<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleMonthlyMileage;
use App\Models\VehicleMileageHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyMileageSnapshotService
{
    /**
     * Capture month-start snapshot. Run at beginning of each month (e.g. 1st at 00:05).
     * - Closes previous month (locks data, sets end_odometer, total_km).
     * - Creates new month record with start_odometer = boundary reading.
     */
    public function captureMonthStartSnapshot(?Carbon $forDate = null): array
    {
        $forDate = $forDate ?? now();
        $prevMonth = $forDate->copy()->subMonth();
        $boundaryReading = $this->getLatestOdometerReadingsAtBoundary($prevMonth->endOfMonth());

        $closed = 0;
        $created = 0;

        foreach ($boundaryReading as $vehicleId => $odometer) {
            $odometer = (float) $odometer;
            if ($odometer <= 0) {
                continue;
            }

            // Close previous month (never recalculate if already closed)
            $prevRecord = VehicleMonthlyMileage::firstOrCreate(
                [
                    'vehicle_id' => $vehicleId,
                    'month' => $prevMonth->month,
                    'year' => $prevMonth->year,
                ],
                ['start_odometer' => $odometer, 'total_km' => 0]
            );

            if (!$prevRecord->is_closed) {
                $monthStart = $prevMonth->copy()->startOfMonth()->toDateString();
                $monthEnd = $prevMonth->copy()->endOfMonth()->toDateString();
                $totalKm = (float) VehicleMileageHistory::where('vehicle_id', $vehicleId)
                    ->whereBetween('recorded_date', [$monthStart, $monthEnd])
                    ->sum('calculated_difference');
                $prevRecord->end_odometer = $odometer;
                $prevRecord->total_km = max(0, $totalKm);
                $prevRecord->is_closed = true;
                $prevRecord->save();
                $closed++;
            }

            // Create new month start snapshot
            $newRecord = VehicleMonthlyMileage::firstOrCreate(
                [
                    'vehicle_id' => $vehicleId,
                    'month' => $forDate->month,
                    'year' => $forDate->year,
                ],
                [
                    'start_odometer' => $odometer,
                    'end_odometer' => null,
                    'total_km' => 0,
                    'is_closed' => false,
                ]
            );

            if ($newRecord->wasRecentlyCreated) {
                $created++;
            }
        }

        return ['closed' => $closed, 'created' => $created];
    }

    /**
     * Update current month's end_odometer and total_km from latest GPS readings.
     * Call periodically (e.g. daily). Never modifies closed months.
     * Creates current month record if missing (first data for vehicle in month).
     */
    public function updateCurrentMonthFromLatest(): array
    {
        $currentMonth = (int) now()->month;
        $currentYear = (int) now()->year;
        $latest = $this->getLatestOdometerReadings();

        $updated = 0;
        foreach ($latest as $vehicleId => $odometer) {
            $odometer = (float) $odometer;
            $record = VehicleMonthlyMileage::firstOrCreate(
                [
                    'vehicle_id' => $vehicleId,
                    'month' => $currentMonth,
                    'year' => $currentYear,
                ],
                ['start_odometer' => $odometer, 'end_odometer' => $odometer, 'total_km' => 0]
            );

            if ($record->is_closed) {
                continue;
            }

            $monthStart = now()->startOfMonth()->toDateString();
            $monthEnd = now()->endOfMonth()->toDateString();
            $totalKm = (float) VehicleMileageHistory::where('vehicle_id', $vehicleId)
                ->whereBetween('recorded_date', [$monthStart, $monthEnd])
                ->sum('calculated_difference');

            $startOdo = (float) ($record->start_odometer ?? 0);
            if ($startOdo > 0 && $odometer < $startOdo) {
                $record->odometer_reset_detected = true;
                $record->start_odometer = $odometer;
            }
            $record->end_odometer = $odometer;
            $record->total_km = max(0, $totalKm);
            $record->save();
            $updated++;
        }

        return ['updated' => $updated];
    }

    /**
     * Get latest odometer per vehicle (from vehicle_locations, fuel_refills, or mobile_tracking_trips).
     */
    private function getLatestOdometerReadings(?Carbon $before = null): array
    {
        $before = $before ?? now();

        // Primary: vehicle_locations (GPS)
        $fromLocations = DB::table('vehicle_locations')
            ->where('created_at', '<=', $before)
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->orderByDesc('created_at')
            ->get(['vehicle_id', 'odometer'])
            ->unique('vehicle_id')
            ->pluck('odometer', 'vehicle_id')
            ->toArray();

        if (!empty($fromLocations)) {
            return $fromLocations;
        }

        // Fallback: fuel_refills
        $fromFuel = DB::table('fuel_refills')
            ->where('refilled_at', '<=', $before)
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->orderByDesc('refilled_at')
            ->get(['vehicle_id', DB::raw('odometer_km as odometer')])
            ->unique('vehicle_id')
            ->pluck('odometer', 'vehicle_id')
            ->toArray();

        if (!empty($fromFuel)) {
            return $fromFuel;
        }

        // Fallback for mobile tracking: latest end_odometer from mobile_tracking_trips
        return DB::table('mobile_tracking_trips')
            ->where('ended_at', '<=', $before)
            ->whereNotNull('end_odometer')
            ->where('end_odometer', '>', 0)
            ->orderByDesc('ended_at')
            ->get(['vehicle_id', DB::raw('end_odometer as odometer')])
            ->unique('vehicle_id')
            ->pluck('odometer', 'vehicle_id')
            ->toArray();
    }

    /**
     * Get latest odometer per vehicle at a specific boundary (e.g. end of previous month).
     */
    private function getLatestOdometerReadingsAtBoundary(Carbon $boundary): array
    {
        $byVehicle = $this->getLatestOdometerReadings($boundary);
        return $byVehicle;
    }

    /**
     * Sum total kilometers for a company within a date range from stored monthly snapshots.
     * Uses vehicle_monthly_mileage (historical data never changes after month closes).
     */
    public function getCompanyTotalKilometersFromSnapshots(int $companyId, Carbon $since): float
    {
        $since = $since->copy()->startOfDay();
        $sinceYear = (int) $since->year;
        $sinceMonth = (int) $since->month;
        $nowYear = (int) now()->year;
        $nowMonth = (int) now()->month;

        $total = (float) DB::table('vehicle_monthly_mileage')
            ->join('vehicles', 'vehicles.id', '=', 'vehicle_monthly_mileage.vehicle_id')
            ->where('vehicles.company_id', $companyId)
            ->whereRaw(
                '(vehicle_monthly_mileage.year > ? OR (vehicle_monthly_mileage.year = ? AND vehicle_monthly_mileage.month >= ?)) AND (vehicle_monthly_mileage.year < ? OR (vehicle_monthly_mileage.year = ? AND vehicle_monthly_mileage.month <= ?))',
                [$sinceYear, $sinceYear, $sinceMonth, $nowYear, $nowYear, $nowMonth]
            )
            ->sum('vehicle_monthly_mileage.total_km');

        return max(0, round($total, 4));
    }
}
