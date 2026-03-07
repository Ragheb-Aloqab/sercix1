<?php

namespace App\Services\Report;

use App\Exports\ComprehensiveReportExport;
use App\Exports\MileageReportExport;
use App\Exports\TaxReportExport;
use App\Exports\VehicleMileageReportExport;
use App\Exports\VehicleReportExport;
use App\Models\Company;
use App\Models\Vehicle;
use App\Services\ComprehensiveReportPdfService;
use App\Services\ComprehensiveReportService;
use App\Services\MileageReportPdfService;
use App\Services\TaxReportPdfService;
use App\Services\TaxReportService;
use App\Services\VehicleMileageReportService;
use App\Services\VehicleMileageService;
use App\Services\VehicleReportPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Unified Report Export Service.
 * Single entry point: exportReport(type, filters, format).
 * Handles: Tax, Comprehensive, Mileage, Vehicle reports.
 */
class ReportExportService
{
    public const TYPE_TAX = 'tax';

    public const TYPE_COMPREHENSIVE = 'comprehensive';

    public const TYPE_MILEAGE = 'mileage';

    public const TYPE_MILEAGE_VEHICLES = 'mileage_vehicles';

    public const TYPE_VEHICLE = 'vehicle';

    public const FORMAT_PDF = 'pdf';

    public const FORMAT_EXCEL = 'excel';

    public function __construct(
        private readonly TaxReportService $taxReportService,
        private readonly TaxReportPdfService $taxPdfService,
        private readonly ComprehensiveReportService $comprehensiveReportService,
        private readonly ComprehensiveReportPdfService $comprehensivePdfService,
        private readonly VehicleMileageReportService $mileageReportService,
        private readonly VehicleMileageService $mileageService,
        private readonly MileageReportPdfService $mileagePdfService,
        private readonly VehicleReportPdfService $vehiclePdfService,
        private readonly VehicleReportDataProvider $vehicleDataProvider
    ) {}

