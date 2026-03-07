<?php

namespace App\Services;

/**
 * Tenant theme helper: generates color variations for tenant branding.
 * Used when passing colors to JS (e.g. charts) or for server-rendered inline styles.
 */
class TenantThemeHelper
{
    public const DEFAULT_PRIMARY = '#2563eb';
    public const DEFAULT_SECONDARY = '#16a34a';

    /**
     * Return primary color (with optional fallback).
     */
    public static function primary(?\App\Models\Company $tenant = null): string
    {
        return $tenant ? $tenant->getResolvedPrimaryColor() : self::DEFAULT_PRIMARY;
    }

    /**
     * Return secondary color (with optional fallback).
     */
    public static function secondary(?\App\Models\Company $tenant = null): string
    {
        return $tenant ? $tenant->getResolvedSecondaryColor() : self::DEFAULT_SECONDARY;
    }

    /**
     * Generate a hover color (lighter) from a hex color.
     * Mixes 85% of the color with 15% white for a subtle lighten.
     */
    public static function hoverColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return $hex;
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $r = (int) round($r * 0.85 + 255 * 0.15);
        $g = (int) round($g * 0.85 + 255 * 0.15);
        $b = (int) round($b * 0.85 + 255 * 0.15);
        return sprintf('#%02x%02x%02x', min(255, $r), min(255, $g), min(255, $b));
    }

    /**
     * Generate a muted (low-opacity) variant for backgrounds.
     * Returns CSS-ready rgba string with ~20% opacity.
     */
    public static function mutedColor(string $hex, float $alpha = 0.2): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return $hex;
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, number_format($alpha, 2));
    }

    /**
     * Return theme variables for the current tenant (for use in JS/config).
     *
     * @return array{primary: string, secondary: string, primaryHover: string, secondaryHover: string}
     */
    public static function forTenant(?\App\Models\Company $tenant = null): array
    {
        $primary = self::primary($tenant);
        $secondary = self::secondary($tenant);
        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'primaryHover' => self::hoverColor($primary),
            'secondaryHover' => self::hoverColor($secondary),
        ];
    }
}
