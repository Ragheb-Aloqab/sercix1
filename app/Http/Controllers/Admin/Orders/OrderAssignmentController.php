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
        // Technician assignment disabled - tasks remain unassigned
        return back()->with('info', __('messages.assignment_disabled'));

        $this->authorize('assignTechnician', $order);

        $tech = User::query()
            ->where('id', $request->technician_id)
            ->where('role', 'technician')
            ->firstOrFail();

        $from = $order->status;

      
        $to = 'assigned_to_technician';

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
        
      
        return back()->with('success', __('messages.order_assigned'));
    }
}
