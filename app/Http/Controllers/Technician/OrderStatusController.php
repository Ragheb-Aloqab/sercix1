<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderTaskStartedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderStatusController extends Controller
{
    /**
     * تحديث حالة الطلب بواسطة الفني
     * PATCH /tech/tasks/{order}/status
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('changeStatus', $order);

        // الحالات المسموحة للفني (عدّلها حسب نظامك)
        $allowed = [
            'assigned_to_technician',
            'in_progress',
            'completed',
            'cancelled',
        ];

        $data = $request->validate([
            'status' => ['required', Rule::in($allowed)],
        ]);

        // (اختياري) منع الرجوع للخلف أو تغيير غير منطقي
        // مثال: إذا مكتمل لا تسمح بالتغيير
        if ($order->status === 'completed') {
            return back()->withErrors(['status' => __('messages.order_completed_no_change')]);
        }

        // مثال: لا تسمح بالتغيير لو ملغي
        if ($order->status === 'cancelled') {
            return back()->withErrors(['status' => __('messages.order_cancelled_no_change')]);
        }

        $order->update([
            'status' => $data['status'],
        ]);

        if (in_array($data['status'], ['assigned_to_technician', 'in_progress'])) {
            $order->company?->notify(new OrderTaskStartedNotification($order, $data['status']));
        }

        return back()->with('success', __('messages.order_status_updated'));
    }
}
