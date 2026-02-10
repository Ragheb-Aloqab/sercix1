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
