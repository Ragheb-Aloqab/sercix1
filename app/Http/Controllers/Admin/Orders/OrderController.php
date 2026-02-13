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
            'technician',
            'services',
            'statusLogs',
            'payments',
            'invoice',
            'attachments',
        ]);

        $technicians = \App\Models\User::query()
            ->where('role', 'technician')
            ->where('status', 'active') 
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('admin.orders.show', compact('order', 'technicians'));
    }
}
