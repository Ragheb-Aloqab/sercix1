<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderInvoiceController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['invoice', 'company', 'vehicle', 'services']);

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
