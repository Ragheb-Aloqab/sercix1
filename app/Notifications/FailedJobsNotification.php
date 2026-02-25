<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FailedJobsNotification extends Notification
{
    use Queueable;

    public function __construct(public int $failedCount) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'failed_jobs',
            'title' => __('admin_dashboard.queue_failed'),
            'message' => $this->failedCount . ' ' . __('admin_dashboard.queue_failed'),
            'url' => null,
        ];
    }
}
