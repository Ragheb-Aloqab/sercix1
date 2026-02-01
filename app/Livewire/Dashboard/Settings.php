<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Settings extends Component
{
    public string $actorType = 'user'; // user | company
    public ?string $role = null;       // admin | technician | company | null
    public string $tab = 'profile';    // current tab

    public function mount()
    {
        // Company guard
        if (auth('company')->check()) {
            $this->actorType = 'company';
            $this->role = 'company';
            $this->tab = 'company_profile';
            return;
        }

        // User guard
        $user = Auth::user();
        abort_unless($user, 401);

        $this->actorType = 'user';
        $this->role = $user->role;

        $this->tab = 'profile';
    }

    public function setTab(string $tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.dashboard.settings');
    }
}
