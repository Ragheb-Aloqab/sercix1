<?php

namespace App\Observers;

use App\Models\Order;
use App\Events\OrderCreated;
use App\Events\OrderAssignedToTechnician;
class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        event(new OrderCreated($order));
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
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
}
