<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use App\Services\MaintenanceInvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('invoice_type') === 'fuel') {
            $params = array_filter($request->only(['from', 'to', 'vehicle_id']));
            return redirect()->route('company.fuel.index', $params);
        }
        return view('company.invoices.index');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $company = auth('company')->user();
        $invoice->load([
            'order.services',
            'order.vehicle',
            'order.attachments',
            'fuelRefill.vehicle',
        ]);

        $total = (float) ($invoice->total ?? 0);

        $paid = (float) ($invoice->paid_amount ?? 0);

        $remaining = max(0, $total - $paid);

        $barcodeData = $invoice->invoice_number ?? 'INV-' . $invoice->id;
        $barcodeGen = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcodeImg = $barcodeGen->getBarcode($barcodeData, $barcodeGen::TYPE_CODE_128, 2, 40);

        $driverInvoiceAtt = $invoice->order?->attachments?->where('type', 'driver_invoice')->first();

        return view('company.invoices.show', [
            'company' => $company,
            'invoice' => $invoice,
            'paidAmount' => $paid,
            'remainingAmount' => $remaining,
            'barcodeData' => $barcodeData,
            'barcodeImg' => $barcodeImg,
            'driverInvoiceAtt' => $driverInvoiceAtt,
        ]);
    }

    public function downloadPdf(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        if ($request->boolean('queue')) {
            $company = $invoice->company ?? $invoice->order?->company ?? $invoice->fuelRefill?->company;
            if (!$company) {
                return redirect()->route('company.invoices.show', $invoice)
                    ->with('error', __('messages.invoice_pdf_error'));
            }
            GenerateInvoicePdfJob::dispatch($invoice, $company);
            return back()->with('success', __('reports.queued_for_generation'));
        }

        try {
            $pdf = app(\App\Services\InvoicePdfService::class)->getPdfContent($invoice);
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('company.invoices.show', $invoice->id)
                ->with('error', __('messages.invoice_pdf_error'));
        }

        $filename = 'invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download maintenance invoice PDF (CamScanner-style: image only on A4).
     * Only for service invoices with driver_invoice image attachment.
     */
    public function downloadMaintenancePdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $att = $invoice->order?->attachments()->where('type', 'driver_invoice')->first();
        if (!$att || !$att->file_path) {
            return redirect()->route('company.invoices.show', $invoice)
                ->with('error', __('messages.maintenance_invoice_pdf_not_available'));
        }

        $ext = strtolower(pathinfo($att->file_path, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            return redirect()->route('company.invoices.show', $invoice)
                ->with('error', __('messages.maintenance_invoice_pdf_not_available'));
        }

        $pdfPath = $att->maintenance_invoice_pdf_path;
        if (!$pdfPath || !Storage::disk('public')->exists($pdfPath)) {
            try {
                $service = app(MaintenanceInvoicePdfService::class);
                $path = $service->generateAndSave($att);
                $att->update(['maintenance_invoice_pdf_path' => $path]);
                $pdfPath = $path;
            } catch (\Throwable $e) {
                report($e);
                return redirect()->route('company.invoices.show', $invoice)
                    ->with('error', __('messages.invoice_pdf_error'));
            }
        }

        $filename = 'maintenance-invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf';
        $content = Storage::disk('public')->get($pdfPath);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
