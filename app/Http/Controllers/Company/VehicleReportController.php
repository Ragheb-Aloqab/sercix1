<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateVehicleReportJob;
use App\Models\Vehicle;
use App\Exports\VehicleReportExport;
use App\Services\SubscriptionService;
use App\Services\VehicleReportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class VehicleReportController extends Controller
{
    /**
     * Export vehicle report as Excel.
     * Pass queue=1 to generate in background and receive a notification when ready.
     */
    public function exportExcel(Request $request, Vehicle $vehicle): Response|RedirectResponse
    {
        $this->authorize('view', $vehicle);
        SubscriptionService::authorize($vehicle->company, 'vehicle_cost_reports');

        if ($request->boolean('queue')) {
            return $this->dispatchVehicleReportJob($request, $vehicle, 'excel');
        }

        $type = $request->string('type', 'all')->toString();
        if (!in_array($type, ['fuel', 'maintenance', 'all'], true)) {
            $type = 'all';
        }
        $dateFrom = $request->string('date_from')->toString() ?: null;
        $dateTo = $request->string('date_to')->toString() ?: null;

        $filename = 'vehicle-report-' . ($vehicle->plate_number ?? $vehicle->id) . '-' . $type . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new VehicleReportExport($vehicle, $type, $dateFrom, $dateTo),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export vehicle report as PDF.
     * Pass queue=1 to generate in background and receive a notification when ready.
     */
    public function exportPdf(Request $request, Vehicle $vehicle): Response|RedirectResponse
    {
        $this->authorize('view', $vehicle);
        SubscriptionService::authorize($vehicle->company, 'vehicle_cost_reports');

        if ($request->boolean('queue')) {
            return $this->dispatchVehicleReportJob($request, $vehicle, 'pdf');
        }

        $type = $request->string('type', 'all')->toString();
        if (!in_array($type, ['fuel', 'maintenance', 'all'], true)) {
            $type = 'all';
        }
        $dateFrom = $request->string('date_from')->toString() ?: null;
        $dateTo = $request->string('date_to')->toString() ?: null;

        $pdfService = app(VehicleReportPdfService::class);
        $pdfContent = $pdfService->generate($vehicle, $type, $dateFrom, $dateTo);

        $filename = 'vehicle-report-' . ($vehicle->plate_number ?? $vehicle->id) . '-' . $type . '-' . now()->format('Y-m-d') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function dispatchVehicleReportJob(Request $request, Vehicle $vehicle, string $format): RedirectResponse
    {
        $type = $request->string('type', 'all')->toString();
        if (!in_array($type, ['fuel', 'maintenance', 'all'], true)) {
            $type = 'all';
        }
        $dateFrom = $request->string('date_from')->toString() ?: null;
        $dateTo = $request->string('date_to')->toString() ?: null;

        $company = $vehicle->company;
        if (!$company) {
            return back()->with('error', __('messages.error') ?? 'Error');
        }

        GenerateVehicleReportJob::dispatch($vehicle, $company, $format, $type, $dateFrom, $dateTo);

        return back()->with('success', __('reports.queued_for_generation'));
    }
}
