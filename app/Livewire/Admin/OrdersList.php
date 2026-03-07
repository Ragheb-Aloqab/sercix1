<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Services\ActivityLogger;
use App\Support\OrderStatus;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $from = '';
    public string $to = '';
    public array $selectedIds = [];
    public bool $selectAll = false;
    public string $bulkStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'from' => ['except' => ''],
        'to' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->from = '';
        $this->to = '';
        $this->selectedIds = [];
        $this->resetPage();
    }

    public function toggleSelectAll(): void
    {
        $this->selectAll = !$this->selectAll;
        if ($this->selectAll) {
            $this->selectedIds = $this->baseQuery()->pluck('id')->values()->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds(): void
    {
        $orders = $this->baseQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->selectAll = count($this->selectedIds) === count($orders) && count($orders) > 0;
    }

    public function bulkUpdateStatus(): void
    {
        $this->validate(['bulkStatus' => ['required', 'in:' . implode(',', OrderStatus::ALL)]]);
        if (empty($this->selectedIds)) {
            $this->addError('selectedIds', __('messages.select_at_least_one') ?: 'Select at least one order.');
            return;
        }

        $orders = Order::whereIn('id', $this->selectedIds)->get();
        foreach ($orders as $order) {
            $from = $order->status;
            if (OrderStatus::canTransition($from, $this->bulkStatus)) {
                $order->update(['status' => $this->bulkStatus]);
                $order->statusLogs()->create([
                    'from_status' => $from,
                    'to_status' => $this->bulkStatus,
                    'note' => __('messages.bulk_update') ?: 'Bulk update',
                    'changed_by' => auth()->id(),
                ]);
                ActivityLogger::log('order_status_changed', 'order', $order->id, "Bulk: {$from} → {$this->bulkStatus}", ['status' => $from], ['status' => $this->bulkStatus]);
            }
        }

        $this->selectedIds = [];
        $this->bulkStatus = '';
        $this->dispatch('orders-updated');
    }

    protected function baseQuery()
    {
        return Order::query()
            ->with([
                'company:id,company_name,phone',
                'vehicle:id,company_id,make,model,plate_number',
                'services:id,name',
            ])
            ->withCount('services')
            ->when($this->status !== '' && in_array($this->status, OrderStatus::ALL, true), fn ($q) => $q->where('status', $this->status))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->when($this->search !== '', function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('id', $s)
                        ->orWhereHas('company', fn ($c) => $c->where('company_name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
                });
            })
            ->latest();
    }

    public function render()
    {
        $orders = $this->baseQuery()->paginate(25)->withQueryString();

        return view('livewire.admin.orders-list', [
            'orders' => $orders,
            'statusOptions' => OrderStatus::ALL,
        ]);
    }
}
