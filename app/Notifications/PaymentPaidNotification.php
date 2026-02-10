<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentPaidNotification extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $url = null;

        // Company should go to the payment details
        if ($notifiable instanceof Company) {
            $url = route('company.payments.show', $this->payment->id);
        }

        // Technician/admin don't have a dedicated payment show page; route to the order
        if (! $url) {
            if ($notifiable instanceof User && $notifiable->role === 'technician') {
                $url = route('tech.tasks.show', $this->payment->order_id);
            } else {
                $url = route('admin.orders.show', $this->payment->order_id);
            }
        }

        return [
            'title'      => 'تم استلام دفعة',
            'payment_id' => $this->payment->id,
            'order_id'   => $this->payment->order_id,
            'amount'     => $this->payment->amount,
            'message'    => 'تم استلام دفعة بقيمة '
                            . $this->payment->amount
                            . ' للطلب #' . $this->payment->order_id,
            'url'        => $url,
        ];
    }
}


