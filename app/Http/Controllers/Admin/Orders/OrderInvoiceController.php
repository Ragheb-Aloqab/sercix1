<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;

class OrderInvoiceController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['invoice', 'company', 'vehicle', 'services', 'payments']);

        $invoice = $order->invoice;

        if ($invoice) {
            $total = (float) ($invoice->total ?? 0);
            $paid = (float) ($order->payments->where('status', 'paid')->sum(fn ($p) => (float) $p->amount));
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
                ->with('error', 'لا توجد فاتورة لهذا الطلب.');
        }

        try {
            $pdf = app(\App\Services\InvoicePdfService::class)->getPdfContent($invoice);
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('admin.orders.invoice.show', $order)
                ->with('error', 'حدث خطأ أثناء إنشاء PDF.');
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
        $order->load(['services', 'payments']);

        $subtotal = (float) $order->total_amount;
        $tax = (float) ($order->tax_amount ?? 0);

        $paid = (float) $order->payments()->where('status', 'paid')->sum('amount');

        $order->invoice()->firstOrCreate([], [
            'company_id'      => $order->company_id,
            'invoice_number'  => 'INV-' . $order->id . '-' . now()->format('Ymd'),
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'paid_amount'     => $paid,
        ]);

        // Ensure company has a pending payment to pay (so they see something in Payments)
        $total = $subtotal + $tax;
        $paid = (float) $order->payments()->where('status', 'paid')->sum('amount');
        $remaining = $total - $paid;
        if ($remaining > 0 && $order->payments()->where('status', 'pending')->count() === 0) {
            Payment::create([
                'order_id'   => $order->id,
                'method'     => 'cash',
                'status'     => 'pending',
                'amount'     => $remaining,
            ]);
        }

        return back()->with('success', 'تم إنشاء الفاتورة.');
    }
}
