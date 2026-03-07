<?php

namespace App\Rules;

use App\Models\CompanyMaintenanceInvoice;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Prevents duplicate maintenance invoice entries.
 * Same vehicle + same amount within 24 hours is considered a potential duplicate.
 */
class PreventDuplicateMaintenanceInvoice implements ValidationRule
{
    public function __construct(
        private readonly int $companyId,
        private readonly ?int $vehicleId,
        private readonly float $amount
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $q = CompanyMaintenanceInvoice::where('company_id', $this->companyId)
            ->where('amount', $this->amount)
            ->where('created_at', '>=', now()->subDay());

        if ($this->vehicleId !== null) {
            $q->where('vehicle_id', $this->vehicleId);
        } else {
            $q->whereNull('vehicle_id');
        }

        if ($q->exists()) {
            $fail(__('maintenance.duplicate_invoice_warning'));
        }
    }
}
