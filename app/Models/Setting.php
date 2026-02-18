<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Cache TTL in seconds (5 minutes) */
    private const CACHE_TTL = 300;

    /**
     * Get setting value by key (cached).
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "setting.{$key}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            return static::query()
                ->where('key', $key)
                ->value('value') ?? $default;
        });
    }

    /**
     * Set / update setting value and clear cache.
     */
    public static function put(string $key, $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? (int) $value : $value]
        );
        Cache::forget("setting.{$key}");
        if ($key === 'site_logo_path') {
            Cache::forget('site_logo_url');
        }
    }
}