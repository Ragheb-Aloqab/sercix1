<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\ReportExport;
use App\Notifications\ReportReadyNotification;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly Company $company
    ) {}

    public function handle(): void
    {
        $pdfService = app(InvoicePdfService::class);
        $content = $pdfService->getPdfContent($this->invoice);

        $filename = 'invoice-' . ($this->invoice->invoice_number ?? $this->invoice->id) . '-' . now()->format('Y-m-d-His') . '.pdf';
        $path = 'report_exports/' . $this->company->id . '/invoices/' . $filename;

        Storage::disk('local')->put($path, $content);

        $export = ReportExport::create([
            'id' => Str::uuid()->toString(),
            'notifiable_type' => Company::class,
            'notifiable_id' => $this->company->id,
            'type' => 'invoice_pdf',
            'file_path' => $path,
            'filename' => $filename,
            'expires_at' => now()->addHours(24),
        ]);

        $this->company->notify(new ReportReadyNotification($export));
    }

    public function failed(\Throwable $exception): void
    {
        report($exception);
    }
}
