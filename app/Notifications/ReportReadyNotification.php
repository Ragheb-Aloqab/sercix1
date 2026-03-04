<?php

namespace App\Notifications;

use App\Models\ReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Broadcast;

class ReportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public ReportExport $export) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (config('broadcasting.default') && config('broadcasting.default') !== 'null') {
            $channels[] = 'broadcast';
        }
        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        $typeLabel = match ($this->export->type) {
            'mileage_pdf' => __('reports.mileage_report') . ' (PDF)',
            'mileage_excel' => __('reports.mileage_report') . ' (Excel)',
            'vehicle_report_pdf' => __('vehicles.vehicle_report') . ' (PDF)',
            'vehicle_report_excel' => __('vehicles.vehicle_report') . ' (Excel)',
            'invoice_pdf' => __('invoice.invoice') . ' (PDF)',
            default => __('reports.report_ready'),
        };

        $url = route('company.reports.download', $this->export);
        return [
            'title' => __('reports.report_ready'),
            'message' => __('reports.report_ready_message', ['type' => $typeLabel]),
            'route' => $url,
            'url' => $url,
            'export_id' => $this->export->id,
            'type' => $this->export->type,
            'filename' => $this->export->filename,
        ];
    }
}
