<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Middleware\LogUnauthorizedAccess;

/**
 * Role-based middleware for web guard users (admin, technician).
 * Use: Route::middleware(['auth:web', 'checkrole:admin'])
 *
 * For company/driver, use EnsureCompany and EnsureDriverSession instead
 * (they use different auth mechanisms).
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth('web')->check()) {
            return redirect()->route('sign-in.index');
        }

        $user = auth('web')->user();

        if (($user->status ?? 'active') !== 'active') {
            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('sign-in.index')->with('error', __('messages.account_suspended'));
        }

        if ($user->role !== $role) {
            LogUnauthorizedAccess::log($request, $role, 'web:' . ($user->role ?? 'unknown'));
            abort(403, __('errors.forbidden_message'));
        }

        return $next($request);
    }
}
