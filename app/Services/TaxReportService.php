<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyMaintenanceInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Tax report data for company maintenance invoices.
 * Supports filtering by vehicle and date range.
 */
class TaxReportService
{
    /**
     * Get tax report data for a company.
     *
     * @param  int  $companyId
     * @param  int|null  $vehicleId  Optional: filter by vehicle. Null = all vehicles.
     * @param  Carbon|null  $dateFrom
     * @param  Carbon|null  $dateTo
     * @return array{total_invoices: int, total_vat_amount: float, total_including_vat: float, total_without_vat: float, invoices: \Illuminate\Support\Collection}
     */
    public function getReportData(
        int $companyId,
        ?int $vehicleId = null,
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null
    ): array {
        $query = CompanyMaintenanceInvoice::where('company_id', $companyId)
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom->copy()->startOfDay()))
            ->when($dateTo, fn ($q) => $q->where('created_at', '<=', $dateTo->copy()->endOfDay()));

        $totals = (clone $query)->selectRaw('
            COUNT(*) as total_invoices,
            COALESCE(SUM(vat_amount), 0) as total_vat_amount,
            COALESCE(SUM(amount), 0) as total_including_vat,
            COALESCE(SUM(COALESCE(original_amount, amount)), 0) as total_without_vat
        ')->first();

        $invoices = (clone $query)
            ->with('vehicle:id,plate_number,make,model')
            ->orderByDesc('created_at')
            ->get();

        return [
            'total_invoices' => (int) ($totals->total_invoices ?? 0),
            'total_vat_amount' => round((float) ($totals->total_vat_amount ?? 0), 2),
            'total_including_vat' => round((float) ($totals->total_including_vat ?? 0), 2),
            'total_without_vat' => round((float) ($totals->total_without_vat ?? 0), 2),
            'invoices' => $invoices,
        ];
    }
}
