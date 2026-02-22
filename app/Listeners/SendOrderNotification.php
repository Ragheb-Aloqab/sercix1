<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderCreated;
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
        // Admin notifications removed - only Company ↔ Driver notifications

        ActivityLogger::log(
            action: 'order_created',
            subjectType: 'order',
            subjectId: $event->order->id,
            description: 'تم إنشاء طلب جديد  '
    );
      
    }
}
