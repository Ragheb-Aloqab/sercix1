<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable instanceof Company && $notifiable->email) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $notifiable instanceof Company
            ? route('company.orders.show', $this->order->id)
            : route('admin.orders.show', $this->order->id);

        return (new MailMessage)
            ->subject(__('messages.order_completed_subject') ?: 'Order #' . $this->order->id . ' Completed')
            ->line(__('messages.order_completed') ?: 'Order #' . $this->order->id . ' has been completed.')
            ->action(__('common.view') ?: 'View Order', $url);
    }

    public function toArray(object $notifiable): array
    {
        $url = $notifiable instanceof Company
            ? route('company.orders.show', $this->order->id)
            : route('admin.orders.show', $this->order->id);

        return [
            'type' => 'order_completed',
            'title' => __('messages.order_completed') ?: 'Order completed',
            'message' => __('messages.order_completed') ?: 'Order #' . $this->order->id . ' has been completed.',
            'order_id' => $this->order->id,
            'route' => $url,
            'url' => $url,
        ];
    }
}
