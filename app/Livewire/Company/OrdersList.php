<?php

namespace App\Livewire\Company;

use App\Models\Order;
use App\Support\OrderStatus;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersList extends Component
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

    protected function baseQuery()
    {
        $company = auth('company')->user();

        return Order::query()
            ->where('company_id', $company->id)
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->with(['technician:id,name,phone', 'payments', 'services'])
            ->latest();
    }

    public function render()
    {
        $orders = $this->baseQuery()->paginate(15)->withQueryString();
        $statuses = OrderStatus::ALL;

        $ordersWithDisplay = $orders->getCollection()->map(function ($order) {
            $payment = $order->payments?->first();
            $amount = $payment?->amount ?? $order->total_amount;
            return (object) ['order' => $order, 'payment' => $payment, 'amount' => $amount];
        });
        $orders->setCollection($ordersWithDisplay);

        return view('livewire.company.orders-list', [
            'orders' => $orders,
            'statuses' => $statuses,
        ]);
    }
}
