<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleDailyOdometer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyOdometerSnapshotService
{
    /**
     * Store the last odometer value for each vehicle for the given date.
     * Sources (in order): vehicle_locations (GPS), fuel_refills, mobile_tracking_trips.
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
        }

        return ['stored' => $stored, 'updated' => $updated];
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
}
