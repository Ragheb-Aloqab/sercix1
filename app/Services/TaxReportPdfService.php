<?php

namespace App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class TaxReportPdfService
{
    /**
     * Generate PDF for the tax report.
     * Uses mpdf for proper Arabic/RTL support.
     *
     * @param  array  $data  Report data from TaxReportService
     */
    public function generate(Company $company, array $data, string $dateFrom, string $dateTo, ?string $vehicleLabel = null): string
    {
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        $title = __('reports.tax_reports');
        $companyName = $company->company_name ?? __('common.company');
        $periodLabel = $dateFrom . ' — ' . $dateTo;
        if ($vehicleLabel) {
            $periodLabel .= ' (' . $vehicleLabel . ')';
        }

        $summaryRows = [
            [__('reports.total_invoices'), number_format($data['total_invoices'] ?? 0, 0)],
            [__('reports.total_vat_amount'), number_format($data['total_vat_amount'] ?? 0, 2) . ' ' . __('company.sar')],
            [__('reports.total_including_vat'), number_format($data['total_including_vat'] ?? 0, 2) . ' ' . __('company.sar')],
        ];

        $summaryTable = '';
        foreach ($summaryRows as $r) {
            $summaryTable .= '<tr><td>' . e($r[0]) . '</td><td style="text-align:right; font-weight:bold">' . e($r[1]) . '</td></tr>';
        }

        // Column order: Vehicle | Invoice Amount | VAT (15%) | Total | Services | Date
        $headerLabels = [
            __('company.vehicle'),
            __('maintenance.invoice_amount'),
            __('maintenance.vat_amount') . ' (15%)',
            __('maintenance.total_with_tax'),
            __('maintenance.services'),
            __('reports.date'),
        ];

        $invoiceRows = '';
        foreach ($data['invoices'] ?? [] as $inv) {
            $vehicleStr = $inv->vehicle
                ? ($inv->vehicle->display_name ?? ($inv->vehicle->plate_number . ' — ' . trim(($inv->vehicle->make ?? '') . ' ' . ($inv->vehicle->model ?? ''))))
                : '—';
            $servicesStr = $inv->services->isNotEmpty()
                ? $inv->services->pluck('name')->join(', ')
                : '—';
            $invoiceRows .= '<tr>'
                . '<td>' . e($vehicleStr) . '</td>'
                . '<td style="text-align:right">' . number_format($inv->original_amount ?? $inv->amount ?? 0, 2) . '</td>'
                . '<td style="text-align:right">' . number_format($inv->vat_amount ?? 0, 2) . '</td>'
                . '<td style="text-align:right; font-weight:bold">' . number_format($inv->amount ?? 0, 2) . '</td>'
                . '<td>' . e($servicesStr) . '</td>'
                . '<td>' . e($inv->created_at?->format('Y-m-d')) . '</td>'
                . '</tr>';
        }

        $generatedAt = Carbon::now()->format('Y-m-d H:i');
        $dir = $isRtl ? 'rtl' : 'ltr';
        $lang = $isRtl ? 'ar' : 'en';
        $textAlign = $isRtl ? 'right' : 'left';

        $headerCells = '';
        foreach ($headerLabels as $i => $label) {
            $align = in_array($i, [1, 2, 3]) ? 'right' : $textAlign;
            $headerCells .= '<th style="text-align:' . $align . '; padding:8px 10px">' . e($label) . '</th>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html dir="{$dir}" lang="{$lang}">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-size: 11px; padding: 24px; margin: 0; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        .meta { margin-bottom: 20px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px 10px; }
        th { background: #1E3A5F; color: #fff; font-weight: bold; }
        .summary-table { max-width: 400px; margin-bottom: 24px; }
        .invoice-table { font-size: 10px; }
        .invoice-table td { word-wrap: break-word; }
        .footer { margin-top: 24px; font-size: 9px; color: #888; }
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
    <h2 style="font-size: 14px; margin-top: 24px;">{$this->e(__('reports.invoice_details'))}</h2>
    <table class="invoice-table">
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
            'default_font_size' => 11,
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
