<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Services\MaintenanceInvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateMaintenanceInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Attachment $attachment
    ) {}

    public function handle(MaintenanceInvoicePdfService $service): void
    {
        $attachment = $this->attachment;

        if ($attachment->type !== 'driver_invoice') {
            return;
        }

        $ext = strtolower(pathinfo($attachment->file_path ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            return;
        }

        // Delete old PDF if exists
        if ($attachment->maintenance_invoice_pdf_path) {
            Storage::disk('public')->delete($attachment->maintenance_invoice_pdf_path);
        }

        $path = $service->generateAndSave($attachment);
        $attachment->update(['maintenance_invoice_pdf_path' => $path]);
    }
}
