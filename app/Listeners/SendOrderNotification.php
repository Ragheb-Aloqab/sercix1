<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderCreated;
use App\Models\User;
use App\Notifications\NewOrderForAdmin;
use App\Services\ActivityLogger;
class SendOrderNotification
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
    public function handle(OrderCreated $event): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(
                new NewOrderForAdmin($event->order)
            );
        }
      ActivityLogger::log(
            action: 'order_created',
            subjectType: 'order',
            subjectId: $event->order->id,
            description: 'تم إنشاء طلب جديد  '
    );
      
    }
}
