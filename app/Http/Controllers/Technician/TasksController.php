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
        $this->authorize('view', $order);

        $technician = Auth::guard('web')->user();
        $order->load([
            'company:id,company_name,phone',
            'vehicle:id,plate_number,make,model',
            'services',
        ]);

        return view('technician.tasks.show', compact('technician', 'order'));
    }

    /**
     * تأكيد إنجاز المهمة
     * POST /tech/tasks/{order}/confirm-complete
     */
    public function confirmComplete(Order $order): RedirectResponse
    {
        $this->authorize('changeStatus', $order);

        $technician = Auth::guard('web')->user();

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new OrderCompletedNotification($order));
        }
        $order->company?->notify(new OrderCompletedNotification($order));

        // (اختياري) امنع التأكيد إذا الطلب مكتمل أصلاً
        if ($order->status === 'completed') {
            return back()->with('info', __('messages.task_already_completed'));
        }

        $order->update([
            'status' => 'completed',
        ]);

        return redirect()
            ->route('tech.tasks.show', $order->id)
            ->with('success', __('messages.task_completed_success'));
    }
    public function accept($id )
{
    $order = Order::find($id);
    
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
    return back()->with('success', __('messages.order_accepted'));
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

    return back()->with('success', __('messages.order_rejected'));
}
}
