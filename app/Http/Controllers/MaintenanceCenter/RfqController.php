<?php

namespace App\Http\Controllers\MaintenanceCenter;

use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Services\MaintenanceRfqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RfqController extends Controller
{
    public function __construct(
        private MaintenanceRfqService $rfqService
    ) {}

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $center = auth('maintenance_center')->user();
        $assignment = $maintenanceRequest->rfqAssignments()->where('maintenance_center_id', $center->id)->firstOrFail();

        $maintenanceRequest->load(['vehicle', 'company', 'attachments', 'quotations']);

        return view('maintenance-center.rfq.show', [
            'request' => $maintenanceRequest,
        ]);
    }

    public function submitQuotation(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $center = auth('maintenance_center')->user();
        $maintenanceRequest->rfqAssignments()->where('maintenance_center_id', $center->id)->firstOrFail();

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'quotation_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $quotationPdfPath = null;
        $originalPdfName = null;
        if ($request->hasFile('quotation_pdf')) {
            $file = $request->file('quotation_pdf');
            $quotationPdfPath = $file->store('quotation-pdfs/' . $maintenanceRequest->id, 'public');
            $originalPdfName = $file->getClientOriginalName();
        }

        $data['quotation_pdf_path'] = $quotationPdfPath;
        $data['original_pdf_name'] = $originalPdfName;

        try {
            $this->rfqService->submitQuotation($maintenanceRequest, $center, $data);
            return redirect()->route('maintenance-center.rfq.show', $maintenanceRequest)
                ->with('success', __('messages.quotation_submitted'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markStarted(MaintenanceRequest $maintenanceRequest)
    {
        $center = auth('maintenance_center')->user();

        try {
            $this->rfqService->markStarted($maintenanceRequest, $center);
            return redirect()->route('maintenance-center.rfq.show', $maintenanceRequest)
                ->with('success', __('messages.job_started'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function uploadInvoice(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $center = auth('maintenance_center')->user();

        $data = $request->validate([
            'final_invoice' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'final_invoice_amount' => ['nullable', 'numeric', 'min:0'],
            'completion_date' => ['nullable', 'date'],
        ]);

        $file = $request->file('final_invoice');
        $path = $file->store('maintenance-invoices/' . $maintenanceRequest->id, 'private');
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            try {
                $pdfPath = app(\App\Services\MaintenanceInvoicePdfService::class)
                    ->convertImageToPdfAndSave($path, 'private');
                $path = $pdfPath;
                $originalName = pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($path);
                report($e);
                return back()->with('error', __('messages.invoice_pdf_error') ?? 'Failed to convert image to PDF.');
            }
        }

        $data['final_invoice_pdf_path'] = $path;
        $data['final_invoice_original_name'] = $originalName;
        $data['final_invoice_amount'] = $data['final_invoice_amount'] ?? null;
        $data['completion_date'] = $data['completion_date'] ?? null;

        try {
            $this->rfqService->uploadFinalInvoice($maintenanceRequest, $center, $data);
            return redirect()->route('maintenance-center.rfq.show', $maintenanceRequest)
                ->with('success', __('messages.invoice_uploaded'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
