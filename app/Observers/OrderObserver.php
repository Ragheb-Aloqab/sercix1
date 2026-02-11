<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Events\OrderCreated;
use App\Events\OrderAssignedToTechnician;
use App\Notifications\DriverServiceRequestNotification;
use App\Notifications\NewOrderForAdmin;

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
