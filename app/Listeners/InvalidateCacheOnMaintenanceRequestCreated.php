<?php

namespace App\Listeners;

use App\Events\MaintenanceRequestCreated;

class InvalidateCacheOnMaintenanceRequestCreated
{
    public function handle(MaintenanceRequestCreated $event): void
    {
        InvalidateCompanyAnalyticsCache::forCompany($event->maintenanceRequest->company_id);
    }
}
