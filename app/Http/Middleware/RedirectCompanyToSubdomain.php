<?php

namespace App\Http\Middleware;

use App\Services\SubdomainRedirectService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects authenticated company users to their correct subdomain when they
 * access company routes from the main domain or a wrong subdomain.
 *
 * Ensures seamless UX: users can log in from anywhere but always end up on
 * their company's subdomain (alpha.servx.sa/dashboard).
 */
class RedirectCompanyToSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('company')->check()) {
            return $next($request);
        }

        $company = Auth::guard('company')->user();
        $redirectUrl = SubdomainRedirectService::redirectUrlIfWrongHost($company, $request);

        if ($redirectUrl !== null) {
            return redirect()->to($redirectUrl);
        }

        return $next($request);
    }
}
