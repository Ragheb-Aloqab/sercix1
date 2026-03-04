<?php

namespace App\Listeners;

use App\Events\PaymentPaid;
use App\Notifications\PaymentPaidNotification;
use App\Models\User;
use App\Services\DriverNotificationService;
use App\Support\OrderStatus;

class NotifyPaymentPaid
{
    public function handle(PaymentPaid $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;

        if (!$order) {
            return;
        }

        User::where('role', 'admin')->where('status', 'active')->each(
            fn ($admin) => $admin->notify(new PaymentPaidNotification($payment))
        );
        $order->company?->notify(new PaymentPaidNotification($payment));

        if ($order->status === OrderStatus::COMPLETED) {
            app(DriverNotificationService::class)->notifyOrderCompleted($order);
        }
    }
}
