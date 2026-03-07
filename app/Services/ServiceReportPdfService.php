<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class ServiceReportPdfService
{
    /**
     * Generate PDF for the service report.
     * Uses mpdf for proper Arabic/RTL support (same font handling as other reports).
     */
    public function generate(
        Company $company,
        Collection $allItems,
        array $totals,
        array $analytics,
        array $byServiceType,
        string $dateFrom,
        string $dateTo,
        ?Vehicle $vehicle = null
    ): string {
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        $title = (string) (__('reports.service_report') ?: 'Service Report');
        $companyName = $company->company_name ?? __('common.company');
        $periodLabel = $dateFrom . ' — ' . $dateTo;
        if ($vehicle) {
            $periodLabel .= ' (' . $vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) . ')';
        }

        $summaryRows = [
            [__('reports.total_service_cost'), number_format($totals['total_cost'] ?? 0, 2) . ' ' . __('company.sar')],
            [__('reports.order_count'), (string) ($totals['order_count'] ?? 0)],
        ];

        $summaryTable = '';
        foreach ($summaryRows as $r) {
            $summaryTable .= '<tr><td>' . e($r[0]) . '</td><td style="text-align:right; font-weight:bold">' . e($r[1]) . '</td></tr>';
        }

        $headerLabels = [
            __('fuel.date'),
            '#',
            __('fuel.vehicle'),
            __('reports.services'),
            __('company.cost'),
            __('orders.status_label'),
            (string) (__('maintenance.invoice') ?: 'Invoice'),
        ];

        $invoiceRows = '';
        foreach ($allItems as $row) {
            $vehicleObj = $row->order?->vehicle ?? $row->maintenanceRequest?->vehicle;
            $vehicleStr = $vehicleObj
                ? ($vehicleObj->plate_number . ' — ' . trim(($vehicleObj->make ?? '') . ' ' . ($vehicleObj->model ?? '')))
                : '—';
            $ref = $row->type === 'order' ? (string) $row->order->id : 'MR-' . $row->maintenanceRequest->id;
            $serviceStr = $row->serviceName . ($row->orderServicesCount > 1 ? ' +' . ($row->orderServicesCount - 1) : '');
            $invoiceDisplay = $row->invoiceDisplay ?? '—';
            $invoiceRows .= '<tr>'
                . '<td>' . e($row->date?->format('Y-m-d H:i') ?? '—') . '</td>'
                . '<td>' . e($ref) . '</td>'
                . '<td>' . e($vehicleStr) . '</td>'
                . '<td>' . e($serviceStr) . '</td>'
                . '<td style="text-align:right">' . number_format($row->amount, 2) . '</td>'
                . '<td>' . e($row->statusLabel) . '</td>'
                . '<td>' . e($invoiceDisplay) . '</td>'
                . '</tr>';
        }

        $generatedAt = Carbon::now()->format('Y-m-d H:i');
        $dir = $isRtl ? 'rtl' : 'ltr';
        $lang = $isRtl ? 'ar' : 'en';
        $textAlign = $isRtl ? 'right' : 'left';

        $headerCells = '';
        foreach ($headerLabels as $i => $label) {
            $align = $i === 4 ? 'right' : $textAlign;
            $headerCells .= '<th style="text-align:' . $align . '; padding:6px 8px">' . e($label) . '</th>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html dir="{$dir}" lang="{$lang}">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-size: 10px; padding: 20px; margin: 0; }
        h1 { font-size: 16px; margin-bottom: 6px; }
        .meta { margin-bottom: 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background: #1E3A5F; color: #fff; font-weight: bold; }
        .summary-table { max-width: 360px; margin-bottom: 20px; }
        .data-table { font-size: 9px; }
        .data-table td { word-wrap: break-word; }
        .footer { margin-top: 20px; font-size: 8px; color: #888; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <div class="meta">
        <p><strong>{$companyName}</strong></p>
        <p><strong>{$periodLabel}</strong></p>
    </div>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="text-align:{$textAlign}">{$this->e(__('reports.metric'))}</th>
                <th style="text-align:right">{$this->e(__('reports.value'))}</th>
            </tr>
        </thead>
        <tbody>{$summaryTable}</tbody>
    </table>
    <h2 style="font-size: 12px; margin-top: 16px;">{$this->e(__('reports.services_log'))}</h2>
    <table class="data-table">
        <thead>
            <tr>{$headerCells}</tr>
        </thead>
        <tbody>{$invoiceRows}</tbody>
    </table>
    <p class="footer">{$this->e(__('reports.generated_on', ['date' => $generatedAt]))}</p>
</body>
</html>
HTML;

        $config = [
            'format' => 'A4',
            'default_font_size' => 10,
        ];

        if ($isRtl) {
            $config['default_font'] = 'xbriyaz';
        } else {
            $config['default_font'] = 'dejavusans';
        }

        $pdf = PDF::loadHTML($html, $config);

        if ($isRtl) {
            $pdf->getMpdf()->SetDirectionality('rtl');
        }

        return $pdf->output();
    }

    private function e(?string $s): string
    {
        return $s !== null ? htmlspecialchars($s, ENT_QUOTES, 'UTF-8') : '';
    }
}
