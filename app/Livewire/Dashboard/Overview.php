<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;


class Overview

    extends Component
{
    public function render()
    {

       // dd(auth('company')->check());
        // Company guard
        if (request()->is('company/*') && auth('company')->check()) {
            return view('livewire.dashboard.company-overview', [
                'company' => auth('company')->user(),
            ]);
        }

        // Default web user
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            return view('livewire.dashboard.admin-overview');
        }

        // technician
        return view('livewire.dashboard.technician-overview', [
            'technician'  => auth()->user(),
            'latestTasks' => $this->latestTasks ?? collect(),
            'kpis'        => $this->kpis ?? [],
            'activeNow'   => $this->activeNow ?? null,
        ]);
    }
}
