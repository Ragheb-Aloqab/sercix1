<?php

namespace App\Listeners;

use App\Events\MaintenanceRequestApproved;
use App\Notifications\MaintenanceInvoiceApprovedNotification;
use App\Services\DriverNotificationService;

class NotifyMaintenanceRequestApproved
{
    public function handle(MaintenanceRequestApproved $event): void
    {
        $request = $event->maintenanceRequest;
        $request->load('approvedCenter');

        $center = $request->approvedCenter;
        if ($center) {
            $center->notify(new MaintenanceInvoiceApprovedNotification($request));
        }
        app(DriverNotificationService::class)->notifyMaintenanceRequestClosed($request);
    }
}
