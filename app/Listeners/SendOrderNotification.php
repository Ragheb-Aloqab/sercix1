<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\User;
use App\Notifications\NewOrderForAdmin;
use App\Services\ActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        ActivityLogger::log(
            action: 'order_created',
            subjectType: 'order',
            subjectId: $order->id,
            description: __('messages.order_created') ?: 'New order created #' . $order->id,
        );

        User::where('role', 'admin')->where('status', 'active')->each(fn ($admin) => $admin->notify(new NewOrderForAdmin($order)));
    }
}
