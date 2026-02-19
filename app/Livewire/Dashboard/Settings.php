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
        $btn = 'px-4 py-2 rounded-2xl border text-sm font-bold';
        $active = 'bg-slate-900 text-white border-slate-900';
        $normal = 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800';

        return view('livewire.dashboard.settings', compact('btn', 'active', 'normal'));
    }
}
