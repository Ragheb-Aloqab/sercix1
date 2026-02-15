<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDriverSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('driver_phone')) {
            return redirect()->route('sign-in.index')->with('error', __('messages.driver_login_required'));
        }
        return $next($request);
    }
}
