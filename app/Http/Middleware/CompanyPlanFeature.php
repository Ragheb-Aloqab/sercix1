<?php

namespace App\Http\Middleware;

use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyPlanFeature
{
    /**
     * Ensure the authenticated company's subscription plan includes the given feature.
     * Use: ->middleware('company.feature:vehicle_tracking')
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $company = $request->user('company');
        if (!$company) {
            return redirect()->guest(route('login'));
        }
        if (!in_array($feature, SubscriptionPlan::FEATURES, true)) {
            return $next($request);
        }
        SubscriptionService::authorize($company, $feature);
        return $next($request);
    }
}
