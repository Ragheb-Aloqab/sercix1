<?php

namespace App\Http\Controllers\Company;

use App\Exports\TaxReportExport;
use App\Http\Controllers\Controller;
use App\Services\TaxReportPdfService;
use App\Services\TaxReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class TaxReportController extends Controller
{
    public function __construct(
        private readonly TaxReportService $taxReportService,
        private readonly TaxReportPdfService $pdfService
    ) {}

    /**
     * Get report data from request (shared by index and exports).
     */
    private function getDataFromRequest(Request $request): array
    {
        $company = auth('company')->user();

        $dateFrom = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();
        $dateTo = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        $vehicleId = $request->filled('vehicle_id') ? (int) $request->vehicle_id : null;
        if ($vehicleId && !$company->vehicles()->where('id', $vehicleId)->exists()) {
            $vehicleId = null;
        }

        $data = $this->taxReportService->getReportData(
            $company->id,
            $vehicleId,
            $dateFrom,
            $dateTo
        );

        $vehicleLabel = null;
        if ($vehicleId) {
            $v = $company->vehicles()->find($vehicleId);
            $vehicleLabel = $v ? ($v->plate_number . ' — ' . trim(($v->make ?? '') . ' ' . ($v->model ?? ''))) : null;
        }

        return [
            'data' => $data,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'vehicleId' => $vehicleId,
            'vehicleLabel' => $vehicleLabel,
            'company' => $company,
        ];
    }

    /**
     * GET /company/reports/tax
     * Tax Reports page with vehicle filter.
     */
    public function index(Request $request)
    {
        $result = $this->getDataFromRequest($request);
        $company = $result['company'];

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('company.reports.tax', [
            'data' => $result['data'],
            'vehicles' => $vehicles,
            'vehicleId' => $result['vehicleId'],
            'dateFrom' => $result['dateFrom'],
            'dateTo' => $result['dateTo'],
        ]);
    }

    /**
     * Export Tax Report as PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $result = $this->getDataFromRequest($request);

        $pdfContent = $this->pdfService->generate(
            $result['company'],
            $result['data'],
            $result['dateFrom'],
            $result['dateTo'],
            $result['vehicleLabel']
        );

        $filename = 'tax-report-' . now()->format('Y-m-d') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export Tax Report as Excel.
     */
    public function exportExcel(Request $request)
    {
        $result = $this->getDataFromRequest($request);

        $filename = 'tax-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new TaxReportExport($result['data']),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
