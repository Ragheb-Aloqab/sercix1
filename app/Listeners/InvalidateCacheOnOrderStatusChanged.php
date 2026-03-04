<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;

class InvalidateCacheOnOrderStatusChanged
{
    public function handle(OrderStatusChanged $event): void
    {
        InvalidateCompanyAnalyticsCache::forCompany($event->order->company_id);
    }
}
