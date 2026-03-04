<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class ThemeService
{
    public const LIGHT = 'light';
    public const DARK = 'dark';
    public const SYSTEM = 'system';

    /**
     * Resolve effective theme (light or dark) from preference.
     */
    public function resolve(string $preference): string
    {
        if ($preference === self::SYSTEM) {
            return $this->getSystemPreference();
        }
        return in_array($preference, [self::LIGHT, self::DARK], true) ? $preference : self::LIGHT;
    }

    /**
     * Get theme preference for current actor (user, company, maintenance_center).
     * Returns: light | dark | system
     */
    public function getPreference(): string
    {
        $actor = $this->actor();
        if ($actor && $this->hasThemeColumn($actor)) {
            $pref = $actor->theme_preference ?? null;
            if ($pref) {
                return $pref;
            }
        }
        return session('ui.theme', self::SYSTEM);
    }

    /**
     * Get effective theme (light or dark) for current actor.
     */
    public function getEffectiveTheme(): string
    {
        return $this->resolve($this->getPreference());
    }

    /**
     * Set theme preference and persist.
     */
    public function setPreference(string $theme): void
    {
        $theme = in_array($theme, [self::LIGHT, self::DARK, self::SYSTEM], true) ? $theme : self::LIGHT;

        $actor = $this->actor();
        if ($actor && $this->hasThemeColumn($actor)) {
            $actor->update(['theme_preference' => $theme]);
        }
        session(['ui.theme' => $theme]);
    }

    /**
     * Toggle between light and dark (ignores system).
     */
    public function toggle(): string
    {
        $current = $this->getEffectiveTheme();
        $next = $current === self::DARK ? self::LIGHT : self::DARK;
        $this->setPreference($next);
        return $next;
    }

    private function actor(): ?Authenticatable
    {
        if (Auth::guard('company')->check()) {
            return Auth::guard('company')->user();
        }
        if (Auth::guard('maintenance_center')->check()) {
            return Auth::guard('maintenance_center')->user();
        }
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }
        return null;
    }

    private function hasThemeColumn(Authenticatable $model): bool
    {
        return \Schema::hasColumn($model->getTable(), 'theme_preference');
    }

    private function getSystemPreference(): string
    {
        if (isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'])) {
            return $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] === 'dark' ? self::DARK : self::LIGHT;
        }
        return self::LIGHT;
    }
}
