<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class UiPreferences extends Component
{
    public string $theme = 'light'; // light | dark
    public string $dir   = 'rtl';   // rtl | ltr

    public function mount()
    {
        $this->theme = session('ui.theme', 'light');
        $this->dir   = session('ui.dir', 'rtl');
    }

    public function toggleTheme()
    {
        $this->theme = $this->theme === 'dark' ? 'light' : 'dark';
        session(['ui.theme' => $this->theme]);

        $this->dispatch('ui-theme-changed', theme: $this->theme);
    }

    public function toggleDir()
    {
        $this->dir = $this->dir === 'rtl' ? 'ltr' : 'rtl';
        session(['ui.dir' => $this->dir]);

        $this->dispatch('ui-dir-changed', dir: $this->dir);
    }

    public function render()
    {
        return view('livewire.dashboard.ui-preferences');
    }
}
