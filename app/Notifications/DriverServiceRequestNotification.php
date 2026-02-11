<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DriverServiceRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'driver_service_request',
            'title' => 'طلب خدمة من السائق',
            'message' => 'السائق ' . ($this->order->requested_by_name ?? 'غير معروف') . ' قدم طلب خدمة جديد.',
            'order_id' => $this->order->id,
            'requested_by_name' => $this->order->requested_by_name,
            'url' => route('company.orders.show', $this->order->id),
            'created_at' => now(),
        ];
    }
}
