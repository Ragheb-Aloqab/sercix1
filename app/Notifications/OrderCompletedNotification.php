<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $url = $notifiable instanceof Company
            ? route('company.orders.show', $this->order->id)
            : route('admin.orders.show', $this->order->id);

        $technicianName = $this->order->technician ? $this->order->technician->name : null;
        return [
            'type' => 'order_completed',
            'title' => 'تم إكمال الطلب',
            'message' => $technicianName
                ? "تم إكمال الطلب بواسطة الفني: {$technicianName}"
                : 'تم إكمال الطلب.',
            'order_id' => $this->order->id,
            'technician_name' => $technicianName,
            'route' => $url,
            'url' => $url,
        ];
    }
}
