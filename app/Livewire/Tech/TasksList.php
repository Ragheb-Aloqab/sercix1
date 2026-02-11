<?php

namespace App\Livewire\Tech;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderAcceptedByTechnicianNotification;
use App\Notifications\TechnicianResponseNotification;
use App\Services\ActivityLogger;
use Livewire\Component;
use Livewire\WithPagination;

class TasksList extends Component
{
    use WithPagination;

    public string $status = '';

    protected $queryString = ['status' => ['except' => '']];

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->status = '';
        $this->resetPage();
    }

    public function acceptTask(int $orderId): void
    {
        $order = Order::findOrFail($orderId);
        $technician = auth()->user();
        abort_unless((int) $order->technician_id === (int) $technician->id, 403);

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new TechnicianResponseNotification($order, $technician, 'accepted'));
        }
        $order->company?->notify(new OrderAcceptedByTechnicianNotification($order, $technician));
        ActivityLogger::log(
            action: 'accept_order',
            subjectType: 'order',
            subjectId: $order->id,
            description: 'تم قبول الطلب'
        );
        session()->flash('success', 'تم قبول الطلب');
    }

    public function rejectTask(int $orderId): void
    {
        $order = Order::findOrFail($orderId);
        $technician = auth()->user();
        abort_unless((int) $order->technician_id === (int) $technician->id, 403);

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new TechnicianResponseNotification($order, $technician, 'rejected'));
        }
        ActivityLogger::log(
            action: 'reject_order',
            subjectType: Order::class,
            subjectId: $order->id,
            description: 'تم رفض الطلب'
        );
        session()->flash('success', 'تم رفض الطلب');
    }

    protected function baseQuery()
    {
        $technician = auth()->user();

        return Order::query()
            ->where('technician_id', $technician->id)
            ->with(['company:id,company_name,phone'])
            ->latest();
    }

    public function render()
    {
        $tasks = $this->baseQuery()
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->paginate(12)
            ->withQueryString();

        $statuses = ['pending', 'accepted', 'on_the_way', 'in_progress', 'completed', 'cancelled'];

        return view('livewire.tech.tasks-list', [
            'tasks' => $tasks,
            'statuses' => $statuses,
        ]);
    }
}
