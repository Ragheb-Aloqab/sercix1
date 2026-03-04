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
        return view('components.theme-init', [
            'initialTheme' => $actor ? $effective : null,
            'initialPreference' => $pref,
        ]);
    }
}
