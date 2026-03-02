<?php

namespace App\Livewire\Company;

use App\Models\MaintenanceRequest;
use App\Models\Order;
use App\Support\OrderStatus;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersList extends Component
{
    use WithPagination;

    public string $status = '';

    public string $search = '';

    protected $queryString = ['status' => ['except' => ''], 'search' => ['except' => '']];

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->status = '';
        $this->search = '';
        $this->resetPage();
    }

    protected function baseQuery()
    {
        $company = auth('company')->user();

        return Order::query()
            ->where('company_id', $company->id)
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('id', 'like', $term)
                        ->orWhere('requested_by_name', 'like', $term)
                        ->orWhere('driver_phone', 'like', $term)
                        ->orWhereHas('vehicle', fn ($v) => $v->where('plate_number', 'like', $term)
                            ->orWhere('make', 'like', $term)
                            ->orWhere('model', 'like', $term));
                });
            })
            ->with(['vehicle:id,plate_number,make,model', 'services'])
            ->latest();
    }

    protected function maintenanceQuery()
    {
        $company = auth('company')->user();

        return MaintenanceRequest::forCompany($company->id)
            ->where('status', 'closed')
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('id', 'like', $term)
                        ->orWhere('requested_by_name', 'like', $term)
                        ->orWhere('driver_phone', 'like', $term)
                        ->orWhereHas('vehicle', fn ($v) => $v->where('plate_number', 'like', $term)
                            ->orWhere('make', 'like', $term)
                            ->orWhere('model', 'like', $term));
                });
            })
            ->with(['vehicle:id,plate_number,make,model', 'approvedCenter'])
            ->latest('completed_at');
    }

    public function render()
    {
        $statuses = OrderStatus::ALL;

        $orders = $this->baseQuery()->paginate(15)->withQueryString();
        $ordersWithDisplay = $orders->getCollection()->map(fn ($order) => (object) ['order' => $order, 'maintenanceRequest' => null]);
        $orders->setCollection($ordersWithDisplay);

        $maintenanceRequests = $this->maintenanceQuery()->paginate(15)->withQueryString();

        return view('livewire.company.orders-list', [
            'orders' => $orders,
            'maintenanceRequests' => $maintenanceRequests,
            'statuses' => $statuses,
        ]);
    }
}
