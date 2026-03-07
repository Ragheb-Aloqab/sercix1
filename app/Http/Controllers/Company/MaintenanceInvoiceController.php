<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\MaintenanceRequest;
use App\Models\Vehicle;
use App\Services\MaintenanceInvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceInvoiceController extends Controller
{
    protected function getInvoiceValidationRules(): array
    {
        $maxMb = config('servx.invoice_max_size_mb', 5);
        return [
            'invoice_file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:' . ($maxMb * 1024),
            ],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'tax_type' => ['nullable', 'in:without_tax,with_tax'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Show form to add a new maintenance invoice.
     */
    public function create()
    {
        return view('company.maintenance-invoices.create');
    }

    /**
     * List all maintenance requests with final invoices + company-uploaded invoices.
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();

        $requests = MaintenanceRequest::forCompany($company->id)
            ->whereNotNull('final_invoice_pdf_path')
            ->with(['vehicle', 'approvedCenter'])
            ->latest('final_invoice_uploaded_at')
            ->paginate(15);

        $companyInvoices = CompanyMaintenanceInvoice::where('company_id', $company->id)
            ->with('vehicle')
            ->latest()
            ->get();

        $totalMaintenanceCost = MaintenanceRequest::forCompany($company->id)
            ->whereNotNull('final_invoice_amount')
            ->sum('final_invoice_amount');

        $totalCompanyInvoicesCost = CompanyMaintenanceInvoice::where('company_id', $company->id)
            ->sum('amount');

        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        $maxFileMb = config('servx.invoice_max_size_mb', 5);

        return view('company.maintenance-invoices.index', [
            'requests' => $requests,
            'companyInvoices' => $companyInvoices,
            'totalMaintenanceCost' => $totalMaintenanceCost,
            'totalCompanyInvoicesCost' => $totalCompanyInvoicesCost,
            'vehicles' => $vehicles,
            'maxFileMb' => $maxFileMb,
        ]);
    }

    /**
     * Store company-uploaded maintenance invoice.
     */
    public function store(Request $request)
    {
        $company = auth('company')->user();

        $maxMb = config('servx.invoice_max_size_mb', 5);
        $rules = $this->getInvoiceValidationRules();
        $rules['invoice_file'] = [
            'required',
            'file',
            'mimes:jpg,jpeg,png,webp,pdf',
            'max:' . ($maxMb * 1024),
        ];

        $validated = $request->validate($rules, [
            'invoice_file.required' => __('maintenance.invoice_validation_type'),
            'invoice_file.mimes' => __('maintenance.invoice_validation_type'),
            'invoice_file.max' => __('maintenance.invoice_validation_size', ['max' => $maxMb]),
        ]);

        $file = $request->file('invoice_file');
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
        $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) ? 'image' : 'pdf';
        $originalName = $file->getClientOriginalName();
        $uniqueName = Str::uuid() . '.' . $ext;
        $path = $file->storeAs('invoices/' . $company->id, $uniqueName, 'private');

        $originalAmount = isset($validated['amount']) ? (float) $validated['amount'] : null;
        $taxType = $validated['tax_type'] ?? CompanyMaintenanceInvoice::TAX_WITHOUT;
        $vatAmount = null;
        $totalAmount = $originalAmount;

        if ($originalAmount !== null && $taxType === CompanyMaintenanceInvoice::TAX_WITH) {
            $vatAmount = round($originalAmount * CompanyMaintenanceInvoice::VAT_RATE, 2);
            $totalAmount = round($originalAmount + $vatAmount, 2);
        }

        CompanyMaintenanceInvoice::create([
            'company_id' => $company->id,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'amount' => $totalAmount,
            'original_amount' => $originalAmount,
            'vat_amount' => $vatAmount,
            'tax_type' => $taxType,
            'invoice_file' => $path,
            'file_type' => $fileType,
            'original_filename' => $originalName,
            'description' => $validated['description'] ?? null,
        ]);

        \App\Listeners\InvalidateCompanyAnalyticsCache::forCompany($company->id);
        if (!empty($validated['vehicle_id'])) {
            \App\Listeners\InvalidateCompanyAnalyticsCache::forVehicle((int) $validated['vehicle_id']);
        }

        return redirect()
            ->route('company.maintenance-invoices.index')
            ->with('success', __('maintenance.invoice_uploaded_success'));
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

    /**
     * Stream company-uploaded invoice for view (image inline, PDF inline).
     */
    public function streamCompanyInvoice(CompanyMaintenanceInvoice $companyMaintenanceInvoice): Response
    {
        $company = auth('company')->user();
        if ((int) $companyMaintenanceInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        $path = $companyMaintenanceInvoice->invoice_file;
        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        $filename = $companyMaintenanceInvoice->original_filename ?? 'invoice-' . $companyMaintenanceInvoice->id;

        if ($companyMaintenanceInvoice->isPdf()) {
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
     * Download company-uploaded invoice. Images converted to CamScanner-style PDF.
     */
    public function downloadCompanyInvoice(CompanyMaintenanceInvoice $companyMaintenanceInvoice): Response
    {
        $company = auth('company')->user();
        if ((int) $companyMaintenanceInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        $path = $companyMaintenanceInvoice->invoice_file;
        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        $filename = $companyMaintenanceInvoice->original_filename ?? 'invoice-' . $companyMaintenanceInvoice->id;

        if ($companyMaintenanceInvoice->isImage()) {
            $pdfContent = app(MaintenanceInvoicePdfService::class)->generateFromImageOnDisk($path, 'private');
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
     * Serve thumbnail for company-uploaded image invoice (for list preview).
     */
    public function thumbnailCompanyInvoice(CompanyMaintenanceInvoice $companyMaintenanceInvoice): Response
    {
        $company = auth('company')->user();
        if ((int) $companyMaintenanceInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        if (!$companyMaintenanceInvoice->hasInvoiceFile() || !$companyMaintenanceInvoice->isImage()) {
            abort(404);
        }

        $path = $companyMaintenanceInvoice->invoice_file;
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
