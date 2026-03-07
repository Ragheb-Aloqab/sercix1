<?php

namespace App\Services;

use App\Services\Report\VehicleReportDataProvider;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Mpdf;

class VehicleReportPdfService
{
    public function __construct(
        private readonly VehicleReportDataProvider $dataProvider
    ) {}

    /**
     * Generate PDF from pre-fetched data (used by ReportExportService).
     *
     * @param  array{rows: array, fuel_total: float, maintenance_total: float, combined_total: float, vehicle: Vehicle, type: string, date_from?: string|null, date_to?: string|null}  $data
     */
    public function generateFromData(array $data): string
    {
        $vehicle = $data['vehicle'] ?? null;
        if (!$vehicle instanceof Vehicle) {
            throw new \InvalidArgumentException('Vehicle is required.');
        }

        $rows = $data['rows'] ?? [];
        $fuelTotal = (float) ($data['fuel_total'] ?? 0);
        $maintenanceTotal = (float) ($data['maintenance_total'] ?? 0);
        $combinedTotal = (float) ($data['combined_total'] ?? 0);
        $type = $data['type'] ?? 'all';
        $dateFrom = $data['date_from'] ?? null;
        $dateTo = $data['date_to'] ?? null;

        $html = $this->buildHtml($vehicle, $rows, $fuelTotal, $maintenanceTotal, $combinedTotal, $type, $dateFrom, $dateTo);

        return $this->renderPdf($html);
    }

    /**
     * Generate PDF (legacy method - delegates to data provider).
     */
    public function generate(Vehicle $vehicle, string $type, ?string $dateFrom, ?string $dateTo): string
    {
        $data = $this->dataProvider->getData([
            'vehicle' => $vehicle,
            'type' => $type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        return $this->generateFromData($data);
    }

    private function buildHtml(Vehicle $vehicle, array $rows, float $fuelTotal, float $maintenanceTotal, float $combinedTotal, string $type, ?string $dateFrom, ?string $dateTo): string
    {
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';
        $dir = $isRtl ? 'rtl' : 'ltr';
        $lang = $isRtl ? 'ar' : 'en';
        $textAlign = $isRtl ? 'right' : 'left';

        $title = __('vehicles.vehicle_report') . ' - ' . ($vehicle->plate_number ?? $vehicle->display_name);
        $dateRange = ($dateFrom && $dateTo) ? $dateFrom . ' — ' . $dateTo : __('vehicles.all_dates');

        $tableRows = '';
        foreach ($rows as $r) {
            $desc = $r['description'] ?? $r['desc'] ?? '';
            $typeLabel = ($r['type'] ?? '') === 'fuel' ? __('company.fuel') : __('company.maintenance');
            $tableRows .= '<tr><td>' . e($r['date'] ?? '') . '</td><td>' . e($typeLabel) . '</td><td>' . e($desc) . '</td><td style="text-align:right">' . number_format((float) ($r['cost'] ?? 0), 2) . '</td></tr>';
        }

        $summary = '';
        if (in_array($type, ['fuel', 'all'])) {
            $summary .= '<p><strong>' . __('company.total_fuel_cost') . ':</strong> ' . number_format($fuelTotal, 2) . ' ' . __('company.sar') . '</p>';
        }
        if (in_array($type, ['maintenance', 'all'])) {
            $summary .= '<p><strong>' . __('company.total_maintenance_cost') . ':</strong> ' . number_format($maintenanceTotal, 2) . ' ' . __('company.sar') . '</p>';
        }
        $summary .= '<p><strong>' . __('vehicles.combined_total') . ':</strong> ' . number_format($combinedTotal, 2) . ' ' . __('company.sar') . '</p>';

        $dateLabel = __('fuel.date');
        $typeLabel = __('vehicles.report_type');
        $descLabel = __('vehicles.description');
        $costLabel = __('company.cost') . ' (SAR)';

        $html = <<<HTML
<!DOCTYPE html>
<html dir="{$dir}" lang="{$lang}">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-size: 11px; padding: 24px; margin: 0; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: {$textAlign}; }
        th { background: #1E3A5F; color: #fff; font-weight: bold; }
        td:last-child { text-align: right; }
        .summary { margin-top: 16px; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <p>{$dateRange}</p>
    <table>
        <thead><tr><th>{$dateLabel}</th><th>{$typeLabel}</th><th>{$descLabel}</th><th style="text-align:right">{$costLabel}</th></tr></thead>
        <tbody>{$tableRows}</tbody>
    </table>
    <div class="summary">{$summary}</div>
</body>
</html>
HTML;

        return $html;
    }

    private function renderPdf(string $html): string
    {
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        if ($isRtl) {
            $config = [
                'format' => 'A4',
                'default_font_size' => 11,
                'default_font' => 'xbriyaz',
            ];
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
