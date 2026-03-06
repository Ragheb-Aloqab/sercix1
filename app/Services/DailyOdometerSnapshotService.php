<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleDailyOdometer;
use App\Models\VehicleMileageHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyOdometerSnapshotService
{
    /**
     * Store the last odometer value for each vehicle for the given date.
     * Sources (in order): vehicle_locations (GPS), fuel_refills, mobile_tracking_trips.
     * Also stores mileage history: Actual Mileage = Current Odometer - Previous Odometer.
     */
    public function storeDailySnapshots(?Carbon $forDate = null): array
    {
        $forDate = $forDate ?? now();
        $endOfDay = $forDate->copy()->endOfDay();
        $readings = $this->getLatestOdometerPerVehicle($endOfDay);

        $stored = 0;
        $updated = 0;

        foreach ($readings as $vehicleId => $data) {
            $odo = (float) $data['odometer'];
            if ($odo <= 0) {
                continue;
            }

            $record = VehicleDailyOdometer::updateOrCreate(
                [
                    'vehicle_id' => $vehicleId,
                    'date' => $forDate->toDateString(),
                ],
                [
                    'odometer_km' => $odo,
                    'source' => $data['source'],
                ]
            );

            $record->wasRecentlyCreated ? $stored++ : $updated++;

            // Store mileage history via OdometerTrackingService (first-entry baseline logic)
            $this->storeMileageHistoryEntry($vehicleId, $odo, $data['source'], $forDate);
        }

        return ['stored' => $stored, 'updated' => $updated];
    }

    /**
     * Store a mileage history entry.
     * First entry (no previous): value saved as baseline only, no distance calculated.
     * Second entry and after: Distance = Current - Previous.
     */
    private function storeMileageHistoryEntry(int $vehicleId, float $currentOdometer, string $source, Carbon $forDate): void
    {
        $exists = VehicleMileageHistory::where('vehicle_id', $vehicleId)
            ->where('recorded_date', $forDate->toDateString())
            ->exists();

        if ($exists) {
            return;
        }

        $trackingType = $this->sourceToTrackingType($source);

        // For batch processing we need to get previous from records before this date
        // (OdometerTrackingService uses VehicleDailyOdometer which we just updated)
        $prevRecord = VehicleDailyOdometer::where('vehicle_id', $vehicleId)
            ->where('date', '<', $forDate->toDateString())
            ->orderByDesc('date')
            ->first();

        $previousReading = $prevRecord ? (float) $prevRecord->odometer_km : null;
        $isFirstEntry = $previousReading === null;

        if ($isFirstEntry) {
            $previousReading = $currentOdometer;
            $calculatedDiff = 0.0;
        } else {
            $calculatedDiff = max(0.0, $currentOdometer - $previousReading);
        }

        VehicleMileageHistory::create([
            'vehicle_id' => $vehicleId,
            'tracking_type' => $trackingType,
            'previous_reading' => $previousReading,
            'current_reading' => $currentOdometer,
            'calculated_difference' => $calculatedDiff,
            'recorded_date' => $forDate->toDateString(),
            'source' => $source,
        ]);
    }

    private function sourceToTrackingType(string $source): string
    {
        return in_array($source, ['vehicle_locations', 'fuel_refills'], true)
            ? VehicleMileageHistory::TRACKING_GPS
            : VehicleMileageHistory::TRACKING_MANUAL;
    }

    /**
     * Get latest odometer per vehicle as of a given timestamp.
     * Returns [vehicle_id => ['odometer' => float, 'source' => string]].
     */
    private function getLatestOdometerPerVehicle(Carbon $before): array
    {
        $result = [];

        // Primary: vehicle_locations (GPS)
        $fromLocations = DB::table('vehicle_locations')
            ->where('created_at', '<=', $before)
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->orderByDesc('created_at')
            ->get(['vehicle_id', 'odometer'])
            ->unique('vehicle_id');

        foreach ($fromLocations as $row) {
            $result[(int) $row->vehicle_id] = [
                'odometer' => (float) $row->odometer,
                'source' => 'vehicle_locations',
            ];
        }

        // Fallback: fuel_refills (for vehicles not in vehicle_locations)
        $fromFuel = DB::table('fuel_refills')
            ->where('refilled_at', '<=', $before)
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->orderByDesc('refilled_at')
            ->get(['vehicle_id', DB::raw('odometer_km as odometer')])
            ->unique('vehicle_id');

        foreach ($fromFuel as $row) {
            $vid = (int) $row->vehicle_id;
            if (!isset($result[$vid])) {
                $result[$vid] = [
                    'odometer' => (float) $row->odometer,
                    'source' => 'fuel_refills',
                ];
            }
        }

        // Fallback: mobile_tracking_trips
        $fromTrips = DB::table('mobile_tracking_trips')
            ->where('ended_at', '<=', $before)
            ->whereNotNull('end_odometer')
            ->where('end_odometer', '>', 0)
            ->orderByDesc('ended_at')
            ->get(['vehicle_id', DB::raw('end_odometer as odometer')])
            ->unique('vehicle_id');

        foreach ($fromTrips as $row) {
            $vid = (int) $row->vehicle_id;
            if (!isset($result[$vid])) {
                $result[$vid] = [
                    'odometer' => (float) $row->odometer,
                    'source' => 'mobile_tracking_trips',
                ];
            }
        }

        return $result;
    }

    /**
     * Store odometer when GPS sends engine/ignition OFF status.
     * For vehicles with IMEI (GPS device): captures the last odometer value at end of trip.
     * Uses OdometerTrackingService for first-entry baseline logic.
     */
    public function storeOdometerOnGpsEngineOff(int $vehicleId, float $odometer): void
    {
        if ($odometer <= 0) {
            return;
        }

        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle || !$vehicle->usesDeviceApiTracking() || empty($vehicle->imei)) {
            return;
        }

        $odometerService = app(OdometerTrackingService::class);

        // For multiple GPS updates same day: use last today's current as previous
        $lastToday = VehicleMileageHistory::where('vehicle_id', $vehicleId)
            ->where('recorded_date', now()->toDateString())
            ->orderByDesc('created_at')
            ->first();

        $overridePrevious = $lastToday ? (float) $lastToday->current_reading : null;

        $odometerService->recordOdometerEntry(
            $vehicleId,
            $odometer,
            VehicleMileageHistory::SOURCE_VEHICLE_LOCATIONS,
            now(),
            VehicleMileageHistory::TRACKING_GPS,
            $overridePrevious
        );

        $odometerService->clearMileageCache($vehicle->company_id);
    }
}
