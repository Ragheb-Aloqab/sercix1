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
        Cache::forget("company_{$companyId}_last_seven_months");
        Cache::forget("company_{$companyId}_fuel_by_month");
        Cache::forget("company_{$companyId}_top_vehicles");
    }
}
