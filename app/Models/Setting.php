<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return static::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }

    /**
     * Set / update setting value
     */
    public static function put(string $key, $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? (int)$value : $value]
        );
    }
}