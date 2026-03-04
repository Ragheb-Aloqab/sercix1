<?php

namespace App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MileageReportPdfService
{
    /**
     * Generate per-vehicle mileage report PDF.
     */
    public function generateVehicleReport(int $companyId, array $rows, array $summary, Carbon $from, Carbon $to): string
    {
        $company = Company::find($companyId);
        $title = __('vehicles.vehicle_mileage_reports');
        $companyName = $company?->company_name ?? 'Company';

        $tableRows = '';
        foreach ($rows as $r) {
            $statusLabel = __("vehicles.status_{$r['status']}");
            $tableRows .= '<tr>';
            $tableRows .= '<td>' . e($r['plate_number'] ?? '-') . '</td>';
            $tableRows .= '<td>' . e($r['vehicle_name'] ?? '-') . '</td>';
            $tableRows .= '<td style="text-align:right">' . number_format($r['current_mileage'] ?? 0, 1) . '</td>';
            $tableRows .= '<td style="text-align:right">' . number_format($r['previous_mileage'] ?? 0, 1) . '</td>';
            $tableRows .= '<td style="text-align:right">' . number_format($r['total_distance'] ?? 0, 1) . '</td>';
            $tableRows .= '<td>' . e($r['last_update_date'] ?? '-') . '</td>';
            $tableRows .= '<td>' . e($statusLabel) . '</td>';
            $tableRows .= '</tr>';
        }

        $periodLabel = $from->format('Y-m-d') . ' — ' . $to->format('Y-m-d');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; padding: 20px; }
        h1 { font-size: 16px; margin-bottom: 8px; }
        .summary { display: flex; gap: 20px; margin: 16px 0; flex-wrap: wrap; }
        .summary p { margin: 0 0 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <p><strong>{$companyName}</strong></p>
    <p><strong>Period:</strong> {$periodLabel}</p>
    <div class="summary">
        <p><strong>Total Vehicles:</strong> {$summary['total_vehicles']}</p>
        <p><strong>Total Mileage (Period):</strong> {$summary['total_mileage_this_period']} km</p>
        <p><strong>Average per Vehicle:</strong> {$summary['average_mileage_per_vehicle']} km</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Plate</th>
                <th>Vehicle</th>
                <th style="text-align:right">Current (km)</th>
                <th style="text-align:right">Previous (km)</th>
                <th style="text-align:right">Distance (km)</th>
                <th>Last Update</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>{$tableRows}</tbody>
    </table>
</body>
</html>
HTML;

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->output();
    }

    public function generate(int $companyId, int $months = 6): string
    {
        $company = Company::find($companyId);
        $mileageService = app(VehicleMileageService::class);
        $report = $mileageService->getCompanyMonthlySummary($companyId, $months);

        $totalAccumulated = $mileageService->getCompanyAccumulatedMileage($companyId);
        $currentMonthKm = $mileageService->getCompanyMonthlyMileage($companyId, (int) now()->month, (int) now()->year);
        $estimatedCost = $mileageService->getEstimatedMarketCost($currentMonthKm);

        $tableRows = '';
        foreach ($report as $r) {
            $tableRows .= '<tr><td>' . ($r['month_label'] ?? '') . '</td><td style="text-align:right">' . number_format($r['total_monthly_mileage_km'] ?? 0, 1) . '</td><td style="text-align:right">' . number_format($r['estimated_market_cost_sar'] ?? 0, 2) . ' SAR</td></tr>';
        }

        $title = __('vehicles.monthly_mileage') . ' ' . __('vehicles.vehicle_report');
        $companyName = $company?->company_name ?? 'Company';

        $html = <<<HTML
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
        .summary { margin: 16px 0; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <p><strong>{$companyName}</strong></p>
    <div class="summary">
        <p><strong>Total Accumulated Mileage:</strong> {$totalAccumulated} km</p>
        <p><strong>Current Month Mileage:</strong> {$currentMonthKm} km</p>
        <p><strong>Estimated Market Cost (this month):</strong> {$estimatedCost} SAR</p>
    </div>
    <table>
        <thead><tr><th>Month</th><th style="text-align:right">Total KM</th><th style="text-align:right">Est. Market Cost (SAR)</th></tr></thead>
        <tbody>{$tableRows}</tbody>
    </table>
</body>
</html>
HTML;

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->output();
    }
}
