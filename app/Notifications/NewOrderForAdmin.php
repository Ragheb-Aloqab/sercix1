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
        $companyName = $this->order->company ? $this->order->company->company_name : null;
        $message = $companyName
            ? __('messages.new_order_message', ['company' => $companyName])
            : __('messages.new_order_message_fallback');
        return [
            'type' => 'new_order',
            'title' => __('messages.new_order_title'),
            'message' => $message,
            'order_id' => $this->order->id,
            'company_name' => $companyName,
            'driver_phone' => $this->order->driver_phone,
            'url' => route('admin.orders.show', $this->order->id),
            'created_at' => now(),
        ];
    }
}
