<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderInvoiceController extends Controller
{
    public function show(Order $order)
    {
       $order->load(['invoice', 'company', 'vehicle', 'services', 'payment']);

        return view('admin.orders.invoice', compact('order'));
    }

    public function store(Order $order)
    {
        $order->load('services');

        $subtotal = (float) $order->total_amount;
        $tax = (float) ($order->tax_amount ?? 0);

        $order->invoice()->firstOrCreate([], [
            'company_id'      => $order->company_id,
            'invoice_number'  => 'INV-' . $order->id . '-' . now()->format('Ymd'),
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'paid_amount'     => 0,
        ]);

        return back()->with('success', 'تم إنشاء الفاتورة.');
    }
}
