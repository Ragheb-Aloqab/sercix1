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
        $name = $this->order->requested_by_name ?? __('messages.driver_unknown');
        return [
            'type' => 'driver_service_request',
            'title' => __('messages.driver_service_request_title'),
            'message' => __('messages.driver_service_request_message', ['name' => $name]),
            'order_id' => $this->order->id,
            'requested_by_name' => $this->order->requested_by_name,
            'url' => route('company.orders.show', $this->order->id),
            'created_at' => now(),
        ];
    }
}
