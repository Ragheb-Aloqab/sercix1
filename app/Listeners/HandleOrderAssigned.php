<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderAssignedToTechnician as AssignedEvent;
use App\Notifications\OrderAssignedToTechnician as AssignedNotification;

use App\Models\User;
use App\Services\ActivityLogger;
class HandleOrderAssigned
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AssignedEvent $event): void
    {
        $tech = User::query()
            ->where('id', $event->technician->id)
            ->where('role', 'technician')
            ->firstOrFail();
        $tech->notify(
            new AssignedNotification($event->order)
        );

        ActivityLogger::log(
            action: 'assigned_order',
            subjectType: 'order',
            subjectId: $event->order->id,
            description: 'تم إسناد الطلب للفني ');
   
    }
}
