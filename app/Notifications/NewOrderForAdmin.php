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
        return [
            'type' => 'new_order',
            'title' => 'طلب جديد',
            'message' => $companyName ? "طلب جديد من الشركة: {$companyName}" : 'طلب جديد من عميل',
            'order_id' => $this->order->id,
            'company_name' => $companyName,
            'driver_phone' => $this->order->driver_phone,
            'url' => route('admin.orders.show', $this->order->id),
            'created_at' => now(),
        ];
    }
}
