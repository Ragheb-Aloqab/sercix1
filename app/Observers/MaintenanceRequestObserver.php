<?php

namespace App\Observers;

use App\Models\MaintenanceRequest;
use App\Events\MaintenanceRequestCreated;
use Illuminate\Support\Facades\Cache;

class MaintenanceRequestObserver
{
    public function created(MaintenanceRequest $maintenanceRequest): void
    {
        event(new MaintenanceRequestCreated($maintenanceRequest));
    }

    /**
     * Invalidate market comparison cache when expense-related fields change.
     */
    public function updated(MaintenanceRequest $maintenanceRequest): void
    {
        if (!$maintenanceRequest->company_id) {
            return;
        }

        $expenseFields = ['approved_quote_amount', 'final_invoice_amount'];
        if ($maintenanceRequest->wasChanged($expenseFields)) {
            Cache::forget("company_dashboard_{$maintenanceRequest->company_id}");
            Cache::forget("market_comparison_{$maintenanceRequest->company_id}_6");
            Cache::forget("market_comparison_{$maintenanceRequest->company_id}_12");
        }
    }
}
