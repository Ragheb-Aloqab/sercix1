<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Middleware\LogUnauthorizedAccess;

class EnsureCompany
{
    /**
     * Handle an incoming request.
     * Ensures user is authenticated via company guard, active, and sets auth for Gate/Policy checks.
     * Aborts 403 with logging when drivers or web users (admin/technician) try to access company pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('company')->check()) {
            $actual = $this->detectActualContext($request);
            LogUnauthorizedAccess::log($request, 'company', $actual);
            abort(403, __('errors.forbidden_message'));
        }

        $company = Auth::guard('company')->user();

        if (($company->status ?? 'active') !== 'active') {
            Auth::guard('company')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('sign-in.index')->with('error', __('messages.account_suspended'));
        }

        Auth::shouldUse('company');

        return $next($request);
    }

    private function detectActualContext(Request $request): string
    {
        if (Auth::guard('company')->check()) {
            return 'company';
        }
        if ($request->session()->has('driver_phone')) {
            return 'driver';
        }
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return 'web:' . ($user->role ?? 'unknown');
        }
        return 'guest';
    }
}
