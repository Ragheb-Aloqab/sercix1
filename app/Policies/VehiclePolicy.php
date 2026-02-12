<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(Company $company): bool
    {
        return true;
    }

    public function view(Company $company, Vehicle $vehicle): bool
    {
        return (int) $vehicle->company_id === (int) $company->id;
    }

    public function create(Company $company): bool
    {
        return true;
    }

    public function update(Company $company, Vehicle $vehicle): bool
    {
        return (int) $vehicle->company_id === (int) $company->id;
    }

    public function delete(Company $company, Vehicle $vehicle): bool
    {
        return (int) $vehicle->company_id === (int) $company->id;
    }
}
