<?php
// app/Support/Settings.php
namespace App\Support;

use App\Models\Setting;

class Settings
{
    public static function get(string $key, $default = null)
    {
        return Setting::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}
