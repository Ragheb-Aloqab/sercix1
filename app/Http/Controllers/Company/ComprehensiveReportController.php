<?php

namespace App\Http\Controllers\Company;

use App\Exports\ComprehensiveReportExport;
use App\Http\Controllers\Controller;
use App\Services\ComprehensiveReportPdfService;
use App\Services\ComprehensiveReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ComprehensiveReportController extends Controller
{
    public function __construct(
        private readonly ComprehensiveReportService $reportService,
        private readonly ComprehensiveReportPdfService $pdfService
    ) {}

    /**
     * Display the Comprehensive Report page.
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();
        $data = $this->reportService->getReportDataFromRequest($request, $company->id);

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('company.reports.comprehensive', [
            'data' => $data,
            'vehicles' => $vehicles,
            'company' => $company,
        ]);
    }

    /**
     * Export Comprehensive Report as PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $company = auth('company')->user();
        $data = $this->reportService->getReportDataFromRequest($request, $company->id);

        $pdfContent = $this->pdfService->generate($company, $data);

        $filename = $this->exportFilename($data, 'pdf');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export Comprehensive Report as Excel.
     */
    public function exportExcel(Request $request)
    {
        $company = auth('company')->user();
        $data = $this->reportService->getReportDataFromRequest($request, $company->id);

        $filename = $this->exportFilename($data, 'xlsx');

        return Excel::download(
            new ComprehensiveReportExport($data),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Build export filename from current filter (all vehicles or specific vehicle).
     */
    private function exportFilename(array $data, string $ext): string
    {
        $date = now()->format('Y-m-d');
        $scope = $data['vehicle_scope_label'] ?? '';
        if (!empty($data['vehicle_id']) && $scope !== '') {
            $slug = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $scope);
            $slug = trim(preg_replace('/-+/', '-', $slug), '-');
            $base = $slug !== '' ? 'comprehensive-report-' . $slug : 'comprehensive-report';
        } else {
            $base = 'comprehensive-report-all-vehicles';
        }
        return $base . '-' . $date . '.' . $ext;
    }
}
