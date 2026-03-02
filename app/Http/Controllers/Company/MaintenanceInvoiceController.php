<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Services\MaintenanceInvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaintenanceInvoiceController extends Controller
{
    /**
     * List all maintenance requests with final invoices (archive).
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();

        $requests = MaintenanceRequest::forCompany($company->id)
            ->whereNotNull('final_invoice_pdf_path')
            ->with(['vehicle', 'approvedCenter'])
            ->latest('final_invoice_uploaded_at')
            ->paginate(20);

        $totalMaintenanceCost = MaintenanceRequest::forCompany($company->id)
            ->whereNotNull('final_invoice_amount')
            ->sum('final_invoice_amount');

        return view('company.maintenance-invoices.index', [
            'requests' => $requests,
            'totalMaintenanceCost' => $totalMaintenanceCost,
        ]);
    }

    /**
     * Stream invoice file for inline view or download.
     * Images are converted to CamScanner-style PDF on-the-fly.
     */
    public function stream(MaintenanceRequest $maintenanceRequest, Request $request): Response
    {
        $company = auth('company')->user();
        if ((int) $maintenanceRequest->company_id !== (int) $company->id) {
            abort(403);
        }

        if (!$maintenanceRequest->final_invoice_pdf_path) {
            abort(404);
        }

        $path = $maintenanceRequest->final_invoice_pdf_path;
        $filename = $maintenanceRequest->final_invoice_original_name ?? 'invoice';
        $disk = Storage::disk('private')->exists($path) ? 'private' : (Storage::disk('public')->exists($path) ? 'public' : null);
        if (!$disk) {
            abort(404);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $pdfContent = app(MaintenanceInvoicePdfService::class)->generateFromImageOnDisk($path, $disk);
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.pdf';
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ]);
        }

        $mime = Storage::disk($disk)->mimeType($path);
        $stream = Storage::disk($disk)->readStream($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    /**
     * Download invoice file. Images are converted to CamScanner-style PDF on-the-fly.
     */
    public function download(MaintenanceRequest $maintenanceRequest): Response
    {
        $company = auth('company')->user();
        if ((int) $maintenanceRequest->company_id !== (int) $company->id) {
            abort(403);
        }

        if (!$maintenanceRequest->final_invoice_pdf_path) {
            abort(404);
        }

        $path = $maintenanceRequest->final_invoice_pdf_path;
        $filename = $maintenanceRequest->final_invoice_original_name ?? 'invoice-' . $maintenanceRequest->id;
        $disk = Storage::disk('private')->exists($path) ? 'private' : (Storage::disk('public')->exists($path) ? 'public' : null);
        if (!$disk) {
            abort(404);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $pdfContent = app(MaintenanceInvoicePdfService::class)->generateFromImageOnDisk($path, $disk);
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.pdf';
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
            ]);
        }

        $filename = $filename ?: 'invoice-' . $maintenanceRequest->id . '.' . pathinfo($path, PATHINFO_EXTENSION);
        if ($disk === 'private') {
            return Storage::disk('private')->download($path, $filename);
        }
        return Storage::disk('public')->download($path, $filename);
    }
}
