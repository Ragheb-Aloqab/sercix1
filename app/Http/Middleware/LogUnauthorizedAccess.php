<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs unauthorized access attempts for security auditing.
 * Call this before aborting 403 in role/guard middlewares.
 */
class LogUnauthorizedAccess
{
    public static function log(Request $request, string $requiredRoleOrGuard, ?string $actualContext = null): void
    {
        $user = $request->user();
        $company = $request->user('company');

        $context = [
            'required' => $requiredRoleOrGuard,
            'actual' => $actualContext ?? self::detectActualContext($request, $user, $company),
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        if ($user) {
            $context['user_id'] = $user->id;
            $context['user_role'] = $user->role ?? null;
        }
        if ($company) {
            $context['company_id'] = $company->id;
        }
        if ($request->session()->has('driver_phone')) {
            $context['driver_phone_session'] = true;
        }

        Log::warning('Unauthorized access attempt', $context);
    }

    private static function detectActualContext(Request $request, $user, $company): string
    {
        if ($company) {
            return 'company';
        }
        if ($request->session()->has('driver_phone')) {
            return 'driver';
        }
        if ($user) {
            return 'web:' . ($user->role ?? 'unknown');
        }
        return 'guest';
    }
}
