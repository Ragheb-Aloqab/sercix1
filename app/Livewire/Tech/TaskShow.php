<?php

namespace App\Livewire\Tech;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCompletedNotification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TaskShow extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load([
            'company:id,company_name,phone',
            'vehicle:id,plate_number,make,model',
            'services',
            'beforePhotos',
            'afterPhotos',
        ]);
    }

    public function confirmComplete(): void
    {
        $technician = auth()->user();
        abort_unless((int) $this->order->technician_id === (int) $technician->id, 403);

        if ($this->order->status === 'completed') {
            session()->flash('info', 'هذه المهمة مكتملة بالفعل.');
            return;
        }

        $this->order->update(['status' => 'completed']);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new OrderCompletedNotification($this->order));
        }

        $this->order = $this->order->fresh(['company', 'vehicle', 'services', 'beforePhotos', 'afterPhotos']);
        session()->flash('success', 'تم تأكيد إنجاز المهمة بنجاح ');
    }

    public function render(): View
    {
        return view('livewire.tech.task-show', ['order' => $this->order]);
    }
}
