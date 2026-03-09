<?php

use App\Models\Company;
use App\Services\SubdomainService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill subdomains for existing companies that don't have one.
     * Required for SaaS multi-tenant subdomain architecture.
     */
    public function up(): void
    {
        $companies = Company::query()->whereNull('subdomain')->orWhere('subdomain', '')->get();

        foreach ($companies as $company) {
            $subdomain = SubdomainService::generateFromName($company->company_name);
            $company->update(['subdomain' => $subdomain]);
        }
    }

    public function down(): void
    {
        // Cannot safely revert - subdomains may have been customized
    }
};
