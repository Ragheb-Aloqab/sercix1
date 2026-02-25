<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePaymentsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('servx.payments_enabled', false)) {
            abort(404);
        }

        return $next($request);
    }
}
