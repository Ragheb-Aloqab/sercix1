<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyFuelInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FuelInvoiceController extends Controller
{
    /**
     * Stream company-uploaded fuel invoice for view (image inline, PDF inline).
     */
    public function view(CompanyFuelInvoice $companyFuelInvoice): Response
    {
        $company = auth('company')->user();
        if ((int) $companyFuelInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        $path = $companyFuelInvoice->invoice_file;
        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        $filename = $companyFuelInvoice->original_filename ?? 'invoice-' . $companyFuelInvoice->id;

        if ($companyFuelInvoice->isPdf()) {
            $mime = 'application/pdf';
            $stream = Storage::disk('private')->readStream($path);
            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ]);
        }

        $mime = Storage::disk('private')->mimeType($path);
        $stream = Storage::disk('private')->readStream($path);
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    /**
     * Download company-uploaded fuel invoice. Images converted to CamScanner-style PDF.
     */
    public function download(CompanyFuelInvoice $companyFuelInvoice): Response
    {
        $company = auth('company')->user();
        if ((int) $companyFuelInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        $path = $companyFuelInvoice->invoice_file;
        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        $filename = $companyFuelInvoice->original_filename ?? 'invoice-' . $companyFuelInvoice->id;

        if ($companyFuelInvoice->isImage()) {
            $pdfContent = app(\App\Services\MaintenanceInvoicePdfService::class)->generateFromImageOnDisk($path, 'private');
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.pdf';
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
            ]);
        }

        return Storage::disk('private')->download($path, $filename);
    }

    /**
     * Serve thumbnail for company-uploaded image fuel invoice (for list preview).
     */
    public function thumbnail(CompanyFuelInvoice $companyFuelInvoice): Response
    {
        $company = auth('company')->user();
        if ((int) $companyFuelInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        if (!$companyFuelInvoice->isImage()) {
            abort(404);
        }

        $path = $companyFuelInvoice->invoice_file;
        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        $mime = Storage::disk('private')->mimeType($path);
        $content = Storage::disk('private')->get($path);

        return response($content, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
