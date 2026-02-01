<?php

namespace App\Http\Controllers\Admin\Orders;
use App\Notifications\OrderUpdate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\ChangeOrderStatusRequest;
use App\Support\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\ActivityLogger;
class OrderStatusController extends Controller
{
    // public function store(ChangeOrderStatusRequest $request, Order $order)
    // {
    //     $from = $order->status;
    //     $to = $request->string('to_status')->toString();

    //     if (!OrderStatus::canTransition($from, $to)) {
    //         return back()->withErrors([
    //             'to_status' => "انتقال غير مسموح: {$from} → {$to}"
    //         ]);
    //     }

    //     $order->update(['status' => $to]);

    //     $order->statusLogs()->create([
    //         'from_status' => $from,
    //         'to_status' => $to,
    //         'note' => $request->input('note'),
    //         'changed_by' => $request->user()->id,
    //     ]);

    //     return back()->with('success', 'تم تحديث حالة الطلب بنجاح ✅');
    // }
    public function store(ChangeOrderStatusRequest $request, Order $order)
    {
        
        
        
        $from = (string) $order->status;
        $to   = $request->string('to_status')->toString();

        $user = $request->user();
        $isAdmin = ($user?->role === 'admin');

        $isAllowed = OrderStatus::canTransition($from, $to);

        // ✅ غير الأدمن: ممنوع أي انتقال غير مسموح
        if (!$isAdmin && !$isAllowed) {
            return back()->withErrors([
                'to_status' => "انتقال غير مسموح: {$from} → {$to}",
            ]);
        }

        // ✅ الأدمن: إذا الانتقال غير مسموح (تجاوز) لازم سبب
        if ($isAdmin && !$isAllowed && !$request->filled('note')) {
            return back()->withErrors([
                'note' => 'هذا انتقال غير قياسي (تجاوز). الرجاء كتابة سبب التغيير.',
            ]);
        }

        // ✅ نفّذ التغيير
        $order->update(['status' => $to]);

        // ✅ سجّل اللوج
        $note = $request->input('note');

        if ($isAdmin && !$isAllowed) {
            $note = trim(($note ? $note . ' ' : '') . '(تجاوز أدمن)');
        }

        $order->statusLogs()->create([
            'from_status' => $from,
            'to_status'   => $to,
            'note'        => $note,
            'changed_by'  => $user->id,
        ]);
        /*ارسال اشعار للمدير انه تم تعليق الطلب*/
        $admin = User::where('role', 'admin')->first();
        
        if ($admin) {
            $admin->notify(new OrderUpdate($order));
        }
        $order->company->notify(new OrderUpdate($order));
        ActivityLogger::log(
            action: 'hold_order',
            subjectType: 'order',
            subjectId: $order->id,
            description: 'تم تعليق طلب العميل');
   
        return back()->with('success', 'تم تحديث حالة الطلب بنجاح ✅');
    }
}
