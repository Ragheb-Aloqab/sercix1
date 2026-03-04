<?php

namespace App\Services;

use App\Models\CompanyMaintenanceInvoice;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InvoiceSummaryService
{
    /**
     * Compute summary stats (total, average) for fuel and service invoices using efficient aggregation.
     */
    public function computeInvoiceSummary(Builder $baseQuery): array
    {
        $subQuery = (clone $baseQuery)->select([
            'invoices.id',
            'invoices.invoice_type',
            'invoices.subtotal',
            'invoices.tax',
            'invoices.order_id',
            'invoices.fuel_refill_id',
        ]);

        $sql = $subQuery->toSql();
        $bindings = $subQuery->getBindings();

        $driver = DB::connection()->getDriverName();
        $orderTotalExpr = '(SELECT COALESCE(SUM(total_price), 0) FROM order_services WHERE order_id = i.order_id)';
        $fuelCostExpr = '(SELECT COALESCE(cost, 0) FROM fuel_refills WHERE id = i.fuel_refill_id)';

        $totalExpr = match ($driver) {
            'mysql', 'mariadb' => "CASE
                WHEN (i.subtotal + i.tax) > 0 THEN i.subtotal + i.tax
                WHEN i.order_id IS NOT NULL THEN {$orderTotalExpr}
                WHEN i.fuel_refill_id IS NOT NULL THEN {$fuelCostExpr}
                ELSE 0
            END",
            default => "CASE
                WHEN (i.subtotal + i.tax) > 0 THEN i.subtotal + i.tax
                WHEN i.order_id IS NOT NULL THEN {$orderTotalExpr}
                WHEN i.fuel_refill_id IS NOT NULL THEN {$fuelCostExpr}
                ELSE 0
            END",
        };

        $stats = DB::select("
            SELECT
                i.invoice_type,
                COUNT(*) as count,
                SUM({$totalExpr}) as total_sum
            FROM ({$sql}) AS i
            GROUP BY i.invoice_type
        ", $bindings);

        $result = [
            'fuel_total' => 0.0,
            'fuel_avg' => 0.0,
            'fuel_count' => 0,
            'service_total' => 0.0,
            'service_avg' => 0.0,
            'service_count' => 0,
        ];

        foreach ($stats as $row) {
            $count = (int) $row->count;
            $total = (float) $row->total_sum;
            $avg = $count > 0 ? round($total / $count, 2) : 0.0;

            if ($row->invoice_type === Invoice::TYPE_FUEL) {
                $result['fuel_total'] = $total;
                $result['fuel_avg'] = $avg;
                $result['fuel_count'] = $count;
            } else {
                $result['service_total'] = $total;
                $result['service_avg'] = $avg;
                $result['service_count'] = $count;
            }
        }

        return $result;
    }

    /**
     * Compute maintenance invoice summary (from MaintenanceRequest + CompanyMaintenanceInvoice).
     */
    public function computeMaintenanceInvoiceSummary(
        int $companyId,
        ?\DateTimeInterface $from,
        ?\DateTimeInterface $to,
        int $vehicleId
    ): array {
        $reqQ = MaintenanceRequest::forCompany($companyId)
            ->whereNotNull('final_invoice_pdf_path')
            ->when($vehicleId > 0, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($from, fn ($q) => $q->where('final_invoice_uploaded_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('final_invoice_uploaded_at', '<=', $to));
        $reqTotal = (float) (clone $reqQ)->sum(DB::raw('COALESCE(final_invoice_amount, approved_quote_amount, 0)'));
        $reqCount = (clone $reqQ)->count();

        $companyQ = CompanyMaintenanceInvoice::where('company_id', $companyId)
            ->when($vehicleId > 0, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));
        $companyTotal = (float) (clone $companyQ)->sum('amount');
        $companyCount = (clone $companyQ)->count();

        $total = $reqTotal + $companyTotal;
        $count = $reqCount + $companyCount;

        return [
            'total' => round($total, 2),
            'avg' => $count > 0 ? round($total / $count, 2) : 0.0,
            'count' => $count,
        ];
    }
}
