<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceInvoiceUploadedNotification extends Notification
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
            'type' => 'maintenance_invoice_uploaded',
            'title' => __('maintenance.invoice_uploaded_title'),
            'message' => str_replace(':id', (string) $this->maintenanceRequest->id, __('maintenance.invoice_uploaded_message')),
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'url' => route('company.maintenance-requests.show', $this->maintenanceRequest->id),
            'route' => route('company.maintenance-requests.show', $this->maintenanceRequest->id),
        ];
    }
}
