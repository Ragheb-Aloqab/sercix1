<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StuckOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public int $daysStuck) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $companyName = $this->order->company?->company_name ?? '-';
        return [
            'type' => 'stuck_order',
            'title' => __('admin_dashboard.alert_stuck_order'),
            'message' => __('dashboard.order') . ' #' . $this->order->id . ' — ' . $companyName . ' (' . $this->daysStuck . ' ' . __('common.days') . ')',
            'order_id' => $this->order->id,
            'url' => route('admin.orders.show', $this->order),
        ];
    }
}
