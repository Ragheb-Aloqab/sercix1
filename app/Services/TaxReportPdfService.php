<?php

namespace App\Services;

use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TaxReportPdfService
{
    /**
     * Generate PDF for the tax report.
     *
     * @param  array  $data  Report data from TaxReportService
     */
    public function generate(Company $company, array $data, string $dateFrom, string $dateTo, ?string $vehicleLabel = null): string
    {
        $title = __('reports.tax_reports');
        $companyName = $company->company_name ?? 'Company';
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

        $invoiceRows = '';
        foreach ($data['invoices'] ?? [] as $inv) {
            $vehicleStr = $inv->vehicle
                ? ($inv->vehicle->plate_number . ' — ' . trim(($inv->vehicle->make ?? '') . ' ' . ($inv->vehicle->model ?? '')))
                : '—';
            $invoiceRows .= '<tr>'
                . '<td>' . e($inv->created_at?->format('Y-m-d H:i')) . '</td>'
                . '<td>' . e($vehicleStr) . '</td>'
                . '<td style="text-align:right">' . number_format($inv->original_amount ?? $inv->amount ?? 0, 2) . '</td>'
                . '<td style="text-align:right">' . number_format($inv->vat_amount ?? 0, 2) . '</td>'
                . '<td style="text-align:right; font-weight:bold">' . number_format($inv->amount ?? 0, 2) . '</td>'
                . '</tr>';
        }

        $generatedAt = Carbon::now()->format('Y-m-d H:i');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; padding: 24px; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        .meta { margin-bottom: 20px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
        td:last-child { text-align: right; }
        .summary-table { max-width: 400px; margin-bottom: 24px; }
        .invoice-table { font-size: 10px; }
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
                <th>Metric</th>
                <th style="text-align:right">Value</th>
            </tr>
        </thead>
        <tbody>{$summaryTable}</tbody>
    </table>
    <h2 style="font-size: 14px; margin-top: 24px;">Invoice Details</h2>
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Vehicle</th>
                <th style="text-align:right">Amount</th>
                <th style="text-align:right">VAT</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>{$invoiceRows}</tbody>
    </table>
    <p style="margin-top: 24px; font-size: 9px; color: #888;">Generated on {$generatedAt}</p>
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
