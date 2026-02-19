<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Middleware\LogUnauthorizedAccess;

class EnsureTechnician
{
    /**
     * Handle an incoming request.
     * Requires: auth:web. Ensures user is active and has technician role.
     * Aborts 403 with logging for unauthorized access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if (($user->status ?? 'active') !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('sign-in.index')->with('error', __('messages.account_suspended'));
        }

        if ($user->role !== 'technician') {
            LogUnauthorizedAccess::log($request, 'technician', 'web:' . ($user->role ?? 'unknown'));
            abort(403, __('errors.forbidden_message'));
        }

        return $next($request);
    }
}
