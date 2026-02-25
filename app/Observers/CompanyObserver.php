<?php

namespace App\Observers;

use App\Models\Company;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;

class CompanyObserver
{
    private function invalidateAdminStats(): void
    {
        Cache::put('admin_stats_version', (Cache::get('admin_stats_version', 1) + 1));
    }

    public function created(Company $company): void
    {
        $this->invalidateAdminStats();
        ActivityLogger::log('company_created', 'company', $company->id, "Company created: {$company->company_name}", null, ['company_name' => $company->company_name]);
    }

    public function updated(Company $company): void
    {
        $this->invalidateAdminStats();
        if ($company->wasChanged()) {
            ActivityLogger::log('company_updated', 'company', $company->id, "Company updated: {$company->company_name}", $company->getOriginal(), $company->getChanges());
        }
    }

    public function deleted(Company $company): void
    {
        $this->invalidateAdminStats();
        ActivityLogger::log('company_deleted', 'company', $company->id, "Company deleted: {$company->company_name}");
    }
}
