<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures admin has completed 2FA.
 * Must run after auth:web. Checks session two_factor_verified_at.
 */
class EnsureAdmin2FA
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!in_array($user->role ?? '', ['admin', 'super_admin'])) {
            return $next($request);
        }

        if (($user->status ?? 'active') !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', __('messages.account_suspended'));
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
