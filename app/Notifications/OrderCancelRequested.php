<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class OrderCancelRequested extends Notification
{
    public function __construct(public $order) {}

    public function via($notifiable)
    {
        return ['database']; // أو mail لو تريد بريد
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'طلب إلغاء طلب',
            'message' => 'هناك طلب إلغاء للطلب رقم #' . $this->order->id,
            'order_id' => $this->order->id,
        ];
    }
}