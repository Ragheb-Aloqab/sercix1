<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\SubdomainRedirectService;
use App\Services\TenantSecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class LoadCompanyFromSubdomain
{
    /** Reserved subdomains that must not resolve to a tenant. */
    private const RESERVED_SUBDOMAINS = ['www', 'app', 'admin', 'api', 'mail', 'ftp', 'cdn', 'static'];

    /** Subdomain length limits (strict validation). */
    private const SUBDOMAIN_MIN_LENGTH = 3;
    private const SUBDOMAIN_MAX_LENGTH = 30;

    /**
     * Handle an incoming request.
     * When on white-label subdomain: resolve tenant, validate, bind and enforce auth match.
     * When not on subdomain but company is logged in: bind tenant for global scoping.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->extractAndValidateSubdomain($request);

        if ($subdomain !== null) {
            $company = $this->resolveTenantFromSubdomain($subdomain);

            if (!$company) {
                TenantSecurityLogger::invalidSubdomainAccess($subdomain, 'company_not_found_or_inactive');
                abort(404);
            }

            $this->bindTenant($company);
            app()->instance('tenant_from_subdomain', true);

            // Keep links (e.g. set-locale) on this subdomain so session and locale persist
            URL::forceRootUrl($request->getSchemeAndHttpHost());

            if (Auth::guard('company')->check()) {
                $authCompany = Auth::guard('company')->user();
                if ($authCompany->id !== $company->id) {
                    TenantSecurityLogger::tenantMismatch($authCompany->id, $company->id);
                    $redirectUrl = SubdomainRedirectService::companyDashboardUrl($authCompany, $request);
                    return redirect()->to($redirectUrl);
                }
            }

            Auth::shouldUse('company');
            return $next($request);
        }

        // Not on white-label subdomain: if company user is logged in, bind tenant for global scope only (no branding)
        if (Auth::guard('company')->check()) {
            $this->bindTenant(Auth::guard('company')->user());
            app()->instance('tenant_from_subdomain', false);
            Auth::shouldUse('company');
        }

        return $next($request);
    }

    private function bindTenant(Company $company): void
    {
        app()->instance('tenant', $company);
        app()->instance('company', $company);
        view()->share('tenant', $company);
        view()->share('company', $company);
    }

    private function resolveTenantFromSubdomain(string $subdomain): ?Company
    {
        // SaaS: All companies with subdomains can access their tenant dashboard
        return Company::query()
            ->where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Extract and validate subdomain. Returns null if not a white-label request or invalid.
     */
    private function extractAndValidateSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $domain = config('servx.white_label_domain');

        if (!$domain || $domain === '' || !str_ends_with($host, $domain)) {
            return null;
        }

        $subdomain = substr($host, 0, -strlen($domain) - 1);

        if ($subdomain === '' || $subdomain === false) {
            return null;
        }

        $subdomain = strtolower($subdomain);

        if (in_array($subdomain, self::RESERVED_SUBDOMAINS, true)) {
            return null;
        }

        $len = strlen($subdomain);
        if ($len < self::SUBDOMAIN_MIN_LENGTH || $len > self::SUBDOMAIN_MAX_LENGTH) {
            TenantSecurityLogger::invalidSubdomainAccess($subdomain, 'invalid_length', ['length' => $len]);
            return null;
        }

        if (!preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/', $subdomain)) {
            TenantSecurityLogger::invalidSubdomainAccess($subdomain, 'invalid_format');
            return null;
        }

        return $subdomain;
    }
}
