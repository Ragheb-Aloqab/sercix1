<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderCompletedNotification;
use App\Notifications\TechnicianResponseNotification;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Notifications\OrderAcceptedByTechnicianNotification;
class TasksController extends Controller
{
    /**
     * قائمة مهام الفني
     * GET /tech/tasks
     */
    public function index()
    {
        return view('technician.tasks.index');
    }

    /**
     * تفاصيل مهمة واحدة
     * GET /tech/tasks/{order}
     */
    public function show(Order $order)
    {
        $technician = Auth::guard('web')->user();

        abort_unless((int) $order->technician_id === (int) $technician->id, 403);

        $order->load([
            'company:id,company_name,phone',
            // 'attachments',
        ]);

        return view('technician.tasks.show', compact('technician', 'order'));
    }

    /**
     * تأكيد إنجاز المهمة
     * POST /tech/tasks/{order}/confirm-complete
     */
    public function confirmComplete(Order $order): RedirectResponse
    {
        $technician = Auth::guard('web')->user();
        
        /* ارسال اشعار للمدير ان الفني انجز المهمة */
        
        
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new OrderCompletedNotification($order));
        }
        /**/
        
        // حماية: الطلب لازم يكون تابع لهذا الفني
        abort_unless((int) $order->technician_id === (int) $technician->id, 403);

        // (اختياري) امنع التأكيد إذا الطلب مكتمل أصلاً
        if ($order->status === 'completed') {
            return back()->with('info', 'هذه المهمة مكتملة بالفعل.');
        }

        $order->update([
            'status' => 'completed',
        ]);

        return redirect()
            ->route('tech.tasks.show', $order->id)
            ->with('success', 'تم تأكيد إنجاز المهمة بنجاح ✅');
    }
    public function accept($id )
{
    $order = Order::find($id);
    //$order->update(['status' => 'accepted']);
    /*$services = $order->services()->first();
    
    $inventory = $services->inventory;
    dd($inventory);
    if ($inventory->qty <= 0) {
        return back()->with('error', 'الكمية غير متوفرة');
    }

    $inventory->qty -= 1;
    $inventory->save();

    $order->status = 'accepted';
    $order->save();*/
    $admin = User::where('role', 'admin')->first();

    $admin->notify(
        new TechnicianResponseNotification(
            $order,
            auth()->user(),
            'accepted'
        )
    );
    $client = $order->company; // أو customer / client حسب الموديل
    $client?->notify(
        new OrderAcceptedByTechnicianNotification(
            $order,
            auth()->user()
        )
    );
    ActivityLogger::log(
            action: 'accept_order',
            subjectType: 'order',
            subjectId: $order->id,
            description: 'تم قبول الطلب   '
    );
    return back()->with('success', 'تم قبول الطلب');
    }
    public function reject($id)
{
    $order = Order::find($id);
    //$order->update(['status' => 'rejected']);

    $admin = User::where('role', 'admin')->first();

    $admin->notify(
        new TechnicianResponseNotification(
            $order,
            auth()->user(),
            'rejected'
        )
    );

    ActivityLogger::log(
        action: 'reject_order',
        subjectType: Order::class,
        subjectId: $order->id,
        description: 'تم رفض الطلب'
    );

    return back()->with('success', 'تم رفض الطلب');
}
}
