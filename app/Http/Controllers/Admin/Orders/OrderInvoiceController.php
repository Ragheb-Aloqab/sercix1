<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;

class OrderInvoiceController extends Controller
{
    public function show(Order $order)
    {
       $order->load(['invoice', 'company', 'vehicle', 'services', 'payment']);

        return view('admin.orders.invoice', compact('order'));
    }

    public function store(Order $order)
    {
        $order->load(['services', 'payments']);

        $subtotal = (float) $order->total_amount;
        $tax = (float) ($order->tax_amount ?? 0);

        $order->invoice()->firstOrCreate([], [
            'company_id'      => $order->company_id,
            'invoice_number'  => 'INV-' . $order->id . '-' . now()->format('Ymd'),
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'paid_amount'     => 0,
        ]);

        // Ensure company has a pending payment to pay (so they see something in Payments)
        $total = $subtotal + $tax;
        $paid = (float) $order->payments()->where('status', 'paid')->sum('amount');
        $remaining = $total - $paid;
        if ($remaining > 0 && $order->payments()->where('status', 'pending')->count() === 0) {
            Payment::create([
                'order_id'   => $order->id,
                'company_id' => $order->company_id,
                'method'     => null,
                'status'     => 'pending',
                'amount'     => $remaining,
            ]);
        }

        return back()->with('success', 'تم إنشاء الفاتورة.');
    }
}
