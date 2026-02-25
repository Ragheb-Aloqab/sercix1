<?php

namespace App\Http\Controllers\Admin\Orders;
use App\Notifications\OrderCompletedNotification;
use App\Notifications\OrderUpdate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\ChangeOrderStatusRequest;
use App\Models\Order;
use App\Models\User;
use App\Models\WebhookUrl;
use App\Support\OrderStatus;
use App\Services\ActivityLogger;
class OrderStatusController extends Controller
{
   
    public function store(ChangeOrderStatusRequest $request, Order $order)
    {
        $this->authorize('changeStatus', $order);

        $from = (string) $order->status;
        $to   = $request->string('to_status')->toString();

        $user = $request->user();
        $isAdmin = ($user?->role === 'admin');

        $isAllowed = OrderStatus::canTransition($from, $to);

       
        if (!$isAdmin && !$isAllowed) {
            return back()->withErrors([
                'to_status' => __('messages.order_transition_not_allowed', ['from' => $from, 'to' => $to]),
            ]);
        }

       
        if ($isAdmin && !$isAllowed && !$request->filled('note')) {
            return back()->withErrors([
                'note' => __('messages.order_override_note_required'),
            ]);
        }

        $order->update(['status' => $to]);

      
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
      
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        if ($to === 'completed') {
            foreach ($admins as $admin) {
                $admin->notify(new OrderCompletedNotification($order));
            }
            $order->company?->notify(new OrderCompletedNotification($order));
        } else {
            foreach ($admins as $admin) {
                $admin->notify(new OrderUpdate($order));
            }
            $order->company?->notify(new OrderUpdate($order));
        }

        ActivityLogger::log(
            action: 'order_status_changed',
            subjectType: 'order',
            subjectId: $order->id,
            description: __('messages.order_status_updated') ?: "Order #{$order->id} status: {$from} → {$to}",
            oldValues: ['status' => $from],
            newValues: ['status' => $to],
        );

        WebhookUrl::dispatch('order_status_changed', [
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'company_id' => $order->company_id,
            'timestamp' => now()->toIso8601String(),
        ], $order->company_id);

        return back()->with('success', __('messages.order_status_updated'));
    }
}
