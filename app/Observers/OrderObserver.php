<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use App\Events\OrderCreated;
use App\Events\OrderAssignedToTechnician;
use App\Notifications\DriverServiceRequestNotification;
use App\Notifications\NewOrderForAdmin;
use App\Services\InvoicePdfService;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ($order->status === 'requested') {
            // Driver submitted: notify company (so they can approve)
            $company = $order->company;
            if ($company) {
                $company->notify(new DriverServiceRequestNotification($order));
            }
        } else {
            event(new OrderCreated($order));
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Company approved request: notify admin
        if ($order->wasChanged('status') && $order->status === 'pending' && $order->getOriginal('status') === 'requested') {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new NewOrderForAdmin($order));
            }
        }

        // Auto-create invoice when order is completed
        if ($order->wasChanged('status') && $order->status === 'completed') {
            $this->createInvoiceForOrder($order);
        }

        if (
            $order->isDirty('technician_id') &&
            $order->technician_id !== null
        ) {
            event(
                new OrderAssignedToTechnician(
                    $order,
                    $order->technician
                )
            );
        }
    }
    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    private function createInvoiceForOrder(Order $order): void
    {
        if ($order->invoice()->exists()) {
            return;
        }

        $order->load(['services', 'payments']);
        $subtotal = (float) $order->total_amount;
        $tax = (float) ($order->tax_amount ?? 0);
        $paid = (float) $order->payments()->where('status', 'paid')->sum('amount');

        $invoice = $order->invoice()->create([
            'company_id' => $order->company_id,
            'invoice_number' => 'INV-' . $order->id . '-' . now()->format('Ymd'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'paid_amount' => $paid,
        ]);

        try {
            app(InvoicePdfService::class)->generate($invoice);
        } catch (\Throwable $e) {
            report($e);
        }

        $total = $subtotal + $tax;
        $remaining = max(0, $total - $paid);
        if ($remaining > 0 && $order->payments()->where('status', 'pending')->count() === 0) {
            Payment::create([
                'order_id' => $order->id,
                'method' => 'cash',
                'status' => 'pending',
                'amount' => $remaining,
            ]);
        }
    }
}
