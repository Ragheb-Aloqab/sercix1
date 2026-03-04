<?php

namespace App\Livewire\Dashboard;

use App\Services\ThemeService;
use Livewire\Component;

class ThemeToggleStandalone extends Component
{
    public string $theme = 'light';

    public function mount(ThemeService $themeService)
    {
        $this->theme = $themeService->getEffectiveTheme();
    }

    public function toggleTheme(ThemeService $themeService)
    {
        $this->theme = $themeService->toggle();
        $this->dispatch('ui-theme-changed', theme: $this->theme);
    }

    public function render()
    {
        return view('livewire.dashboard.theme-toggle-standalone');
    }
}
