<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePreferredDomain
{
    /**
     * Redirect www ↔ non-www to match APP_URL so canonical URLs stay consistent.
     * Prevents "Duplicate, Google chose different canonical than user" in Search Console.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

        if ($isLocal || ! app()->environment('production')) {
            return $next($request);
        }

        $appUrl = config('app.url');
        $preferredHost = parse_url($appUrl, PHP_URL_HOST);

        if (! $preferredHost) {
            return $next($request);
        }

        $baseDomain = config('servx.white_label_domain', $preferredHost);
        $isMainDomain = $host === $baseDomain || $host === 'www.' . $baseDomain;

        if (! $isMainDomain) {
            return $next($request);
        }

        $needsRedirect = $host !== $preferredHost;

        if ($needsRedirect) {
            $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
            $url = $scheme . '://' . $preferredHost . $request->getRequestUri();

            return redirect()->to($url, 301);
        }

        return $next($request);
    }
}
