<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Listeners\InvalidateCompanyAnalyticsCache;
use App\Models\CompanyFuelInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

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
     * Download company-uploaded fuel invoice as PDF.
     * Images are converted to a single-page PDF; uploaded PDFs are served as-is.
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

        $baseName = pathinfo($companyFuelInvoice->original_filename ?? 'invoice-' . $companyFuelInvoice->id, PATHINFO_FILENAME);
        $filename = $baseName . '.pdf';

        if ($companyFuelInvoice->isImage()) {
            $pdfContent = app(\App\Services\MaintenanceInvoicePdfService::class)->generateFromImageOnDisk($path, 'private');
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
            ]);
        }

        // Already a PDF: stream with explicit PDF headers for download
        $stream = Storage::disk('private')->readStream($path);
        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
        ]);
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

    /**
     * Delete a company-uploaded fuel invoice. Removes file from storage if present.
     */
    public function destroy(CompanyFuelInvoice $companyFuelInvoice): RedirectResponse
    {
        $company = auth('company')->user();
        if ((int) $companyFuelInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        $path = $companyFuelInvoice->invoice_file;
        if ($path && Storage::disk('private')->exists($path)) {
            Storage::disk('private')->delete($path);
        }

        $vehicleId = $companyFuelInvoice->vehicle_id;
        $companyFuelInvoice->delete();

        InvalidateCompanyAnalyticsCache::forCompany($company->id);
        InvalidateCompanyAnalyticsCache::forVehicle($vehicleId);

        $redirect = request()->input('from') === 'invoices'
            ? redirect()->route('company.invoices.index', ['invoice_type' => 'fuel'])->with('success', __('maintenance.invoice_deleted'))
            : redirect()->route('company.fuel.index', request()->only(['from', 'to', 'vehicle_id']))->with('success', __('maintenance.invoice_deleted'));

        return $redirect;
    }

    /**
     * Show edit form for a company-uploaded fuel invoice.
     */
    public function edit(CompanyFuelInvoice $companyFuelInvoice): View
    {
        $company = auth('company')->user();
        if ((int) $companyFuelInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('company.fuel-invoices.edit', [
            'companyFuelInvoice' => $companyFuelInvoice->load('vehicle'),
            'vehicles' => $vehicles,
            'maxFileMb' => config('servx.invoice_max_size_mb', 5),
        ]);
    }

    /**
     * Update a company-uploaded fuel invoice (vehicle, amount, description, optional file replace).
     */
    public function update(Request $request, CompanyFuelInvoice $companyFuelInvoice): RedirectResponse
    {
        $company = auth('company')->user();
        if ((int) $companyFuelInvoice->company_id !== (int) $company->id) {
            abort(403);
        }

        $maxMb = config('servx.invoice_max_size_mb', 5);
        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'invoice_file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:' . ($maxMb * 1024),
            ],
        ]);

        $vehicleId = (int) $validated['vehicle_id'];
        if (!$company->vehicles()->where('id', $vehicleId)->exists()) {
            return back()->withErrors(['vehicle_id' => __('validation.exists', ['attribute' => __('driver.vehicle')])]);
        }

        $oldVehicleId = $companyFuelInvoice->vehicle_id;

        $invoiceFile = $companyFuelInvoice->invoice_file;
        $fileType = $companyFuelInvoice->file_type;
        $originalFilename = $companyFuelInvoice->original_filename;

        if ($request->hasFile('invoice_file')) {
            $oldPath = $companyFuelInvoice->invoice_file;
            if ($oldPath && Storage::disk('private')->exists($oldPath)) {
                Storage::disk('private')->delete($oldPath);
            }
            $file = $request->file('invoice_file');
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
            $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) ? 'image' : 'pdf';
            $originalFilename = $file->getClientOriginalName();
            $uniqueName = Str::uuid() . '.' . $ext;
            $invoiceFile = $file->storeAs('fuel_invoices/' . $company->id, $uniqueName, 'private');
        }

        $companyFuelInvoice->update([
            'vehicle_id' => $vehicleId,
            'amount' => isset($validated['amount']) && $validated['amount'] !== '' ? (float) $validated['amount'] : null,
            'description' => $validated['description'] ?? null,
            'invoice_file' => $invoiceFile,
            'file_type' => $fileType,
            'original_filename' => $originalFilename,
        ]);

        InvalidateCompanyAnalyticsCache::forCompany($company->id);
        InvalidateCompanyAnalyticsCache::forVehicle($vehicleId);
        if ($oldVehicleId && (int) $oldVehicleId !== $vehicleId) {
            InvalidateCompanyAnalyticsCache::forVehicle((int) $oldVehicleId);
        }

        $params = request()->only(['from', 'to', 'vehicle_id']);
        $params = array_filter($params);
        return redirect()->route('company.fuel.index', $params)->with('success', __('maintenance.invoice_updated_success'));
    }
}
