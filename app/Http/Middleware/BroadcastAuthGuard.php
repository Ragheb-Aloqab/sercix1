<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures broadcast auth can resolve Company/MaintenanceCenter when they are logged in.
 * Only runs for /broadcasting/auth; the default auth guard is 'web', so company users need this.
 */
class BroadcastAuthGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('broadcasting/auth') || $request->is('broadcasting/*')) {
            if (auth('company')->check()) {
                $request->setUserResolver(fn () => auth('company')->user());
            } elseif (auth('maintenance_center')->check()) {
                $request->setUserResolver(fn () => auth('maintenance_center')->user());
            }
        }

        return $next($request);
    }
}
