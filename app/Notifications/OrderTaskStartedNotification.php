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
        $technicianName = $this->order->technician?->name ?? __('messages.technician');
        $statusLabel = $this->status === 'assigned_to_technician'
            ? __('messages.order_assigned_to_technician')
            : __('messages.order_in_progress');

        $url = $notifiable instanceof Company
            ? route('company.orders.show', $this->order->id)
            : route('admin.orders.show', $this->order->id);

        return [
            'type' => 'order_task_started',
            'title' => __('messages.order_task_started_title'),
            'message' => __('messages.order_task_started_message', [
                'technician' => $technicianName,
                'status' => $statusLabel,
                'id' => $this->order->id,
            ]),
            'order_id' => $this->order->id,
            'technician_name' => $technicianName,
            'status' => $this->status,
            'route' => $url,
            'url' => $url,
        ];
    }
}
