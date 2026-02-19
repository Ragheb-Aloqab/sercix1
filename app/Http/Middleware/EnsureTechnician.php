<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTechnician
{
    /**
     * Handle an incoming request.
     * Requires: auth:web. Ensures user is active and has technician role.
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
            return redirect()->route($user->role === 'admin' ? 'admin.dashboard' : 'sign-in.index');
        }

        return $next($request);
    }
}
