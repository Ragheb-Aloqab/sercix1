<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Handles subdomain-aware redirects for company users.
 *
 * Ensures users always land on their correct company subdomain after login:
 * - Login from main domain → redirect to company subdomain
 * - Login from company subdomain → stay on same subdomain
 * - Login from wrong subdomain → redirect to correct company subdomain
 */
class SubdomainRedirectService
{
    public static function companyDashboardUrl(Company $company, ?Request $request = null): string
    {
        $request = $request ?? request();

        // SaaS: Always redirect to company subdomain when available
        if ($company->subdomain) {
            $domain = config('servx.white_label_domain', 'servxmotors.com');
            $scheme = $request->getScheme();

            return $scheme . '://' . $company->subdomain . '.' . $domain . '/company/dashboard';
        }

        return rtrim(config('app.url'), '/') . '/company/dashboard';
    }

    /**
     * Check if the current request is on the company's correct subdomain.
     */
    public static function isOnCorrectSubdomain(Company $company, ?Request $request = null): bool
    {
        $request = $request ?? request();
        $host = $request->getHost();
        $domain = config('servx.white_label_domain');

        if (!$company->subdomain || !$domain) {
            return true; // No subdomain expected, any host is fine
        }

        $expectedHost = $company->subdomain . '.' . $domain;

        return $host === $expectedHost;
    }

    /**
     * Get redirect URL if company user is on wrong host (main domain or another subdomain).
     * Returns null if no redirect needed.
     */
    public static function redirectUrlIfWrongHost(Company $company, ?Request $request = null): ?string
    {
        if (self::isOnCorrectSubdomain($company, $request)) {
            return null;
        }

        return self::companyDashboardUrl($company, $request);
    }
}
