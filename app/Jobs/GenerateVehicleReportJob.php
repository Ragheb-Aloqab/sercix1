<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\ReportExport;
use App\Models\Vehicle;
use App\Notifications\ReportReadyNotification;
use App\Services\VehicleReportPdfService;
use App\Exports\VehicleReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class GenerateVehicleReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Vehicle $vehicle,
        public readonly Company $company,
        public readonly string $format, // 'pdf' | 'excel'
        public readonly string $type = 'all', // fuel, maintenance, all
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null
    ) {}

    public function handle(): void
    {
        $ext = $this->format === 'excel' ? 'xlsx' : 'pdf';
        $filename = 'vehicle-report-' . ($this->vehicle->plate_number ?? $this->vehicle->id) . '-' . $this->type . '-' . now()->format('Y-m-d-His') . '.' . $ext;
        $path = 'report_exports/' . $this->company->id . '/vehicles/' . $filename;

        if ($this->format === 'pdf') {
            $pdfService = app(VehicleReportPdfService::class);
            $content = $pdfService->generate($this->vehicle, $this->type, $this->dateFrom, $this->dateTo);
            Storage::disk('local')->put($path, $content);
        } else {
            Excel::store(
                new VehicleReportExport($this->vehicle, $this->type, $this->dateFrom, $this->dateTo),
                $path,
                'local'
            );
        }

        $export = ReportExport::create([
            'id' => Str::uuid()->toString(),
            'notifiable_type' => Company::class,
            'notifiable_id' => $this->company->id,
            'type' => 'vehicle_report_' . $this->format,
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
