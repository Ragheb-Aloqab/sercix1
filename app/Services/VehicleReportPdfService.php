<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\FuelRefill;
use App\Models\MaintenanceRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class VehicleReportPdfService
{
    public function generate(Vehicle $vehicle, string $type, ?string $dateFrom, ?string $dateTo): string
    {
        $fuelTotal = 0.0;
        $maintenanceTotal = 0.0;
        $rows = [];

        if (in_array($type, ['fuel', 'all'])) {
            $q = FuelRefill::where('vehicle_id', $vehicle->id);
            if ($dateFrom) {
                $q->where('refilled_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $q->where('refilled_at', '<=', $dateTo . ' 23:59:59');
            }
            foreach ($q->orderBy('refilled_at')->get() as $fr) {
                $cost = (float) $fr->cost;
                $fuelTotal += $cost;
                $rows[] = [
                    'date' => $fr->refilled_at?->format('Y-m-d H:i'),
                    'type' => __('company.fuel'),
                    'desc' => $fr->liters . ' L',
                    'cost' => $cost,
                ];
            }
        }

        if (in_array($type, ['maintenance', 'all'])) {
            $q = MaintenanceRequest::where('vehicle_id', $vehicle->id)
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
                $maintenanceTotal += $cost;
                $rows[] = [
                    'date' => $mr->created_at?->format('Y-m-d H:i'),
                    'type' => __('company.maintenance'),
                    'desc' => 'Request #' . $mr->id,
                    'cost' => $cost,
                ];
            }
        }

        usort($rows, fn ($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
        $combinedTotal = $fuelTotal + $maintenanceTotal;

        $html = $this->buildHtml($vehicle, $rows, $fuelTotal, $maintenanceTotal, $combinedTotal, $type, $dateFrom, $dateTo);

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->output();
    }

    private function buildHtml(Vehicle $vehicle, array $rows, float $fuelTotal, float $maintenanceTotal, float $combinedTotal, string $type, ?string $dateFrom, ?string $dateTo): string
    {
        $title = __('vehicles.vehicle_report') . ' - ' . ($vehicle->plate_number ?? $vehicle->display_name);
        $dateRange = ($dateFrom && $dateTo) ? $dateFrom . ' — ' . $dateTo : __('vehicles.all_dates');

        $tableRows = '';
        foreach ($rows as $r) {
            $tableRows .= '<tr><td>' . ($r['date'] ?? '') . '</td><td>' . ($r['type'] ?? '') . '</td><td>' . ($r['desc'] ?? '') . '</td><td style="text-align:right">' . number_format($r['cost'], 2) . '</td></tr>';
        }

        $summary = '';
        if (in_array($type, ['fuel', 'all'])) {
            $summary .= '<p><strong>' . __('company.total_fuel_cost') . ':</strong> ' . number_format($fuelTotal, 2) . ' SAR</p>';
        }
        if (in_array($type, ['maintenance', 'all'])) {
            $summary .= '<p><strong>' . __('company.total_maintenance_cost') . ':</strong> ' . number_format($maintenanceTotal, 2) . ' SAR</p>';
        }
        $summary .= '<p><strong>' . __('vehicles.combined_total') . ':</strong> ' . number_format($combinedTotal, 2) . ' SAR</p>';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; padding: 20px; }
        h1 { font-size: 16px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #eee; }
        .summary { margin-top: 16px; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <p>{$dateRange}</p>
    <table>
        <thead><tr><th>Date</th><th>Type</th><th>Description</th><th>Cost (SAR)</th></tr></thead>
        <tbody>{$tableRows}</tbody>
    </table>
    <div class="summary">{$summary}</div>
</body>
</html>
HTML;
    }
}
