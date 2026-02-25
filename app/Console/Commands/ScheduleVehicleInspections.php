<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\VehicleInspectionService;
use Illuminate\Console\Command;

class ScheduleVehicleInspections extends Command
{
    protected $signature = 'inspections:schedule';

    protected $description = 'Schedule pending vehicle inspections for companies with monthly frequency';

    public function handle(VehicleInspectionService $service): int
    {
        $companies = Company::query()
            ->whereHas('inspectionSettings', fn ($q) => $q->where('is_enabled', true)->where('frequency_type', 'monthly'))
            ->get();

        $total = 0;
        foreach ($companies as $company) {
            $count = $service->scheduleInspectionsForCompany($company);
            $total += $count;
        }

        $this->info("Scheduled {$total} inspections across " . $companies->count() . " companies.");
        return 0;
    }
}
