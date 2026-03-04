<?php

namespace App\Listeners;

use App\Events\InvoiceCreated;

class InvalidateCacheOnInvoiceCreated
{
    public function handle(InvoiceCreated $event): void
    {
        $companyId = $event->invoice->company_id
            ?? $event->invoice->order?->company_id
            ?? $event->invoice->fuelRefill?->company_id;
        InvalidateCompanyAnalyticsCache::forCompany($companyId);
    }
}
