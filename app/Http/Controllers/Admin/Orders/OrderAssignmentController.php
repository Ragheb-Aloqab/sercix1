<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\AssignTechnicianRequest;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderAssignedToTechnician;

class OrderAssignmentController extends Controller
{
    public function store(AssignTechnicianRequest $request, Order $order)
    {
        $this->authorize('assignTechnician', $order);

        $tech = User::query()
            ->where('id', $request->technician_id)
            ->where('role', 'technician')
            ->firstOrFail();

        $from = $order->status;

      
        $to = 'in_progress';

        $order->update([
            'technician_id' => $tech->id,
            'status' => $to,
        ]);

     
        $order->statusLogs()->create([
            'from_status' => $from,
            'to_status' => $to,
            'note' => $request->note,
            'changed_by' => auth()->id(),
        ]);
      
        $order->technician_id = $tech->id;
        $order->save();
        
       /* $tech->notify(
            new OrderAssignedToTechnician($order)
        );*/
        return back()->with('success', 'تم إسناد الطلب للفني بنجاح.');
    }
}
