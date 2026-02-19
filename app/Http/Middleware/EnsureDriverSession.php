<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Middleware\LogUnauthorizedAccess;

class EnsureDriverSession
{
    /**
     * Handle an incoming request.
     * Ensures driver is authenticated via session (driver_phone).
     * Aborts 403 with logging when companies or web users try to access driver pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('driver_phone')) {
            $actual = $this->detectActualContext($request);
            LogUnauthorizedAccess::log($request, 'driver', $actual);
            abort(403, __('errors.forbidden_message'));
        }
        return $next($request);
    }

    private function detectActualContext(Request $request): string
    {
        if (Auth::guard('company')->check()) {
            return 'company';
        }
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return 'web:' . ($user->role ?? 'unknown');
        }
        return 'guest';
    }
}
