<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Exports\MileageReportExport;
use App\Services\VehicleMileageService;
use App\Services\MileageReportPdfService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MileageReportController extends Controller
{
    /**
     * Export company mileage report as Excel.
     */
    public function exportExcel(Request $request)
    {
        $company = auth('company')->user();
        $months = (int) $request->get('months', 6);
        $months = min(max($months, 1), 24);

        $mileageService = app(VehicleMileageService::class);
        $report = $mileageService->getCompanyMonthlySummary($company->id, $months);

        $filename = 'mileage-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new MileageReportExport($report), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Export company mileage report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $company = auth('company')->user();
        $months = (int) $request->get('months', 6);
        $months = min(max($months, 1), 24);

        $pdfService = app(MileageReportPdfService::class);
        $pdfContent = $pdfService->generate($company->id, $months);

        $filename = 'mileage-report-' . now()->format('Y-m-d') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
