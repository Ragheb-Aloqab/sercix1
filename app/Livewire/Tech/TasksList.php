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

    private function getStatusDisplay(string $status): array
    {
        $status = strtolower((string) $status);
        $map = [
            'pending'     => [__('common.status_pending'),   'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300', 'w-2 bg-amber-400'],
            'in_progress' => [__('common.status_in_progress'),'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',     'w-2 bg-sky-400'],
            'completed'   => [__('common.status_completed'),    'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-slate-300','w-2 bg-emerald-400'],
            'cancelled'   => [__('common.status_cancelled'),     'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',  'w-2 bg-rose-400'],
            'rejected'    => [__('common.status_rejected'),    'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',  'w-2 bg-rose-400'],
        ];
        $label = $map[$status][0] ?? $status;
        $badge = $map[$status][1] ?? 'bg-slate-100 text-slate-800 dark:bg-white/10 dark:text-white';
        $bar = $map[$status][2] ?? 'w-2 bg-slate-300';
        $progress = match ($status) {
            'pending' => 20,
            'in_progress' => 60,
            'completed' => 100,
            default => 35,
        };
        return ['label' => $label, 'badge' => $badge, 'bar' => $bar, 'progress' => $progress];
    }

    public function render()
    {
        $tasks = $this->baseQuery()
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->paginate(12)
            ->withQueryString();

        $tasksWithDisplay = $tasks->getCollection()->map(function ($o) {
            $display = $this->getStatusDisplay($o->status ?? '');
            return (object) [
                'order' => $o,
                'label' => $display['label'],
                'badge' => $display['badge'],
                'bar' => $display['bar'],
                'progress' => $display['progress'],
                'companyName' => $o->company?->company_name ?? '-',
                'companyPhone' => $o->company?->phone ?? null,
            ];
        });
        $tasks->setCollection($tasksWithDisplay);

        $statuses = ['pending', 'accepted', 'on_the_way', 'in_progress', 'completed', 'cancelled'];

        return view('livewire.tech.tasks-list', [
            'tasks' => $tasks,
            'statuses' => $statuses,
        ]);
    }
}
