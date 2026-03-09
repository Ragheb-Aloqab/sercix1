<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Str;

/**
 * Generates and validates unique subdomains for companies.
 * Used when creating new companies (registration or admin).
 */
class SubdomainService
{
    /** Reserved subdomains that must not be used. */
    private const RESERVED = [
        'www', 'app', 'admin', 'api', 'mail', 'ftp', 'cdn', 'static',
        'login', 'register', 'dashboard', 'support', 'help', 'blog',
        'status', 'docs', 'dev', 'staging', 'test', 'demo',
    ];

    private const MIN_LENGTH = 3;
    private const MAX_LENGTH = 30;

    /**
     * Generate a unique subdomain from company name.
     * Example: "Alpha Logistics" → "alpha" or "alpha-2" if taken.
     */
    public static function generateFromName(string $companyName): string
    {
        $base = self::slugify($companyName);

        if (strlen($base) < self::MIN_LENGTH) {
            $base = $base . str_repeat('x', self::MIN_LENGTH - strlen($base));
        }

        $base = substr($base, 0, self::MAX_LENGTH);
        $candidate = $base;
        $suffix = 0;

        while (!self::isAvailable($candidate)) {
            $suffix++;
            $candidate = substr($base, 0, self::MAX_LENGTH - strlen((string) $suffix) - 1)
                . '-' . $suffix;
        }

        return $candidate;
    }

    /**
     * Check if subdomain is available (not reserved, not taken).
     */
    public static function isAvailable(string $subdomain): bool
    {
        $subdomain = strtolower(trim($subdomain));

        if (in_array($subdomain, self::RESERVED, true)) {
            return false;
        }

        if (strlen($subdomain) < self::MIN_LENGTH || strlen($subdomain) > self::MAX_LENGTH) {
            return false;
        }

        if (!preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/', $subdomain)) {
            return false;
        }

        return !Company::query()->where('subdomain', $subdomain)->exists();
    }

    /**
     * Validate subdomain format (does not check availability).
     */
    public static function isValidFormat(string $subdomain): bool
    {
        $subdomain = strtolower(trim($subdomain));

        if (in_array($subdomain, self::RESERVED, true)) {
            return false;
        }

        if (strlen($subdomain) < self::MIN_LENGTH || strlen($subdomain) > self::MAX_LENGTH) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/', $subdomain);
    }

    /**
     * Convert company name to subdomain-safe slug.
     */
    private static function slugify(string $name): string
    {
        $slug = Str::slug($name, '-');
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        return $slug ?: 'company';
    }

    /**
     * Get full tenant domain (e.g. alpha.servxmotors.com).
     */
    public static function fullDomain(string $subdomain): string
    {
        $baseDomain = config('servx.white_label_domain', 'servxmotors.com');
        return $subdomain . '.' . $baseDomain;
    }
}
