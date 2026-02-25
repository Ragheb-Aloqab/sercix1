<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MaintenanceInvoicePdfService;
use Illuminate\Support\Facades\Storage;

class OrderInvoiceController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['invoice', 'company', 'vehicle', 'services', 'attachments']);

        $invoice = $order->invoice;

        if ($invoice) {
            $total = (float) ($invoice->total ?? 0);
            $paid = (float) ($invoice->paid_amount ?? 0);
            $remaining = max(0, $total - $paid);

            return view('admin.orders.invoice', [
                'order' => $order,
                'invoice' => $invoice,
                'paidAmount' => $paid,
                'remainingAmount' => $remaining,
            ]);
        }

        return view('admin.orders.invoice', [
            'order' => $order,
            'invoice' => null,
            'paidAmount' => 0,
            'remainingAmount' => 0,
        ]);
    }

    public function downloadPdf(Order $order)
    {
        $invoice = $order->invoice;
        if (!$invoice) {
            return redirect()
                ->route('admin.orders.invoice.show', $order)
                ->with('error', __('messages.no_invoice_for_order'));
        }

        try {
            $pdf = app(\App\Services\InvoicePdfService::class)->getPdfContent($invoice);
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('admin.orders.invoice.show', $order)
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
     */
    public function downloadMaintenancePdf(Order $order)
    {
        $att = $order->attachments()->where('type', 'driver_invoice')->first();
        if (!$att || !$att->file_path) {
            return redirect()->route('admin.orders.invoice.show', $order)
                ->with('error', __('messages.maintenance_invoice_pdf_not_available'));
        }

        $ext = strtolower(pathinfo($att->file_path, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            return redirect()->route('admin.orders.invoice.show', $order)
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
                return redirect()->route('admin.orders.invoice.show', $order)
                    ->with('error', __('messages.invoice_pdf_error'));
            }
        }

        $filename = 'maintenance-invoice-order-' . $order->id . '.pdf';
        $content = Storage::disk('public')->get($pdfPath);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function store(Order $order)
    {
        $order->load(['services']);

        $subtotal = (float) $order->total_amount;
        $tax = (float) ($order->tax_amount ?? 0);

        $order->invoice()->firstOrCreate([], [
            'company_id'      => $order->company_id,
            'invoice_number'  => 'INV-' . $order->id . '-' . now()->format('Ymd'),
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'paid_amount'     => 0,
        ]);

        return back()->with('success', __('messages.invoice_created'));
    }
}