    /**
     * Export a report in the specified format.
     *
     * @param  string  $type  tax|comprehensive|mileage|mileage_vehicles|vehicle
     * @param  array<string, mixed>  $filters  type-specific filters
     * @param  string  $format  pdf|excel
     * @return \Illuminate\Http\Response|BinaryFileResponse
     */
    public function export(string $type, array $filters, string $format): \Illuminate\Http\Response|BinaryFileResponse
    {
        return $format === self::FORMAT_PDF
            ? $this->exportPdf($type, $filters)
            : $this->exportExcel($type, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportPdf(string $type, array $filters): \Illuminate\Http\Response
    {
        $content = match ($type) {
            self::TYPE_TAX => $this->exportTaxPdf($filters),
            self::TYPE_COMPREHENSIVE => $this->exportComprehensivePdf($filters),
            self::TYPE_MILEAGE => $this->exportMileageMonthlyPdf($filters),
            self::TYPE_MILEAGE_VEHICLES => $this->exportMileageVehiclesPdf($filters),
            self::TYPE_VEHICLE => $this->exportVehiclePdf($filters),
            default => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };

        $filename = $this->getFilename($type, $filters, 'pdf');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportExcel(string $type, array $filters): BinaryFileResponse
    {
        $filename = $this->getFilename($type, $filters, 'xlsx');

        $export = match ($type) {
            self::TYPE_TAX => $this->createTaxExport($filters),
            self::TYPE_COMPREHENSIVE => $this->createComprehensiveExport($filters),
            self::TYPE_MILEAGE => $this->createMileageMonthlyExport($filters),
            self::TYPE_MILEAGE_VEHICLES => $this->createMileageVehiclesExport($filters),
            self::TYPE_VEHICLE => $this->createVehicleExport($filters),
            default => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };

        return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    private function exportTaxPdf(array $filters): string
    {
        $company = $filters['company'] ?? auth('company')->user();
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now()->endOfDay();
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom)->startOfDay();
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo)->endOfDay();
        }

        $data = $filters['data'] ?? $this->taxReportService->getReportData(
            $company->id,
            $filters['vehicle_id'] ?? null,
            $dateFrom,
            $dateTo
        );

        return $this->taxPdfService->generate(
            $company,
            $data,
            $dateFrom->format('Y-m-d'),
            $dateTo->format('Y-m-d'),
            $filters['vehicle_label'] ?? null
        );
    }

    private function exportComprehensivePdf(array $filters): string
    {
        $company = $filters['company'] ?? auth('company')->user();
        $data = $filters['data'] ?? $this->comprehensiveReportService->getReportData(
            $company->id,
            $filters['month'] ?? null,
            $filters['year'] ?? null,
            $filters['vehicle_id'] ?? null
        );

        return $this->comprehensivePdfService->generate($company, $data);
    }

    private function exportMileageMonthlyPdf(array $filters): string
    {
        $companyId = ($filters['company'] ?? auth('company')->user())->id;
        $months = (int) ($filters['months'] ?? 6);
        $months = min(max($months, 1), 24);

        return $this->mileagePdfService->generate($companyId, $months);
    }

    private function exportMileageVehiclesPdf(array $filters): string
    {
        $companyId = ($filters['company'] ?? auth('company')->user())->id;
        $from = $filters['from'] ?? now()->startOfMonth();
        $to = $filters['to'] ?? now()->endOfDay();
        if (!$from instanceof Carbon) {
            $from = Carbon::parse($from)->startOfDay();
        }
        if (!$to instanceof Carbon) {
            $to = Carbon::parse($to)->endOfDay();
        }

        $result = $this->mileageReportService->getReport(
            $companyId,
            $from,
            $to,
            $filters['vehicle_id'] ?? null,
            $filters['branch_id'] ?? null,
            'total_distance',
            'desc'
        );

        return $this->mileagePdfService->generateVehicleReport(
            $companyId,
            $result['rows'],
            $result['summary'],
            $from,
            $to
        );
    }

    private function exportVehiclePdf(array $filters): string
    {
        $data = $this->vehicleDataProvider->getData($filters);
        $vehicle = $data['vehicle'];
        if (!$vehicle) {
            throw new \InvalidArgumentException('Vehicle is required for vehicle report.');
        }

        return $this->vehiclePdfService->generateFromData($data);
    }

    private function createTaxExport(array $filters): TaxReportExport
    {
        $company = $filters['company'] ?? auth('company')->user();
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now()->endOfDay();
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom)->startOfDay();
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo)->endOfDay();
        }

        $data = $filters['data'] ?? $this->taxReportService->getReportData(
            $company->id,
            $filters['vehicle_id'] ?? null,
            $dateFrom,
            $dateTo
        );

        return new TaxReportExport($data, app()->getLocale());
    }

    private function createComprehensiveExport(array $filters): ComprehensiveReportExport
    {
        $company = $filters['company'] ?? auth('company')->user();
        $data = $filters['data'] ?? $this->comprehensiveReportService->getReportData(
            $company->id,
            $filters['month'] ?? null,
            $filters['year'] ?? null,
            $filters['vehicle_id'] ?? null
        );

        return new ComprehensiveReportExport($data);
    }

    private function createMileageMonthlyExport(array $filters): MileageReportExport
    {
        $companyId = ($filters['company'] ?? auth('company')->user())->id;
        $months = (int) ($filters['months'] ?? 6);
        $months = min(max($months, 1), 24);

        $report = $this->mileageService->getCompanyMonthlySummary($companyId, $months);

        return new MileageReportExport($report);
    }

    private function createMileageVehiclesExport(array $filters): VehicleMileageReportExport
    {
        $companyId = ($filters['company'] ?? auth('company')->user())->id;
        $from = $filters['from'] ?? now()->startOfMonth();
        $to = $filters['to'] ?? now()->endOfDay();
        if (!$from instanceof Carbon) {
            $from = Carbon::parse($from)->startOfDay();
        }
        if (!$to instanceof Carbon) {
            $to = Carbon::parse($to)->endOfDay();
        }

        $result = $this->mileageReportService->getReport(
            $companyId,
            $from,
            $to,
            $filters['vehicle_id'] ?? null,
            $filters['branch_id'] ?? null,
            'total_distance',
            'desc'
        );

        $rows = collect($result['rows'])->map(fn ($r) => array_merge($r, ['status_label' => __("vehicles.status_{$r['status']}")]))->all();

        return new VehicleMileageReportExport($rows);
    }

    private function createVehicleExport(array $filters): VehicleReportExport
    {
        $vehicle = $filters['vehicle'] ?? null;
        if (!$vehicle instanceof Vehicle) {
            throw new \InvalidArgumentException('Vehicle is required for vehicle report.');
        }

        return new VehicleReportExport(
            $vehicle,
            $filters['type'] ?? 'all',
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null
        );
    }

    private function getFilename(string $type, array $filters, string $ext): string
    {
        $base = match ($type) {
            self::TYPE_TAX => 'tax-report',
            self::TYPE_COMPREHENSIVE => 'comprehensive-report',
            self::TYPE_MILEAGE => 'mileage-report',
            self::TYPE_MILEAGE_VEHICLES => 'vehicle-mileage-report',
            self::TYPE_VEHICLE => 'vehicle-report-' . (($filters['vehicle'] ?? null)?->plate_number ?? ($filters['vehicle']?->id ?? 'all')) . '-' . ($filters['type'] ?? 'all'),
            default => 'report',
        };

        return $base . '-' . now()->format('Y-m-d') . '.' . $ext;
    }

    /**
     * Build filters from HTTP request for a given report type.
     *
     * @return array<string, mixed>
     */
    public function filtersFromRequest(Request $request, string $type): array
    {
        $company = auth('company')->user();

        return match ($type) {
            self::TYPE_TAX => [
                'company' => $company,
                'vehicle_id' => $request->filled('vehicle_id') ? (int) $request->vehicle_id : null,
                'date_from' => $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->startOfMonth(),
                'date_to' => $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay(),
            ],
            self::TYPE_COMPREHENSIVE => [
                'company' => $company,
                'month' => $request->filled('month') ? (int) $request->month : null,
                'year' => $request->filled('year') ? (int) $request->year : null,
                'vehicle_id' => $request->filled('vehicle_id') ? (int) $request->vehicle_id : null,
            ],
            self::TYPE_MILEAGE => [
                'company' => $company,
                'months' => (int) $request->get('months', 6),
            ],
            self::TYPE_MILEAGE_VEHICLES => [
                'company' => $company,
                'from' => $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null,
                'to' => $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null,
                'vehicle_id' => $request->filled('vehicle_id') ? (int) $request->vehicle_id : null,
                'branch_id' => $request->filled('branch_id') ? (int) $request->branch_id : null,
            ],
            self::TYPE_VEHICLE => [
                'vehicle' => $request->route('vehicle'),
                'type' => $request->string('type', 'all')->toString(),
                'date_from' => $request->string('date_from')->toString() ?: null,
                'date_to' => $request->string('date_to')->toString() ?: null,
            ],
            default => ['company' => $company],
        };
    }
}
