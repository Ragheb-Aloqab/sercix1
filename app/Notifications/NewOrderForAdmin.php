<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderForAdmin extends Notification
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
            'type' => 'new_order',
            'title' => 'طلب جديد من عميل',
            'order_id' => $this->order->id,
            'customer_name' => $this->order->customer_name ?? null,
            'url' => route('admin.orders.show', $this->order->id),
            'created_at' => now(),
        ];
    }
}
