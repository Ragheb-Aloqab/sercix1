<?php

namespace App\Livewire\Company;

use App\Services\SubscriptionService;
use App\Services\TaxReportService;
use Carbon\Carbon;
use Livewire\Component;

class TaxReport extends Component
{
    public string $from = '';

    public string $to = '';

    public string $vehicleId = '';

    public function mount(): void
    {
        $this->from = request('from', now()->startOfMonth()->format('Y-m-d'));
        $this->to = request('to', now()->format('Y-m-d'));
        $vid = request('vehicle_id');
        $this->vehicleId = $vid !== null && $vid !== '' ? (string) $vid : '';
    }

    public function render()
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'tax_reports');

        $dateFrom = $this->from !== '' ? Carbon::parse($this->from)->startOfDay() : now()->startOfMonth();
        $dateTo = $this->to !== '' ? Carbon::parse($this->to)->endOfDay() : now()->endOfDay();
        $vehicleId = $this->vehicleId !== '' ? (int) $this->vehicleId : null;
        if ($vehicleId && ! $company->vehicles()->where('id', $vehicleId)->exists()) {
            $vehicleId = null;
        }

        $taxReportService = app(TaxReportService::class);
        $data = $taxReportService->getReportData($company->id, $vehicleId, $dateFrom, $dateTo);

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('livewire.company.tax-report', [
            'data' => $data,
            'vehicles' => $vehicles,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);
    }
}
