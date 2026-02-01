<?php

namespace App\Livewire\Dashboard\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notifications extends Component
{
    public function render()
    {
      
       abort_unless(Auth::user()?->role === 'admin', 403);
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(20)
            ->get();
        $unreadCount = Auth::user()->unreadNotifications()->count();

        return view('livewire.dashboard.settings.notifications', compact('notifications','unreadCount'));
    }
    public function markAsRead(string $id)
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();
    }

    public function markAllAsRead()
    {
        auth()->user()
            ->unreadNotifications
            ->markAsRead();
    }
}

