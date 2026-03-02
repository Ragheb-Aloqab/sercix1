<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Middleware\LogUnauthorizedAccess;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     * Requires: auth:web. Ensures user is active, has admin role, and 2FA verified.
     * Aborts 403 with logging for unauthorized access (prevents companies/drivers from accessing admin).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (($user->status ?? 'active') !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', __('messages.account_suspended'));
        }

        if (!in_array($user->role ?? '', ['admin', 'super_admin'])) {
            LogUnauthorizedAccess::log($request, 'admin', 'web:' . ($user->role ?? 'unknown'));
            abort(403, __('errors.forbidden_message'));
        }

        if (!$request->session()->has('two_factor_verified_at')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', __('login.two_factor_required'));
        }

        return $next($request);
    }
}
