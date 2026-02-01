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
        $order->invoice()->firstOrCreate([], [
            'number' => 'INV-'.$order->id.'-'.now()->format('Ymd'),
            'issued_at' => now(),
        ]);

        return back()->with('success', 'تم إنشاء الفاتورة.');
    }
}
