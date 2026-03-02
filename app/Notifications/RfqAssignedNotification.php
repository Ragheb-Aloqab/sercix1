<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RfqAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenanceRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $vehicle = $this->maintenanceRequest->vehicle;
        $vehicleInfo = $vehicle ? ($vehicle->plate_number . ' — ' . ($vehicle->display_name ?? '')) : __('maintenance.maintenance_request');

        return [
            'type' => 'rfq_assigned',
            'title' => __('maintenance.rfq_assigned_title'),
            'message' => __('maintenance.rfq_assigned_message', [
                'id' => $this->maintenanceRequest->id,
                'vehicle' => $vehicleInfo,
            ]),
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'url' => route('maintenance-center.rfq.show', $this->maintenanceRequest->id),
            'route' => route('maintenance-center.rfq.show', $this->maintenanceRequest->id),
        ];
    }
}
