<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Exports\VehicleReportExport;
use App\Services\VehicleReportPdfService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VehicleReportController extends Controller
{
    /**
     * Export vehicle report as Excel.
     */
    public function exportExcel(Request $request, Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

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
     */
    public function exportPdf(Request $request, Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

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
}
