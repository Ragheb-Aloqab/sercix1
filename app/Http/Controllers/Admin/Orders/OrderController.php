<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.orders.index');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load([
            'company',
            'vehicle',
            'services',
            'statusLogs',
            'invoice',
            'attachments',
        ]);

        return view('admin.orders.show', compact('order'));
    }
}
