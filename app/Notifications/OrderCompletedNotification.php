<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderCompletedNotification extends Notification /*implements ShouldQueue*/
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $url = route('company.orders.show', $this->order->id);
        return [
            'type' => 'order_completed',
            'title' => 'Order completed',
            'message' => 'Your order has been completed.',
            'order_id' => $this->order->id,
            'route' => $url,
            'url' => $url,
        ];
    }
}
