<?php

namespace App\Livewire\Company;

use App\Models\Order;
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
        $statuses = ['pending', 'accepted', 'on_the_way', 'in_progress', 'completed', 'cancelled'];

        return view('livewire.company.orders-list', [
            'orders' => $orders,
            'statuses' => $statuses,
        ]);
    }
}
