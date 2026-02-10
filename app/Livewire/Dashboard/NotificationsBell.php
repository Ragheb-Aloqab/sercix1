<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsBell extends Component
{
    public bool $open = false;
    public int $unreadCount = 0;
    /** @var array<int, array{id:string,data:array,read_at:?string,created_at:?string,created_human:?string}> */
    public array $notifications = [];

    public function mount(): void
    {
        $this->refreshUnread();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;

        if ($this->open) {
            $this->loadNotifications();
            $this->refreshUnread();
        }
    }

    public function close(): void
    {
        $this->open = false;
    }

    private function actor()
    {
        
        if (Auth::guard('company')->check()) return Auth::guard('company')->user();
        if (Auth::guard('web')->check()) return Auth::guard('web')->user();
        return null;
    }

    public function refreshUnread(): void
    {
        $actor = $this->actor();
        $this->unreadCount = $actor ? (int) $actor->unreadNotifications()->count() : 0;
    }

    public function loadNotifications(): void
    {
        $actor = $this->actor();
        if (! $actor) {
            $this->notifications = [];
            return;
        }

        $this->notifications = $actor->notifications()
            ->latest()
            ->limit(10)
            ->get(['id', 'data', 'read_at', 'created_at'])
            ->map(function ($n) {
                return [
                    'id'            => (string) $n->id,
                    'data'          => (array) ($n->data ?? []),
                    'read_at'       => $n->read_at?->toISOString(),
                    'created_at'    => $n->created_at?->toISOString(),
                    'created_human' => $n->created_at?->shortRelativeDiffForHumans(),
                ];
            })
            ->all();
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

        $url = data_get($notification->data, 'url') ?? data_get($notification->data, 'route');

        // Fallback for older notifications that don't have url/route saved
        if (! $url) {
            $orderId = data_get($notification->data, 'order_id');
            $paymentId = data_get($notification->data, 'payment_id');

            if ($paymentId && Auth::guard('company')->check()) {
                $url = route('company.payments.show', $paymentId);
            } elseif ($orderId) {
                if (Auth::guard('company')->check()) {
                    $url = route('company.orders.show', $orderId);
                } elseif (Auth::guard('web')->check() && ($actor->role ?? null) === 'technician') {
                    $url = route('tech.tasks.show', $orderId);
                } else {
                    $url = route('admin.orders.show', $orderId);
                }
            } elseif ($paymentId && Auth::guard('web')->check()) {
                // no admin payment show route; best fallback is order if available
                $maybeOrderId = data_get($notification->data, 'order_id');
                if ($maybeOrderId) {
                    $url = route('admin.orders.show', $maybeOrderId);
                }
            }
        }

        $this->open = false;
        $this->refreshUnread();
        // refresh list so read state updates next time
        $this->loadNotifications();

        if ($url) {
            return $this->redirect($url, navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        $actor = $this->actor();
        abort_unless($actor, 403);

        $actor->unreadNotifications()->update(['read_at' => now()]); // ✅
        $this->refreshUnread();
        if ($this->open) {
            $this->loadNotifications();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.notifications-bell', [
            'notifications' => $this->notifications,
            'unreadCount'   => $this->unreadCount,
        ]);
    }
}
