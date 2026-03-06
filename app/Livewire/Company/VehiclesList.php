<?php

namespace App\Livewire\Company;

use App\Services\ExpiryMonitoringService;
use App\Modules\Vehicles\Services\VehicleQueryService;
use App\Services\VehicleInspectionService;
use App\Services\VehicleMileageService;
use Livewire\Component;
use Livewire\WithPagination;

class VehiclesList extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';
    public string $branchId = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'status' => ['except' => ''],
        'branchId' => ['as' => 'branch_id', 'except' => ''],
    ];

    public function mount(): void
    {
        $this->q = request('q', '');
        $this->status = request('status', '');
        $this->branchId = (string) request('branch_id', '');
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingBranchId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $company = auth('company')->user();
        $quotaUsage = $company->getQuotaUsage();

        $queryService = new VehicleQueryService($company->id);
        $vehicles = $queryService->paginate(
            $this->q,
            $this->status,
            $this->branchId ? (int) $this->branchId : null
        );

        $expiryService = app(ExpiryMonitoringService::class);
        $inspectionService = app(VehicleInspectionService::class);
        $mileageService = app(VehicleMileageService::class);

        $vehicles->getCollection()->each(function ($v) use ($inspectionService) {
            $v->inspection_status = $inspectionService->getVehicleInspectionStatus($v);
        });

        $mileageSummaries = $mileageService->getVehicleMileageSummariesForVehicles($vehicles->getCollection());

        $branches = $company->branches()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.company.vehicles-list', [
            'vehicles' => $vehicles,
            'quotaUsage' => $quotaUsage,
            'expiryService' => $expiryService,
            'mileageSummaries' => $mileageSummaries,
            'branches' => $branches,
        ]);
    }
}
