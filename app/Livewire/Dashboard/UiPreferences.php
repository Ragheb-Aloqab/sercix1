<?php

namespace App\Livewire\Dashboard;

use App\Services\ThemeService;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class UiPreferences extends Component
{
    public string $theme = 'light'; // light | dark (effective)
    public string $dir   = 'rtl';   // rtl | ltr

    public function mount(ThemeService $themeService)
    {
        $this->theme = $themeService->getEffectiveTheme();
        $this->dir   = session('ui.dir', app()->getLocale() === 'ar' ? 'rtl' : 'ltr');
    }

    public function toggleTheme(ThemeService $themeService)
    {
        $this->theme = $themeService->toggle();

        $this->dispatch('ui-theme-changed', theme: $this->theme);
    }

    public function toggleDir()
    {
        $this->dir = $this->dir === 'rtl' ? 'ltr' : 'rtl';
        $locale = $this->dir === 'rtl' ? 'ar' : 'en';
        session(['ui.dir' => $this->dir, 'ui.locale' => $locale]);
        App::setLocale($locale);

        $this->dispatch('ui-dir-changed', dir: $this->dir);
    }

    public function render()
    {
        return view('livewire.dashboard.ui-preferences');
    }
}
