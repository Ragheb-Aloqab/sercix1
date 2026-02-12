<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\CompanyBranch;

class CompanyBranchPolicy
{
    public function viewAny(Company $company): bool
    {
        return true;
    }

    public function view(Company $company, CompanyBranch $branch): bool
    {
        return (int) $branch->company_id === (int) $company->id;
    }

    public function create(Company $company): bool
    {
        return true;
    }

    public function update(Company $company, CompanyBranch $branch): bool
    {
        return (int) $branch->company_id === (int) $company->id;
    }

    public function delete(Company $company, CompanyBranch $branch): bool
    {
        return (int) $branch->company_id === (int) $company->id;
    }
}
