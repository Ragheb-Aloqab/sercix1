<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Cache;

class SubscriptionService
{
    private const PLANS_CACHE_KEY = 'subscription_plans_active';
    private const PLANS_CACHE_TTL = 3600; // 1 hour

    /** Get all feature keys with labels from config. */
    public static function featureLabels(): array
    {
        return config('servx.subscription_plans.features', []);
    }

    /** Check if the given company can use the feature (backend/frontend). */
    public static function can(Company $company, string $featureKey): bool
    {
        return $company->canUseFeature($featureKey);
    }

    /** Abort with 403 if company does not have the feature. */
    public static function authorize(Company $company, string $featureKey): void
    {
        if (!static::can($company, $featureKey)) {
            abort(403, __('plans.feature_not_included') ?: 'This feature is not included in your plan.');
        }
    }

    /** Get active plans ordered for display (e.g. pricing page). Cached for 1 hour. */
    public static function activePlansForDisplay()
    {
        return Cache::remember(self::PLANS_CACHE_KEY, self::PLANS_CACHE_TTL, fn () =>
            SubscriptionPlan::active()->ordered()->get()
        );
    }

    /** Invalidate plans cache (call when plans are created/updated/deleted). */
    public static function invalidatePlansCache(): void
    {
        Cache::forget(self::PLANS_CACHE_KEY);
    }
}
