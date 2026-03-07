<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TenantSecurityLogger
{
    public const CHANNEL = 'stack';

    /**
     * Log invalid subdomain access attempt (company not found or white-label disabled).
     */
    public static function invalidSubdomainAccess(string $subdomain, string $reason, array $context = []): void
    {
        Log::channel(self::CHANNEL)->warning('Tenant security: invalid subdomain access', array_merge([
            'event' => 'invalid_subdomain_access',
            'subdomain' => $subdomain,
            'reason' => $reason,
            'host' => request()?->getHost(),
            'ip' => request()?->ip(),
        ], $context));
    }

    /**
     * Log tenant mismatch (authenticated company tried to access another tenant).
     */
    public static function tenantMismatch(int $authenticatedCompanyId, int $resolvedTenantId, array $context = []): void
    {
        Log::channel(self::CHANNEL)->warning('Tenant security: tenant mismatch', array_merge([
            'event' => 'tenant_mismatch',
            'authenticated_company_id' => $authenticatedCompanyId,
            'resolved_tenant_id' => $resolvedTenantId,
            'host' => request()?->getHost(),
            'ip' => request()?->ip(),
        ], $context));
    }

    /**
     * Log forbidden cross-company access attempt (e.g. direct ID manipulation).
     */
    public static function forbiddenCrossCompanyAccess(string $resource, $resourceId, array $context = []): void
    {
        Log::channel(self::CHANNEL)->warning('Tenant security: forbidden cross-company access', array_merge([
            'event' => 'forbidden_cross_company_access',
            'resource' => $resource,
            'resource_id' => $resourceId,
            'tenant_id' => app()->bound('tenant') ? app('tenant')->id : null,
            'ip' => request()?->ip(),
        ], $context));
    }
}
