<?php

namespace App\Observers;

use App\Models\FuelRefill;
use App\Models\Invoice;
use App\Services\CompanyAnalyticsService;
use App\Services\InvoicePdfService;

class FuelRefillObserver
{
    /**
     * Auto-create invoice when driver uploads fuel refill with receipt.
     * Fuel invoices do NOT require company approval - generated immediately.
     */
    public function created(FuelRefill $fuelRefill): void
    {
        if ($fuelRefill->company_id) {
            CompanyAnalyticsService::invalidateDashboardCache($fuelRefill->company_id);
            \Illuminate\Support\Facades\Cache::forget("market_comparison_{$fuelRefill->company_id}_6");
            \Illuminate\Support\Facades\Cache::forget("market_comparison_{$fuelRefill->company_id}_12");
        }
        if (!$fuelRefill->receipt_path) {
            return;
        }

        if (Invoice::where('fuel_refill_id', $fuelRefill->id)->exists()) {
            return;
        }

        $invoice = Invoice::create([
            'company_id' => $fuelRefill->company_id,
            'fuel_refill_id' => $fuelRefill->id,
            'invoice_type' => Invoice::TYPE_FUEL,
            'invoice_number' => 'INV-F-' . $fuelRefill->id . '-' . now()->format('Ymd'),
            'subtotal' => (float) $fuelRefill->cost,
            'tax' => 0,
            'paid_amount' => 0,
        ]);

        try {
            app(InvoicePdfService::class)->generate($invoice);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function updated(FuelRefill $fuelRefill): void
    {
        if ($fuelRefill->company_id) {
            CompanyAnalyticsService::invalidateDashboardCache($fuelRefill->company_id);
        }
    }

    public function deleted(FuelRefill $fuelRefill): void
    {
        if ($fuelRefill->company_id) {
            CompanyAnalyticsService::invalidateDashboardCache($fuelRefill->company_id);
        }
    }
}
