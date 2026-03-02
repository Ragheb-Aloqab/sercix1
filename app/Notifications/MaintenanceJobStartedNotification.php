<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceJobStartedNotification extends Notification
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
            'type' => 'maintenance_job_started',
            'title' => __('maintenance.job_started_title'),
            'message' => str_replace(':id', (string) $this->maintenanceRequest->id, __('maintenance.job_started_message')),
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'url' => route('company.maintenance-requests.show', $this->maintenanceRequest->id),
            'route' => route('company.maintenance-requests.show', $this->maintenanceRequest->id),
        ];
    }
}
