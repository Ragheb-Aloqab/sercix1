<?php

namespace App\Livewire\Company;

use App\Services\ComprehensiveReportService;
use App\Services\SubscriptionService;
use Livewire\Component;

class ComprehensiveReport extends Component
{
    public int $month;

    public int $year;

    public string $vehicleId = '';

    public function mount(): void
    {
        $this->month = (int) request('month', now()->month);
        $this->year = (int) request('year', now()->year);
        $vid = request('vehicle_id');
        $this->vehicleId = $vid !== null && $vid !== '' ? (string) $vid : '';
    }

    public function render()
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'advanced_reports');

        $service = app(ComprehensiveReportService::class);
        $data = $service->getReportData(
            $company->id,
            $this->month,
            $this->year,
            $this->vehicleId !== '' ? (int) $this->vehicleId : null
        );

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('livewire.company.comprehensive-report', [
            'data' => $data,
            'vehicles' => $vehicles,
        ]);
    }
}
