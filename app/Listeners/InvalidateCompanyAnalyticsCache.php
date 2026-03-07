<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;

class InvalidateCompanyAnalyticsCache
{
    /**
     * Invalidate company analytics cache for a company ID.
     */
    public static function forCompany(?int $companyId): void
    {
        if (!$companyId) {
            return;
        }
        Cache::forget("company_dashboard_{$companyId}");
        Cache::forget("market_comparison_{$companyId}_6");
        Cache::forget("market_comparison_{$companyId}_12");
        Cache::forget("market_monthly_{$companyId}_6");
        Cache::forget("market_monthly_{$companyId}_12");
        Cache::forget("company_{$companyId}_last_seven_months");
        Cache::forget("company_{$companyId}_fuel_by_month");
        Cache::forget("company_{$companyId}_top_vehicles");
    }

    /**
     * Invalidate vehicle analytics cache when maintenance invoice is added for a vehicle.
     */
    public static function forVehicle(?int $vehicleId): void
    {
        if (!$vehicleId) {
            return;
        }
        Cache::forget("vehicle_analytics_{$vehicleId}");
        foreach ([6, 12] as $months) {
            Cache::forget("vehicle_chart_{$vehicleId}_{$months}");
        }
    }
}
