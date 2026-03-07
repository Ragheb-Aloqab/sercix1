<?php

namespace App\Services\Report;

use App\Contracts\ReportDataProviderInterface;
use App\Models\CompanyFuelInvoice;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\FuelRefill;
use App\Models\MaintenanceRequest;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

/**
 * Shared data provider for Vehicle Report (fuel + maintenance).
 * Eliminates duplication between VehicleReportPdfService and VehicleReportExport.
 */
class VehicleReportDataProvider implements ReportDataProviderInterface
{
    public function getType(): string
    {
        return 'vehicle';
    }

    /**
     * @param  array{vehicle: Vehicle, type: string, date_from?: string|null, date_to?: string|null}  $filters
     * @return array{rows: array, fuel_total: float, maintenance_total: float, combined_total: float, vehicle: Vehicle}
     */
    public function getData(array $filters): array
    {
        $vehicle = $filters['vehicle'] ?? null;
        if (!$vehicle instanceof Vehicle) {
            return $this->emptyResult();
        }

        $type = $filters['type'] ?? 'all';
        if (!in_array($type, ['fuel', 'maintenance', 'all'], true)) {
            $type = 'all';
        }
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $rows = collect();
        $fuelTotal = 0.0;
        $maintenanceTotal = 0.0;

        if (in_array($type, ['fuel', 'all'])) {
            $fuelData = $this->getFuelRows($vehicle->id, $dateFrom, $dateTo);
            foreach ($fuelData as $r) {
                $rows->push($r);
                $fuelTotal += (float) ($r['cost'] ?? 0);
            }
        }

        if (in_array($type, ['maintenance', 'all'])) {
            $maintenanceData = $this->getMaintenanceRows($vehicle->id, $dateFrom, $dateTo);
            foreach ($maintenanceData as $r) {
                $rows->push($r);
                $maintenanceTotal += (float) ($r['cost'] ?? 0);
            }
        }

        $rows = $rows->sortBy('date')->values()->all();
        $combinedTotal = $fuelTotal + $maintenanceTotal;

        return [
            'rows' => $rows,
            'fuel_total' => round($fuelTotal, 2),
            'maintenance_total' => round($maintenanceTotal, 2),
            'combined_total' => round($combinedTotal, 2),
            'vehicle' => $vehicle,
            'type' => $type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function getFuelRows(int $vehicleId, ?string $dateFrom, ?string $dateTo): Collection
    {
        $rows = collect();

        $q = FuelRefill::where('vehicle_id', $vehicleId);
        if ($dateFrom) {
            $q->where('refilled_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $q->where('refilled_at', '<=', $dateTo . ' 23:59:59');
        }
        foreach ($q->orderBy('refilled_at')->get() as $fr) {
            $cost = (float) $fr->cost;
            $rows->push([
                'date' => $fr->refilled_at?->format('Y-m-d H:i'),
                'type' => 'fuel',
                'description' => $fr->liters . ' L @ ' . ($fr->price_per_liter ?? 0) . ' SAR',
                'cost' => $cost,
            ]);
        }

        $fuelInvQ = CompanyFuelInvoice::where('vehicle_id', $vehicleId);
        if ($dateFrom) {
            $fuelInvQ->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $fuelInvQ->where('created_at', '<=', $dateTo . ' 23:59:59');
        }
        foreach ($fuelInvQ->orderBy('created_at')->get() as $inv) {
            $cost = (float) ($inv->amount ?? 0);
            $rows->push([
                'date' => $inv->created_at?->format('Y-m-d H:i'),
                'type' => 'fuel',
                'description' => __('maintenance.invoice') . ' #' . $inv->id,
                'cost' => $cost,
            ]);
        }

        return $rows->sortBy('date')->values();
    }

    private function getMaintenanceRows(int $vehicleId, ?string $dateFrom, ?string $dateTo): Collection
    {
        $rows = collect();

        $q = MaintenanceRequest::where('vehicle_id', $vehicleId)
            ->where(function ($mq) {
                $mq->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
            });
        if ($dateFrom) {
            $q->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $q->where('created_at', '<=', $dateTo . ' 23:59:59');
        }
        foreach ($q->orderBy('created_at')->get() as $mr) {
            $cost = (float) ($mr->final_invoice_amount ?? $mr->approved_quote_amount ?? 0);
            $rows->push([
                'date' => $mr->created_at?->format('Y-m-d H:i'),
                'type' => 'maintenance',
                'description' => 'Request #' . $mr->id . ' - ' . ($mr->maintenance_type ?? ''),
                'cost' => $cost,
            ]);
        }

        $invQ = CompanyMaintenanceInvoice::where('vehicle_id', $vehicleId);
        if ($dateFrom) {
            $invQ->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $invQ->where('created_at', '<=', $dateTo . ' 23:59:59');
        }
        foreach ($invQ->orderBy('created_at')->get() as $inv) {
            $cost = (float) ($inv->amount ?? 0);
            $rows->push([
                'date' => $inv->created_at?->format('Y-m-d H:i'),
                'type' => 'maintenance',
                'description' => __('maintenance.invoice') . ' #' . $inv->id,
                'cost' => $cost,
            ]);
        }

        return $rows->sortBy('date')->values();
    }

    private function emptyResult(): array
    {
        return [
            'rows' => [],
            'fuel_total' => 0.0,
            'maintenance_total' => 0.0,
            'combined_total' => 0.0,
            'vehicle' => null,
            'type' => 'all',
            'date_from' => null,
            'date_to' => null,
        ];
    }
}
