<?php

namespace App\Services;

class TenantScope
{
    /**
     * Return a cache key that includes the current tenant id when bound.
     * Use for any cached data that is tenant-specific.
     */
    public static function cacheKey(string $suffix): string
    {
        if (app()->bound('tenant')) {
            return 'tenant:' . app('tenant')->id . ':' . $suffix;
        }
        return 'global:' . $suffix;
    }

    /**
     * Return the current tenant id or null.
     */
    public static function tenantId(): ?int
    {
        if (app()->bound('tenant')) {
            return app('tenant')->id;
        }
        return null;
    }
}
