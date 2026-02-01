<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $technician = Auth::guard('web')->user(); // role: technician

        // لو تحب: تأكيد سريع (اختياري)
        if (!$technician) {
            abort(403);
        }

        // الإحصائيات الخاصة بالفني فقط
        $today = now()->toDateString();

        $kpis = [
            'today_tasks' => Order::query()
                ->where('technician_id', $technician->id)
                ->whereDate('created_at', $today)
                ->count(),

            'pending' => Order::query()
                ->where('technician_id', $technician->id)
                ->where('status', 'pending')
                ->count(),

            'in_progress' => Order::query()
                ->where('technician_id', $technician->id)
                ->whereIn('status', ['accepted', 'on_the_way', 'in_progress'])
                ->count(),

            'completed' => Order::query()
                ->where('technician_id', $technician->id)
                ->where('status', 'completed')
                ->count(),
        ];

        // آخر المهام المسندة للفني
        $latestTasks = Order::query()
            ->where('technician_id', $technician->id)
            ->with(['company:id,company_name,phone']) // عدّل العلاقات حسب مشروعك
            ->latest()
            ->take(8)
            ->get();

        return view('technician.index', compact('technician', 'kpis', 'latestTasks'));
    }
}
