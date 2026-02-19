<?php

namespace App\Livewire\Layout;

use App\Livewire\Actions\Logout;
use Livewire\Component;

class Navigation extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        $userName = auth()->user()->name ?? '';
        $userName = is_string($userName) ? $userName : (string) $userName;
        $userEmail = (string) (auth()->user()->email ?? '');

        return view('livewire.layout.navigation', compact('userName', 'userEmail'));
    }
}
