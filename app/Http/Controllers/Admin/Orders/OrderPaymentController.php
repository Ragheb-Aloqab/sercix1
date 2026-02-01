<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\StorePaymentRequest;
use App\Models\Order;
use App\Support\OrderStatus;

class OrderPaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Order $order)
    {
        // ✅ سجّل الدفع في جدول payments فقط (لا علاقة له بحالة الطلب)
        // جدول payments عندك: order_id, company_id, method(tap|cash), status(pending|paid|failed|refunded), amount, ... , paid_at
        $order->payment()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'company_id' => $order->company_id,
                'method' => $request->method,       // cash|tap
                'status' => $request->status,       // pending|paid|failed|refunded
                'amount' => $request->amount,
                'paid_at' => $request->status === 'paid' ? now() : null,
            ]
        );

        // ✅ لا تضع orders.status = paid لأنه غير مسموح
        // إذا حبيت تربط الدفع بتغيير حالة الطلب استخدم حالة مسموحة مثل completed (اختياري)
        if ($request->status === 'paid' && $order->status !== OrderStatus::COMPLETED) {
            $from = $order->status;
            $to = OrderStatus::COMPLETED; // ✅ مسموحة ضمن CHECK

            $order->update(['status' => $to]);

            $order->statusLogs()->create([
                'from_status' => $from,
                'to_status' => $to,
                'note' => 'تم تسجيل الدفع من لوحة الأدمن.',
                'changed_by' => auth()->id(), // ✅ حسب جدول order_status_logs
            ]);
        }

        return back()->with('success', 'تم تسجيل بيانات الدفع.');
    }
}
