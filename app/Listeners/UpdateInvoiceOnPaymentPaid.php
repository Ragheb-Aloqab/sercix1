<?php

namespace App\Listeners;

use App\Events\PaymentPaid;

class UpdateInvoiceOnPaymentPaid
{
    public function handle(PaymentPaid $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;

        if (!$order || $payment->status !== 'paid') {
            return;
        }

        $invoice = $order->invoice;
        if (!$invoice) {
            return;
        }

        $totalPaid = (float) $order->payments()->where('status', 'paid')->sum('amount');
        $invoice->update(['paid_amount' => $totalPaid]);
    }
}
