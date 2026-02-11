<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Support\OrderStatus;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $payment_method = '';
    public string $from = '';
    public string $to = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'payment_method' => ['except' => ''],
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

    public function updatedPaymentMethod(): void
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
        $this->payment_method = '';
        $this->from = '';
        $this->to = '';
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return Order::query()
            ->with([
                'company:id,company_name,phone',
                'vehicle:id,company_id,make,model,plate_number',
                'technician:id,name,phone',
                'payment:id,order_id,method,status,amount,paid_at',
                'services:id,name',
            ])
            ->withCount('services')
            ->when($this->status !== '' && in_array($this->status, OrderStatus::ALL, true), fn ($q) => $q->where('status', $this->status))
            ->when($this->payment_method !== '', fn ($q) => $q->whereHas('payments', fn ($p) => $p->where('method', $this->payment_method)))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->when($this->search !== '', function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('id', $s)
                        ->orWhereHas('company', fn ($c) => $c->where('company_name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
                        ->orWhereHas('technician', fn ($t) => $t->where('name', 'like', "%{$s}%"));
                });
            })
            ->latest();
    }

    public function render()
    {
        $orders = $this->baseQuery()->paginate(15)->withQueryString();

        return view('livewire.admin.orders-list', [
            'orders' => $orders,
            'statusOptions' => OrderStatus::ALL,
        ]);
    }
}
