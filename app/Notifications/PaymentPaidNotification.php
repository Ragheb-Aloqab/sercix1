<?php

namespace App\Notifications;

use App\Models\Payment;
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
        return [
            'title'      => 'تم استلام دفعة',
            'payment_id' => $this->payment->id,
            'order_id'   => $this->payment->order_id,
            'amount'     => $this->payment->amount,
            'message'    => 'تم استلام دفعة بقيمة '
                            . $this->payment->amount
                            . ' للطلب #' . $this->payment->order_id,
        ];
    }
}


