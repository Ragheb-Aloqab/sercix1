<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleDailyOdometer;
use App\Models\VehicleMileageHistory;
use App\Models\MobileTrackingTrip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized service for vehicle odometer tracking.
 *
 * First Entry (New Vehicle): When no previous odometer record exists, the first value
 * is saved as the baseline (previous) only. No distance calculation is performed.
 * calculated_difference = 0 — excluded from Market Average Cost.
 *
 * Second Entry and After: The entered value is the current reading. Distance is
 * calculated as Current - Previous. Current becomes previous for the next calculation.
 * Market Average Cost = Distance × 0.37 (displayed in dashboard).
 */
class OdometerTrackingService
{
    /**
     * Check if the vehicle has any previous odometer record.
     * Sources: vehicle_daily_odometer, vehicle_mileage_history, mobile_tracking_trips.
     */
    public function hasPreviousOdometerRecord(int $vehicleId): bool
    {
        return $this->getLastOdometerForVehicle($vehicleId) !== null;
    }

    /**
     * Get the last recorded odometer value for a vehicle.
     * Used as "previous" for the next calculation.
     */
    public function getLastOdometerForVehicle(int $vehicleId): ?float
    {
        $fromDaily = VehicleDailyOdometer::where('vehicle_id', $vehicleId)
            ->orderByDesc('date')
            ->value('odometer_km');

        if ($fromDaily !== null) {
            return (float) $fromDaily;
        }

        $fromTrip = MobileTrackingTrip::where('vehicle_id', $vehicleId)
            ->whereNotNull('end_odometer')
            ->where('end_odometer', '>', 0)
            ->orderByDesc('ended_at')
            ->value('end_odometer');

        return $fromTrip !== null ? (float) $fromTrip : null;
    }

    /**
     * Record an odometer entry with proper first-entry baseline logic.
     *
     * First entry (no previous): Value saved as baseline only. No distance calculated.
     * Second entry and after: Distance = current - previous. Current becomes previous for next.
     *
     * @param  int  $vehicleId
     * @param  float  $currentReading  The odometer value being entered
     * @param  string  $source  e.g. VehicleMileageHistory::SOURCE_MANUAL_DAILY, SOURCE_MOBILE_TRIPS, etc.
     * @param  Carbon|null  $recordedDate  Default: today
     * @param  string  $trackingType  TRACKING_GPS or TRACKING_MANUAL
     * @param  float|null  $overridePrevious  Optional: use this as previous instead of fetching (e.g. for mobile trip start_odometer)
     * @return array{is_first_entry: bool, previous_reading: float|null, calculated_difference: float}
     */
    public function recordOdometerEntry(
        int $vehicleId,
        float $currentReading,
        string $source,
        ?Carbon $recordedDate = null,
        string $trackingType = VehicleMileageHistory::TRACKING_MANUAL,
        ?float $overridePrevious = null
    ): array {
        $recordedDate = $recordedDate ?? now();
        $dateString = $recordedDate->toDateString();

        // Validate: prevent invalid odometer values
        if ($currentReading < 0) {
            throw new \InvalidArgumentException('Odometer value cannot be negative.');
        }

        $previousReading = $overridePrevious ?? $this->getLastOdometerForVehicle($vehicleId);
        $isFirstEntry = $previousReading === null;

        if ($isFirstEntry) {
            // First entry: save as baseline (previous) only. No distance calculation.
            $previousReading = $currentReading;
            $calculatedDifference = 0.0;
        } else {
            // Second entry and after: Distance = Current - Previous. Prevent negative.
            $calculatedDifference = max(0.0, $currentReading - $previousReading);
        }

        // Store in vehicle_daily_odometer (for daily snapshot / last value lookup)
        VehicleDailyOdometer::updateOrCreate(
            [
                'vehicle_id' => $vehicleId,
                'date' => $dateString,
            ],
            [
                'odometer_km' => $currentReading,
                'source' => $source,
            ]
        );

        // manual_daily: one entry per day; update existing record if driver re-enters on same day
        $isDaily = $source === VehicleMileageHistory::SOURCE_MANUAL_DAILY;
        $existing = $isDaily
            ? VehicleMileageHistory::where('vehicle_id', $vehicleId)->where('recorded_date', $dateString)->first()
            : null;

        if ($existing) {
            $existing->update([
                'previous_reading' => $previousReading,
                'current_reading' => $currentReading,
                'calculated_difference' => $calculatedDifference,
            ]);
            return [
                'is_first_entry' => $isFirstEntry,
                'previous_reading' => $previousReading,
                'calculated_difference' => $calculatedDifference,
            ];
        }

        // Store in vehicle_mileage_history
        VehicleMileageHistory::create([
            'vehicle_id' => $vehicleId,
            'tracking_type' => $trackingType,
            'previous_reading' => $previousReading,
            'current_reading' => $currentReading,
            'calculated_difference' => $calculatedDifference,
            'recorded_date' => $dateString,
            'source' => $source,
        ]);

        return [
            'is_first_entry' => $isFirstEntry,
            'previous_reading' => $previousReading,
            'calculated_difference' => $calculatedDifference,
        ];
    }

    /**
     * Validate that a new odometer reading is not less than the previous (prevents invalid entries).
     *
     * @throws \InvalidArgumentException when value is invalid
     */
    public function validateOdometerReading(int $vehicleId, float $newReading): void
    {
        if ($newReading < 0) {
            throw new \InvalidArgumentException('Odometer value cannot be negative.');
        }

        $previous = $this->getLastOdometerForVehicle($vehicleId);
        if ($previous !== null && $newReading < $previous) {
            throw new \InvalidArgumentException(__('tracking.end_odometer_less_than_previous'));
        }
    }

    /**
     * Clear mileage-related cache for a company (call after recording odometer).
     * Ensures Market Average Cost and Total Distance stay in sync when driver adds odometer.
     */
    public function clearMileageCache(?int $companyId): void
    {
        if ($companyId === null) {
            return;
        }
        Cache::forget('market_avg_cost_card_' . $companyId . '_' . now()->format('Y-m'));
        Cache::forget("market_comparison_{$companyId}_6");
        Cache::forget("market_comparison_{$companyId}_12");
        Cache::forget("company_dashboard_{$companyId}");
    }
}
