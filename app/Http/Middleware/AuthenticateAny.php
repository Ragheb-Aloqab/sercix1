<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateAny
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = $guards ?: ['web'];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard); // مهم جدًا حتى Auth::user() يشتغل صح
                return $next($request);
            }
        }

        return redirect()->route('login');
    }
}
