<?php

namespace App\Services;

use App\Models\CompanyMaintenanceInvoice;
use App\Models\MaintenanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Tax report data for company maintenance invoices and maintenance requests with tax.
 * Supports filtering by vehicle and date range.
 */
class TaxReportService
{
    private const VAT_RATE = 0.15;

    /**
     * Get tax report data for a company.
     * Includes: (1) Company maintenance invoices with tax, (2) Maintenance requests with final_invoice_tax_type = 'with_tax'.
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
        $companyInvoicesQuery = CompanyMaintenanceInvoice::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('tax_type', CompanyMaintenanceInvoice::TAX_WITH)
                    ->orWhere('vat_amount', '>', 0);
            })
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom->copy()->startOfDay()))
            ->when($dateTo, fn ($q) => $q->where('created_at', '<=', $dateTo->copy()->endOfDay()));

        $companyInvoices = (clone $companyInvoicesQuery)
            ->with(['vehicle:id,plate_number,make,model,name', 'services:id,name'])
            ->orderByDesc('created_at')
            ->get();

        // Maintenance requests with final invoice marked "with tax"
        $mrQuery = MaintenanceRequest::where('company_id', $companyId)
            ->where('final_invoice_tax_type', 'with_tax')
            ->whereRaw('(COALESCE(final_invoice_amount, 0) > 0 OR COALESCE(approved_quote_amount, 0) > 0)')
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->with(['vehicle:id,plate_number,make,model,name', 'requestServices.service', 'requestServices.driverProposedService']);

        if ($dateFrom || $dateTo) {
            $mrQuery->where(function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) {
                    $q->whereBetween(DB::raw('COALESCE(final_invoice_uploaded_at, created_at)'), [
                        $dateFrom->copy()->startOfDay(),
                        $dateTo->copy()->endOfDay(),
                    ]);
                } elseif ($dateFrom) {
                    $q->whereRaw('COALESCE(final_invoice_uploaded_at, created_at) >= ?', [$dateFrom->copy()->startOfDay()]);
                } else {
                    $q->whereRaw('COALESCE(final_invoice_uploaded_at, created_at) <= ?', [$dateTo->copy()->endOfDay()]);
                }
            });
        }

        $maintenanceRequests = $mrQuery->get();

        $mrRows = $maintenanceRequests->map(function (MaintenanceRequest $mr) {
            $total = (float) ($mr->final_invoice_amount ?? $mr->approved_quote_amount ?? 0);
            $amountBeforeTax = round($total / (1 + self::VAT_RATE), 2);
            $vatAmount = round($total - $amountBeforeTax, 2);
            $date = $mr->final_invoice_uploaded_at ?? $mr->created_at;
            $servicesList = $mr->requestServices->isNotEmpty()
                ? $mr->requestServices->map(fn ($rs) => (object) ['name' => $rs->display_name])
                : collect([(object) ['name' => __('maintenance.maintenance_request') . ' #' . $mr->id]]);
            return (object) [
                'created_at' => $date,
                'vehicle' => $mr->vehicle,
                'services' => $servicesList,
                'original_amount' => $amountBeforeTax,
                'vat_amount' => $vatAmount,
                'amount' => $total,
            ];
        });

        $invoices = $companyInvoices->concat($mrRows->all())
            ->sortByDesc(fn ($i) => $i->created_at?->timestamp ?? 0)
            ->values();

        $totalInvoices = $invoices->count();
        $totalVatAmount = round($invoices->sum(fn ($i) => (float) ($i->vat_amount ?? 0)), 2);
        $totalIncludingVat = round($invoices->sum(fn ($i) => (float) ($i->amount ?? 0)), 2);
        $totalWithoutVat = round($invoices->sum(fn ($i) => (float) ($i->original_amount ?? $i->amount ?? 0)), 2);

        return [
            'total_invoices' => $totalInvoices,
            'total_vat_amount' => $totalVatAmount,
            'total_including_vat' => $totalIncludingVat,
            'total_without_vat' => $totalWithoutVat,
            'invoices' => $invoices,
        ];
    }
}
