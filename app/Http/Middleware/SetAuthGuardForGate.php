<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures auth()->user() returns the correct user for Gate/Policy checks.
 * Use in route groups where a non-default guard is used (e.g. company).
 */
class SetAuthGuardForGate
{
    public function handle(Request $request, Closure $next, string $guard = 'company'): Response
    {
        if (Auth::guard($guard)->check()) {
            Auth::shouldUse($guard);
        }

        return $next($request);
    }
}
