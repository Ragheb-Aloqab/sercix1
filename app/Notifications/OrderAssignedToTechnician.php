<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderAssignedToTechnician extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database']; // نخزنه فقط
    }
    
    public function toArray(object $notifiable): array
    {
       return 
         [
            'type' => 'order_assigned',
            'title' => 'تم إسناد طلب جديد لك',
            'order_id' => $this->order->id,
            'customer_name' => $this->order->customer_name ?? null,
            'url' => route('tech.tasks.show', $this->order->id),
            'created_at' => now(),
        ];
        
    }
}
