<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExpiryMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VehicleDocumentExpiryController extends Controller
{
    public function __construct(
        private ExpiryMonitoringService $expiryService
    ) {}

    /**
     * List vehicles with expiring/expired documents.
     */
    public function index(Request $request)
    {
        $companyId = $request->integer('company_id', 0) ?: null;
        $filter = $request->string('filter')->toString();
        if (!in_array($filter, ['', 'expiring_soon', 'expired'])) {
            $filter = null;
        }

        $items = $this->expiryService->getExpiringForAdmin($companyId, $filter ?: null);
        $companies = \App\Models\Company::orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.vehicles.expiring-documents', [
            'items' => $items,
            'companies' => $companies,
            'companyId' => $companyId,
            'filter' => $filter,
            'expiryService' => $this->expiryService,
        ]);
    }

    /**
     * Export expiring documents report as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $companyId = $request->integer('company_id', 0) ?: null;
        $filter = $request->string('filter')->toString();
        if (!in_array($filter, ['', 'expiring_soon', 'expired'])) {
            $filter = null;
        }

        $items = $this->expiryService->getExpiringForAdmin($companyId, $filter ?: null);
        $filename = 'vehicle_document_expiry_' . now()->format('Y-m-d_His') . '.csv';

        return Response::streamDownload(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Vehicle ID', 'Plate', 'Make/Model', 'Company', 'Document Type', 'Status', 'Expiry Date', 'Days Remaining',
            ]);

            foreach ($items as $i) {
                fputcsv($handle, [
                    $i->vehicle->id,
                    $i->vehicle->plate_number ?? '',
                    trim(($i->vehicle->make ?? '') . ' ' . ($i->vehicle->model ?? '')),
                    $i->vehicle->company?->company_name ?? '',
                    $i->type === ExpiryMonitoringService::DOC_REGISTRATION ? __('vehicles.registration') : __('vehicles.insurance'),
                    __('vehicles.' . $i->status),
                    $i->date?->format('Y-m-d') ?? '',
                    $i->days_remaining ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export as Excel.
     */
    public function exportExcel(Request $request)
    {
        $companyId = $request->integer('company_id', 0) ?: null;
        $filter = $request->string('filter')->toString();
        if (!in_array($filter, ['', 'expiring_soon', 'expired'])) {
            $filter = null;
        }

        $items = $this->expiryService->getExpiringForAdmin($companyId, $filter ?: null);
        $filename = 'vehicle_document_expiry_' . now()->format('Y-m-d_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\VehicleDocumentExpiryExport($items),
            $filename
        );
    }
}
