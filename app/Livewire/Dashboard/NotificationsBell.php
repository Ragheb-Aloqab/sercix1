<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

class NotificationsBell extends Component
{
    public bool $open = false;

    public function toggle(): void { $this->open = ! $this->open; }
    public function close(): void { $this->open = false; }

    private function actor()
    {
        
        if (Auth::guard('company')->check()) return Auth::guard('company')->user();
        if (Auth::guard('web')->check()) return Auth::guard('web')->user();
        return null;
    }

    public function openNotification(string $id)
    {
        
        $actor = $this->actor();
        abort_unless($actor, 403);

        $notification = $actor->notifications()->whereKey($id)->first();
        abort_unless($notification, 404);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $url = data_get($notification->data, 'url');

        $this->open = false;

        if ($url) {
            return $this->redirect($url, navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        $actor = $this->actor();
        abort_unless($actor, 403);

        $actor->unreadNotifications()->update(['read_at' => now()]); // ✅
    }

    public function render()
    {
        $actor = $this->actor();
        $notifications = collect();
        $unreadCount = 0;

        if ($actor) {
            $notifications = $actor->notifications()
                ->latest()
                ->limit(10)
                ->get();

            $unreadCount = $actor->unreadNotifications()->count(); // ✅
        }
        
        return view('livewire.dashboard.notifications-bell', compact('notifications', 'unreadCount'));
    }
}
