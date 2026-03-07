<?php

namespace App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Mpdf;

class MileageReportPdfService
{
    /**
     * Generate per-vehicle mileage report PDF.
     * Supports Arabic/RTL and English like other reports.
     */
    public function generateVehicleReport(int $companyId, array $rows, array $summary, Carbon $from, Carbon $to): string
    {
        $company = Company::find($companyId);
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        $title = __('vehicles.vehicle_mileage_reports');
        $companyName = $company?->company_name ?? __('common.company');
        $periodLabel = $from->format('Y-m-d') . ' — ' . $to->format('Y-m-d');
        $periodLabelT = __('reports.period') . ': ' . $periodLabel;

        $totalVehiclesLbl = __('vehicles.total_vehicles');
        $totalMileageLbl = __('vehicles.total_mileage_this_period');
        $avgPerVehicleLbl = __('vehicles.avg_mileage_per_vehicle');
        $plateLbl = __('fleet.plate_number');
        $vehicleLbl = __('fleet.vehicle_name');
        $branchLbl = __('fleet.branch');
        $dailyOdometerLbl = __('vehicles.daily_total_distance') . ' (' . __('common.km') . ')';
        $monthTotalLbl = __('vehicles.month_total_distance') . ' (' . __('common.km') . ')';
        $totalDistanceAllLbl = __('vehicles.total_distance_all') . ' (' . __('common.km') . ')';
        $lastUpdateLbl = __('vehicles.last_update_date');
        $statusLbl = __('vehicles.status');

        $tableRows = '';
        foreach ($rows as $r) {
            $statusLabel = __("vehicles.status_{$r['status']}");
            $distanceCell = ($r['has_anomaly'] ?? false)
                ? '—'
                : number_format($r['total_distance'] ?? 0, 1);
            $tableRows .= '<tr>';
            $tableRows .= '<td>' . e($r['plate_number'] ?? '-') . '</td>';
            $tableRows .= '<td>' . e($r['vehicle_name'] ?? '-') . '</td>';
            $tableRows .= '<td>' . e($r['branch_name'] ?? '-') . '</td>';
            $tableRows .= '<td style="text-align:right">' . $distanceCell . '</td>';
            $tableRows .= '<td style="text-align:right">' . number_format($r['current_mileage'] ?? 0, 1) . '</td>';
            $tableRows .= '<td style="text-align:right">' . number_format($r['daily_odometer'] ?? 0, 1) . '</td>';
            $tableRows .= '<td>' . e($r['last_update_date'] ?? '-') . '</td>';
            $tableRows .= '<td>' . e($statusLabel) . '</td>';
            $tableRows .= '</tr>';
        }

        $dir = $isRtl ? 'rtl' : 'ltr';
        $lang = $isRtl ? 'ar' : 'en';
        $textAlign = $isRtl ? 'right' : 'left';

        $html = <<<HTML
<!DOCTYPE html>
<html dir="{$dir}" lang="{$lang}">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-size: 10px; padding: 20px; margin: 0; }
        h1 { font-size: 16px; margin-bottom: 8px; }
        .summary { display: flex; gap: 20px; margin: 16px 0; flex-wrap: wrap; }
        .summary p { margin: 0 0 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: {$textAlign}; }
        th { background: #1E3A5F; color: #fff; font-weight: bold; }
        td:nth-child(4), td:nth-child(5) { text-align: right; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <p><strong>{$companyName}</strong></p>
    <p><strong>{$periodLabelT}</strong></p>
    <div class="summary">
        <p><strong>{$totalVehiclesLbl}:</strong> {$summary['total_vehicles']}</p>
        <p><strong>{$totalMileageLbl}:</strong> {$summary['total_mileage_this_period']} km</p>
        <p><strong>{$avgPerVehicleLbl}:</strong> {$summary['average_mileage_per_vehicle']} km</p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="text-align:{$textAlign}">{$plateLbl}</th>
                <th style="text-align:{$textAlign}">{$vehicleLbl}</th>
                <th style="text-align:{$textAlign}">{$branchLbl}</th>
                <th style="text-align:right">{$monthTotalLbl}</th>
                <th style="text-align:right">{$totalDistanceAllLbl}</th>
                <th style="text-align:right">{$dailyOdometerLbl}</th>
                <th style="text-align:{$textAlign}">{$lastUpdateLbl}</th>
                <th style="text-align:{$textAlign}">{$statusLbl}</th>
            </tr>
        </thead>
        <tbody>{$tableRows}</tbody>
    </table>
</body>
</html>
HTML;

        if ($isRtl) {
            $config = ['format' => 'A4', 'default_font_size' => 10, 'default_font' => 'xbriyaz'];
            $pdf = Mpdf::loadHTML($html, $config);
            $pdf->getMpdf()->SetDirectionality('rtl');
            return $pdf->output();
        }

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->output();
    }

    public function generate(int $companyId, int $months = 6): string
    {
        $company = Company::find($companyId);
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        $mileageService = app(VehicleMileageService::class);
        $report = $mileageService->getCompanyMonthlySummary($companyId, $months);

        $totalAccumulated = $mileageService->getCompanyAccumulatedMileage($companyId);
        $currentMonthKm = $mileageService->getCompanyMonthlyMileage($companyId, (int) now()->month, (int) now()->year);
        $estimatedCost = $mileageService->getEstimatedMarketCost($currentMonthKm);

        $tableRows = '';
        foreach ($report as $r) {
            $tableRows .= '<tr><td>' . e($r['month_label'] ?? '') . '</td><td style="text-align:right">' . number_format($r['total_monthly_mileage_km'] ?? 0, 1) . '</td><td style="text-align:right">' . number_format($r['estimated_market_cost_sar'] ?? 0, 2) . ' ' . __('company.sar') . '</td></tr>';
        }

        $title = __('vehicles.monthly_mileage') . ' ' . __('vehicles.vehicle_report');
        $companyName = $company?->company_name ?? __('common.company');
        $totalAccLbl = __('vehicles.accumulated_mileage');
        $currentMonthLbl = __('vehicles.monthly_mileage');
        $estCostLbl = __('vehicles.estimated_market_cost');
        $monthLbl = __('reports.month');
        $totalKmLbl = __('vehicles.total_mileage_this_period');
        $sarLabel = __('company.sar');

        $dir = $isRtl ? 'rtl' : 'ltr';
        $lang = $isRtl ? 'ar' : 'en';
        $textAlign = $isRtl ? 'right' : 'left';

        $html = <<<HTML
<!DOCTYPE html>
<html dir="{$dir}" lang="{$lang}">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-size: 11px; padding: 20px; margin: 0; }
        h1 { font-size: 16px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: {$textAlign}; }
        th { background: #1E3A5F; color: #fff; font-weight: bold; }
        td:nth-child(2), td:nth-child(3) { text-align: right; }
        .summary { margin: 16px 0; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <p><strong>{$companyName}</strong></p>
    <div class="summary">
        <p><strong>{$totalAccLbl}:</strong> {$totalAccumulated} km</p>
        <p><strong>{$currentMonthLbl}:</strong> {$currentMonthKm} km</p>
        <p><strong>{$estCostLbl}:</strong> {$estimatedCost} {$sarLabel}</p>
    </div>
    <table>
        <thead><tr><th style="text-align:{$textAlign}">{$monthLbl}</th><th style="text-align:right">{$totalKmLbl}</th><th style="text-align:right">{$estCostLbl} ({$sarLabel})</th></tr></thead>
        <tbody>{$tableRows}</tbody>
    </table>
</body>
</html>
HTML;

        if ($isRtl) {
            $config = ['format' => 'A4', 'default_font_size' => 11, 'default_font' => 'xbriyaz'];
            $pdf = Mpdf::loadHTML($html, $config);
            $pdf->getMpdf()->SetDirectionality('rtl');
            return $pdf->output();
        }

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->output();
    }

}
