<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
    * Force HTTPS in production (skip for localhost/127.0.0.1).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

        if (! $isLocal && app()->environment('production') && ! $request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
