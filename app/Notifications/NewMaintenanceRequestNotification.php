<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMaintenanceRequestNotification extends Notification
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
        $plate = $vehicle?->plate_number ?? '-';
        $type = \App\Enums\MaintenanceType::tryFrom($this->maintenanceRequest->maintenance_type)?->label() ?? $this->maintenanceRequest->maintenance_type;

        $driver = $this->maintenanceRequest->requested_by_name ?? __('driver.driver');
        $message = __('maintenance.new_request_message');
        if ($message === 'maintenance.new_request_message') {
            $message = "Driver {$driver} submitted a new maintenance request for vehicle {$plate} ({$type}). Assign to maintenance centers.";
        } else {
            $message = str_replace([':driver', ':vehicle', ':type'], [$driver, $plate, $type], $message);
        }

        return [
            'type' => 'new_maintenance_request',
            'title' => __('maintenance.new_request_title') ?: 'New Maintenance Request',
            'message' => $message,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'url' => route('company.maintenance-requests.show', $this->maintenanceRequest->id),
            'route' => route('company.maintenance-requests.show', $this->maintenanceRequest->id),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
