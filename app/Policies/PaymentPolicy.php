<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Payment;

class PaymentPolicy
{
    public function viewAny(Company $company): bool
    {
        return true;
    }

    public function view(Company $company, Payment $payment): bool
    {
        return (int) $payment->order->company_id === (int) $company->id;
    }

    public function create(Company $company): bool
    {
        return true;
    }

    public function update(Company $company, Payment $payment): bool
    {
        return (int) $payment->order->company_id === (int) $company->id;
    }

    public function delete(Company $company, Payment $payment): bool
    {
        return (int) $payment->order->company_id === (int) $company->id;
    }
}
