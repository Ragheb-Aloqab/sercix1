<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompany
{
    /**
     * Handle an incoming request.
     * Ensures user is authenticated via company guard, active, and sets auth for Gate/Policy checks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('company')->check()) {
            return redirect()->to($this->dashboardForCurrentAuth());
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

    private function dashboardForCurrentAuth(): string
    {
        if (Auth::guard('company')->check()) {
            return route('company.dashboard');
        }
        if (session()->has('driver_phone')) {
            return route('driver.dashboard');
        }
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return match ($user->role ?? null) {
                'technician' => route('tech.dashboard'),
                'admin' => route('admin.dashboard'),
                default => route('dashboard'),
            };
        }
        return route('sign-in.index');
    }
}
