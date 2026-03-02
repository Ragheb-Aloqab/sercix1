<?php

namespace App\Notifications;

use App\Models\MaintenanceCenter;
use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceQuotationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public MaintenanceRequest $maintenanceRequest,
        public ?MaintenanceCenter $center = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $centerName = $this->center?->name ?? $this->maintenanceRequest->quotations()->latest()->first()?->maintenanceCenter?->name ?? __('maintenance.center');

        return [
            'type' => 'maintenance_quotation_submitted',
            'title' => __('maintenance.quotation_submitted_title'),
            'message' => str_replace(':id', (string) $this->maintenanceRequest->id, __('maintenance.quotation_submitted_message')),
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'center_name' => $centerName,
            'url' => route('company.maintenance-requests.show', $this->maintenanceRequest->id),
            'route' => route('company.maintenance-requests.show', $this->maintenanceRequest->id),
        ];
    }
}
