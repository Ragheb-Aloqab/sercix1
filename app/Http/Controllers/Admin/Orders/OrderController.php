<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\IndexOrdersRequest;
use App\Models\Order;
use App\Support\OrderStatus;

class OrderController extends Controller
{
    public function index(IndexOrdersRequest $request)
    {
        $q = Order::query()
            ->with([
                // companies columns: id, company_name, phone
                'company:id,company_name,phone',
                'vehicle:id,company_id,make,model,plate_number',
                'technician:id,name,phone',
                // payments columns: id, order_id, method, status, amount, paid_at
                'payments:id,order_id,method,status,amount,paid_at',
            ])
            ->latest();

        // status filter
        if ($request->filled('status') && in_array($request->status, OrderStatus::ALL, true)) {
            $q->where('status', $request->status);
        }

        // company filter
        if ($request->filled('company_id')) {
            $q->where('company_id', $request->company_id);
        }

        // technician filter
        if ($request->filled('technician_id')) {
            $q->where('technician_id', $request->technician_id);
        }

        // payment method filter (NOT in orders table) => filter via payments relation
        if ($request->filled('payment_method')) {
            $method = $request->payment_method; // allowed: cash|tap (حسب سكيمة payments)
            $q->whereHas('payments', fn($p) => $p->where('method', $method));
        }

        // date filters
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->to);
        }

        // search
        if ($request->filled('search')) {
            $search = trim($request->search);

            $q->where(function ($qq) use ($search) {
                $qq->where('id', $search)
                    ->orWhereHas('company', function ($c) use ($search) {
                        $c->where('company_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('technician', fn($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $q->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
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
            ->where('status', 'active') // إذا عندك قيم status مختلفة عدّلها
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('admin.orders.show', compact('order', 'technicians'));
    }
}
// public function show(Order $order)
// {
//     $order->load([
//         'company',
//         'vehicle',
//         'technician',
//         'services',
//         'statusLogs',
//         'payments',
//         'invoice',
//         'attachments',
//     ]);

//     // الحالات التي تعتبر الفني "مشغول" فيها
//     $busyStatuses = ['assigned', 'on_the_way', 'in_progress'];

//     // الفنيين المشغولين الآن (عندهم طلب نشط)
//     $busyTechnicianIds = \App\Models\Order::query()
//         ->whereIn('status', $busyStatuses)
//         ->whereNotNull('technician_id')
//         ->pluck('technician_id')
//         ->unique()
//         ->values();

//     // لو الطلب الحالي عليه فني، خلّيه يظهر بالقائمة حتى لو كان مشغول (عشان إعادة الإسناد)
//     if ($order->technician_id) {
//         $busyTechnicianIds = $busyTechnicianIds->reject(fn ($id) => (int)$id === (int)$order->technician_id);
//     }

//     // الفنيين المتاحين فقط
//     $technicians = \App\Models\User::query()
//         ->where('role', 'technician')
//         ->where('status', 'active')
//         ->whereNotIn('id', $busyTechnicianIds)
//         ->orderBy('name')
//         ->get(['id', 'name', 'phone']);

//     return view('admin.orders.show', compact('order', 'technicians'));
// }
