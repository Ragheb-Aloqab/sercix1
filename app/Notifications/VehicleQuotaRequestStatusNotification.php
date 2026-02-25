<?php

namespace App\Notifications;

use App\Models\VehicleQuotaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VehicleQuotaRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VehicleQuotaRequest $request
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->request->status;
        $title = $status === VehicleQuotaRequest::STATUS_APPROVED
            ? __('admin_dashboard.quota_request_approved')
            : __('admin_dashboard.quota_request_rejected');

        return [
            'type' => 'vehicle_quota_request',
            'title' => $title,
            'message' => $this->request->admin_note ?? $title,
            'request_id' => $this->request->id,
            'company_id' => $this->request->company_id,
            'status' => $status,
        ];
    }
}
