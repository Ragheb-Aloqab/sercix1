<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderTaskStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $technicianName = $this->order->technician?->name ?? 'الفني';
        $statusLabel = $this->status === 'assigned_to_technician' ? 'معيّن لفني' : 'قيد التنفيذ';

        $url = $notifiable instanceof Company
            ? route('company.orders.show', $this->order->id)
            : route('admin.orders.show', $this->order->id);

        return [
            'type' => 'order_task_started',
            'title' => 'بدء تنفيذ الطلب',
            'message' => "{$technicianName} {$statusLabel} - الطلب #{$this->order->id}",
            'order_id' => $this->order->id,
            'technician_name' => $technicianName,
            'status' => $this->status,
            'route' => $url,
            'url' => $url,
        ];
    }
}
