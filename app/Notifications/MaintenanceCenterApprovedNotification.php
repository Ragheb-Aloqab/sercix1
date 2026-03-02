<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceCenterApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenanceRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'maintenance_center_approved',
            'title' => __('maintenance.center_approved_title'),
            'message' => str_replace(':id', (string) $this->maintenanceRequest->id, __('maintenance.center_approved_message')),
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'url' => route('maintenance-center.rfq.show', $this->maintenanceRequest->id),
            'route' => route('maintenance-center.rfq.show', $this->maintenanceRequest->id),
        ];
    }
}
