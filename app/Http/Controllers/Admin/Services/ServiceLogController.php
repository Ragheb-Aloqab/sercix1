<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderService;
use Illuminate\Http\Request;

class ServiceLogController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $done = $request->get('done', 'all'); // done | not_done | all

        $rows = OrderService::query()
            ->with([
                'service:id,name',
                'order:id,company_id,vehicle_id,status,scheduled_at,created_at',
                'order.company:id,company_name,phone',
                'order.vehicle:id,make,model,year,plate_number,type',
            ])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('service', fn($s) => $s->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('order.vehicle', fn($v) => $v->where('plate_number', 'like', "%{$q}%"));
            })
            ->when($done === 'done', fn($query) => $query->whereHas('order', fn($o) => $o->where('status', 'completed')))
            ->when($done === 'not_done', fn($query) => $query->whereHas('order', fn($o) => $o->where('status', '!=', 'completed')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.services.service_logs.index', compact('rows', 'q', 'done'));
    }

    public function show(OrderService $orderService)
    {
        $orderService->load([
            'service',
            'order.company',
            'order.vehicle',
            'order.attachments', // لأن attachments عندك order_id
        ]);

        return view('admin.services.service_logs.show', compact('orderService'));
    }
}
