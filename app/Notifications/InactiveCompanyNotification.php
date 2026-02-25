<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InactiveCompanyNotification extends Notification
{
    use Queueable;

    public function __construct(public Company $company, public int $daysInactive) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inactive_company',
            'title' => __('admin_dashboard.alert_inactive_company'),
            'message' => $this->company->company_name . ' — ' . $this->daysInactive . ' ' . __('common.days') . ' no activity',
            'company_id' => $this->company->id,
            'url' => route('admin.companies.show', $this->company),
        ];
    }
}
