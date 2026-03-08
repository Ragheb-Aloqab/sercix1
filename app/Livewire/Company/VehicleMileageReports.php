<?php

namespace App\Livewire\Company;

use App\Models\Vehicle;
use App\Models\CompanyBranch;
use App\Services\VehicleMileageReportService;
use App\Services\MileageReportPdfService;
use App\Exports\VehicleMileageReportExport;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class VehicleMileageReports extends Component
{
    public string $from = '';
    public string $to = '';
    public string $vehicleId = '';
    public string $branchId = '';
    public string $sortBy = 'total_distance';
    public string $sortDir = 'desc';

    public function mount(): void
    {
        $this->from = request('from', now()->startOfMonth()->format('Y-m-d'));
        $this->to = request('to', now()->format('Y-m-d'));
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'desc' ? 'asc' : 'desc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'desc';
        }
    }

    public function render()
    {
        $company = auth('company')->user();

        $from = Carbon::parse($this->from)->startOfDay();
        $to = Carbon::parse($this->to)->endOfDay();

        $service = app(VehicleMileageReportService::class);
        $result = $service->getReport(
            $company->id,
            $from,
            $to,
            $this->vehicleId ? (int) $this->vehicleId : null,
            $this->branchId ? (int) $this->branchId : null,
            $this->sortBy,
            $this->sortDir
        );

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'name', 'make', 'model']);

        $branches = CompanyBranch::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.company.vehicle-mileage-reports', [
            'rows' => $result['rows'],
            'summary' => $result['summary'],
            'vehicles' => $vehicles,
            'branches' => $branches,
        ]);
    }
}
