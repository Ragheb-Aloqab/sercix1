<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMaintenanceCenter
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('maintenance_center')->check()) {
            return redirect()->route('maintenance-center.login');
        }

        $center = Auth::guard('maintenance_center')->user();

        if (($center->status ?? 'active') !== 'active') {
            Auth::guard('maintenance_center')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('maintenance-center.login')
                ->with('error', __('messages.account_suspended'));
        }

        Auth::shouldUse('maintenance_center');

        return $next($request);
    }
}
