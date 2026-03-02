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
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => __('messages.order_cancel_requested_title'),
            'message' => __('messages.order_cancel_requested_message', ['id' => $this->order->id]),
            'order_id' => $this->order->id,
            'url' => route('admin.orders.show', $this->order->id),
        ];
    }
}