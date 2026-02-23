<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\VehicleTrackingApiService;
use Illuminate\Console\Command;

class FetchVehicleLocationsCommand extends Command
{
    protected $signature = 'tracking:fetch-locations
                            {--company= : Only fetch for a specific company ID}';

    protected $description = 'Fetch vehicle locations from tracking APIs for all companies';

    public function handle(VehicleTrackingApiService $trackingService): int
    {
        $companyId = $this->option('company');

        $query = Company::query()
            ->whereNotNull('tracking_base_url')
            ->where('tracking_base_url', '!=', '')
            ->whereNotNull('tracking_api_key');

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $companies = $query->get();

        if ($companies->isEmpty()) {
            $this->info('No companies with tracking API configured.');
            return self::SUCCESS;
        }

        foreach ($companies as $company) {
            $this->info("Fetching locations for company: {$company->company_name} (ID: {$company->id})");
            $results = $trackingService->fetchAllForCompany($company);
            $success = collect($results)->filter(fn ($r) => $r['success'])->count();
            $failed = count($results) - $success;
            $this->line("  → {$success} success, {$failed} failed");
        }

        return self::SUCCESS;
    }
}
