<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripTenantInputFromRequest
{
    /**
     * Keys that must never be taken from request when tenant is bound (company context).
     */
    private const TENANT_KEYS = ['company_id'];

    /**
     * Handle an incoming request.
     * Remove company_id (and other tenant identifiers) from input so they cannot be injected.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->bound('tenant')) {
            return $next($request);
        }

        foreach (self::TENANT_KEYS as $key) {
            $request->request->remove($key);
            $request->query->remove($key);
        }

        return $next($request);
    }
}
