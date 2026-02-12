<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(Company $company): bool
    {
        return true;
    }

    public function view(Company $company, Invoice $invoice): bool
    {
        return (int) $invoice->company_id === (int) $company->id;
    }

    public function create(Company $company): bool
    {
        return true;
    }

    public function update(Company $company, Invoice $invoice): bool
    {
        return (int) $invoice->company_id === (int) $company->id;
    }

    public function delete(Company $company, Invoice $invoice): bool
    {
        return (int) $invoice->company_id === (int) $company->id;
    }
}
