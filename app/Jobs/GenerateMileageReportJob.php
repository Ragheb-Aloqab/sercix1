<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\ReportExport;
use App\Notifications\ReportReadyNotification;
use App\Services\MileageReportPdfService;
use App\Services\VehicleMileageReportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VehicleMileageReportExport;
use App\Exports\MileageReportExport;

class GenerateMileageReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Company $company,
        public readonly string $format, // 'pdf' | 'excel'
        public readonly ?Carbon $from = null,
        public readonly ?Carbon $to = null,
        public readonly ?int $vehicleId = null,
        public readonly ?int $branchId = null,
        public readonly int $months = 6
    ) {}

    public function handle(): void
    {
        $filename = 'mileage-report-' . now()->format('Y-m-d-His') . '.' . ($this->format === 'excel' ? 'xlsx' : 'pdf');
        $path = 'report_exports/' . $this->company->id . '/' . $filename;

        if ($this->format === 'pdf') {
            $content = $this->generatePdf();
            Storage::disk('local')->put($path, $content);
        } else {
            $this->generateExcel($path);
        }

        $export = ReportExport::create([
            'id' => Str::uuid()->toString(),
            'notifiable_type' => Company::class,
            'notifiable_id' => $this->company->id,
            'type' => 'mileage_' . $this->format,
            'file_path' => $path,
            'filename' => $filename,
            'expires_at' => now()->addHours(24),
        ]);

        $this->company->notify(new ReportReadyNotification($export));
    }

    private function generatePdf(): string
    {
        $pdfService = app(MileageReportPdfService::class);

        if ($this->from && $this->to) {
            $service = app(VehicleMileageReportService::class);
            $result = $service->getReport(
                $this->company->id,
                $this->from,
                $this->to,
                $this->vehicleId,
                $this->branchId,
                'total_distance',
                'desc'
            );
            return $pdfService->generateVehicleReport(
                $this->company->id,
                $result['rows'],
                $result['summary'],
                $this->from,
                $this->to
            );
        }

        return $pdfService->generate($this->company->id, $this->months);
    }

    private function generateExcel(string $path): void
    {
        if ($this->from && $this->to) {
            $service = app(VehicleMileageReportService::class);
            $result = $service->getReport(
                $this->company->id,
                $this->from,
                $this->to,
                $this->vehicleId,
                $this->branchId,
                'total_distance',
                'desc'
            );
            $rows = collect($result['rows'])->map(fn ($r) => array_merge($r, ['status_label' => __("vehicles.status_{$r['status']}")]))->all();
            Excel::store(new VehicleMileageReportExport($rows), $path, 'local');
        } else {
            $mileageService = app(\App\Services\VehicleMileageService::class);
            $report = $mileageService->getCompanyMonthlySummary($this->company->id, $this->months);
            Excel::store(new MileageReportExport($report), $path, 'local');
        }
    }

    public function failed(\Throwable $exception): void
    {
        report($exception);
    }
}
