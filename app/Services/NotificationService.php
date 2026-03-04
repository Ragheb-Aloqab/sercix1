<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Get unread count for a notifiable (Company, User, etc.).
     */
    public function getUnreadCount(Model $notifiable): int
    {
        return $notifiable->unreadNotifications()->count();
    }

    /**
     * Get read count for a notifiable.
     */
    public function getReadCount(Model $notifiable): int
    {
        return $notifiable->readNotifications()->count();
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Model $notifiable, string $notificationId): bool
    {
        $notification = $notifiable->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    /**
     * Mark all notifications as read for a notifiable.
     */
    public function markAllAsRead(Model $notifiable): int
    {
        return $notifiable->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Get notifications with pagination.
     */
    public function getNotifications(
        Model $notifiable,
        string $filter = 'all',
        int $perPage = 15
    ) {
        $query = $notifiable->notifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Send a database notification (centralized for future Pusher/WebSocket).
     */
    public function notify(Model $notifiable, DatabaseNotification $notification): void
    {
        $notifiable->notify($notification);
    }

    /**
     * Get unread count for current company (for layout/header).
     */
    public function getCompanyUnreadCount(): int
    {
        $company = auth('company')->user();
        if (!$company) {
            return 0;
        }
        return $this->getUnreadCount($company);
    }
}
