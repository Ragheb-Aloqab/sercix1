<?php

namespace App\View\Components;

use App\Services\ThemeService;
use Illuminate\View\Component;

class ThemeInit extends Component
{
    public function __construct(
        private readonly ThemeService $themeService
    ) {}

    public function render()
    {
        $actor = auth('company')->user() ?? auth('maintenance_center')->user() ?? auth('web')->user();
        $effective = $this->themeService->getEffectiveTheme();
        $pref = $this->themeService->getPreference();
        $forceLight = app()->bound('tenant_from_subdomain') && app('tenant_from_subdomain');
        return view('components.theme-init', [
            'initialTheme' => $forceLight ? 'light' : ($actor ? $effective : null),
            'initialPreference' => $pref,
            'forceLightTheme' => $forceLight,
        ]);
    }
}
