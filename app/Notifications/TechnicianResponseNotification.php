<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class TechnicianResponseNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $order,
        public $technician,
        public string $status // accepted | rejected
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $url = null;
        if ($notifiable instanceof Company) {
            $url = route('company.orders.show', $this->order->id);
        } elseif ($notifiable instanceof User && $notifiable->role === 'technician') {
            $url = route('tech.tasks.show', $this->order->id);
        } else {
            $url = route('admin.orders.show', $this->order->id);
        }

        return [
            'order_id' => $this->order->id,
            'technician_id' => $this->technician->id,
            'status' => $this->status,
            'title' => $this->status === 'accepted'
                ? 'تم قبول اسناد الطلب'
                : 'تم رفض اسناد الطلب',
            'message' => $this->status === 'accepted'
                ? "الفني {$this->technician->name} قبل تنفيذ الطلب"
                : "الفني {$this->technician->name} رفض تنفيذ الطلب",
            'url' => $url,
        ];
    }
}