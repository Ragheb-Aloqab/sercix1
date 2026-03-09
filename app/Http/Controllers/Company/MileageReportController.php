<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Exports\MileageReportExport;
use App\Exports\VehicleMileageReportExport;
use App\Jobs\GenerateMileageReportJob;
use App\Services\SubscriptionService;
use App\Services\VehicleMileageService;
use App\Services\VehicleMileageReportService;
use App\Services\MileageReportPdfService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class MileageReportController extends Controller
{
    /**
     * Export company mileage report as Excel.
     * Supports both: monthly summary (months param) and vehicle mileage report (from/to params).
     * Pass queue=1 to generate in background and receive a notification when ready.
     */
    public function exportExcel(Request $request): Response|RedirectResponse
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'distance_reports');

        if ($request->boolean('queue')) {
            return $this->dispatchExportJob($company, 'excel', $request);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to = Carbon::parse($request->to)->endOfDay();
            $vehicleId = $request->filled('vehicle_id') ? (int) $request->vehicle_id : null;
            $branchId = $request->filled('branch_id') ? (int) $request->branch_id : null;

            $service = app(VehicleMileageReportService::class);
            $result = $service->getReport($company->id, $from, $to, $vehicleId, $branchId, 'total_distance', 'desc');

            $rows = collect($result['rows'])->map(function ($r) {
                $r['status_label'] = __("vehicles.status_{$r['status']}");
                return $r;
            })->all();

            return Excel::download(new VehicleMileageReportExport($rows), 'vehicle-mileage-report-' . now()->format('Y-m-d') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }

        $months = (int) $request->get('months', 6);
        $months = min(max($months, 1), 24);

        $mileageService = app(VehicleMileageService::class);
        $report = $mileageService->getCompanyMonthlySummary($company->id, $months);

        return Excel::download(new MileageReportExport($report), 'mileage-report-' . now()->format('Y-m-d') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Export company mileage report as PDF.
     * Supports both: monthly summary (months param) and vehicle mileage report (from/to params).
     * Pass queue=1 to generate in background and receive a notification when ready.
     */
    public function exportPdf(Request $request): Response|RedirectResponse
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'distance_reports');

        if ($request->boolean('queue')) {
            return $this->dispatchExportJob($company, 'pdf', $request);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to = Carbon::parse($request->to)->endOfDay();
            $vehicleId = $request->filled('vehicle_id') ? (int) $request->vehicle_id : null;
            $branchId = $request->filled('branch_id') ? (int) $request->branch_id : null;

            $service = app(VehicleMileageReportService::class);
            $result = $service->getReport($company->id, $from, $to, $vehicleId, $branchId, 'total_distance', 'desc');

            $pdfService = app(MileageReportPdfService::class);
            $pdfContent = $pdfService->generateVehicleReport($company->id, $result['rows'], $result['summary'], $from, $to);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="vehicle-mileage-report-' . now()->format('Y-m-d') . '.pdf"',
            ]);
        }

        $months = (int) $request->get('months', 6);
        $months = min(max($months, 1), 24);

        $pdfService = app(MileageReportPdfService::class);
        $pdfContent = $pdfService->generate($company->id, $months);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="mileage-report-' . now()->format('Y-m-d') . '.pdf"',
        ]);
    }

    private function dispatchExportJob($company, string $format, Request $request): RedirectResponse
    {
        $from = null;
        $to = null;
        $vehicleId = null;
        $branchId = null;
        $months = (int) $request->get('months', 6);
        $months = min(max($months, 1), 24);

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to = Carbon::parse($request->to)->endOfDay();
            $vehicleId = $request->filled('vehicle_id') ? (int) $request->vehicle_id : null;
            $branchId = $request->filled('branch_id') ? (int) $request->branch_id : null;
        }

        GenerateMileageReportJob::dispatch($company, $format, $from, $to, $vehicleId, $branchId, $months);

        return back()->with('success', __('reports.queued_for_generation') ?? 'Report is being generated. You will be notified when it is ready.');
    }
}
