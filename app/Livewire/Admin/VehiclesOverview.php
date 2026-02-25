<?php

namespace App\Livewire\Admin;

use App\Models\Vehicle;
use App\Models\Company;
use App\Services\ExpiryMonitoringService;
use Livewire\Component;
use Livewire\WithPagination;

class VehiclesOverview extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $companyId = null;
    public string $statusFilter = 'all'; // all, active, inactive, maintenance

    protected $queryString = [
        'search' => ['except' => ''],
        'companyId' => ['except' => null],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCompanyId(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getCompaniesProperty()
    {
        return Company::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name']);
    }

    public function getVehiclesProperty()
    {
        return Vehicle::query()
            ->with('company:id,company_name')
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('plate_number', 'like', '%' . $this->search . '%')
                    ->orWhere('make', 'like', '%' . $this->search . '%')
                    ->orWhere('model', 'like', '%' . $this->search . '%')
                    ->orWhere('driver_name', 'like', '%' . $this->search . '%')
                    ->orWhere('driver_phone', 'like', '%' . $this->search . '%');
            }))
            ->when($this->companyId, fn ($q) => $q->where('company_id', $this->companyId))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->statusFilter === 'maintenance', fn ($q) => $q->where('is_active', false)) // Could add maintenance status if model has it
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.vehicles-overview', [
            'vehicles' => $this->vehicles,
            'companies' => $this->companies,
            'expiringDocumentsCount' => app(ExpiryMonitoringService::class)->countExpiringForAdmin(),
        ]);
    }
}
