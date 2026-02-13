<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
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

        if ($notifiable instanceof Company) {
            $url = route('company.payments.show', $this->payment->id);
        }

        if (! $url) {
            if ($notifiable instanceof User && $notifiable->role === 'technician') {
                $url = route('tech.tasks.show', $this->payment->order_id);
            } else {
                $url = route('admin.orders.show', $this->payment->order_id);
            }
        }

        $company = $this->payment->relationLoaded('order')
            ? $this->payment->order->company
            : $this->payment->order->company;
        $companyName = $company?->company_name ?? '—';

        $methodLabel = match ($this->payment->method ?? '') {
            'cash' => 'كاش',
            'tap'  => 'Tap',
            'bank' => 'تحويل بنكي',
            default => $this->payment->method ?? '—',
        };

        $amount = number_format((float) $this->payment->amount, 2);
        $title = 'تم استلام دفعة';
        $message = sprintf(
            'شركة %s دفعت %s ر.س (%s) للطلب #%s',
            $companyName,
            $amount,
            $methodLabel,
            $this->payment->order_id
        );

        return [
            'title'         => $title,
            'message'       => $message,
            'payment_id'    => $this->payment->id,
            'order_id'      => $this->payment->order_id,
            'amount'        => $this->payment->amount,
            'company_name'  => $companyName,
            'payment_method'=> $this->payment->method,
            'method_label'  => $methodLabel,
            'url'           => $url,
        ];
    }
}


