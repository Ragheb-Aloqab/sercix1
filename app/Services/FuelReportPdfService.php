<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class FuelReportPdfService
{
    /**
     * Generate PDF for the fuel report.
     * Uses mpdf for proper Arabic/RTL support.
     */
    public function generate(
        Company $company,
        Collection $rows,
        float $totalCost,
        float $totalLiters,
        int $refillCount,
        string $dateFrom,
        string $dateTo,
        ?Vehicle $vehicle = null
    ): string {
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        $title = (string) (__('fuel.title') ?: 'Fuel Report');
        $companyName = $company->company_name ?? __('common.company');
        $periodLabel = $dateFrom . ' — ' . $dateTo;
        if ($vehicle) {
            $periodLabel .= ' (' . $vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) . ')';
        }

        $summaryRows = [
            [__('fuel.total_fuel_cost'), number_format($totalCost, 2) . ' ' . __('company.sar')],
            [__('fuel.total_liters'), number_format($totalLiters, 1)],
            [__('fuel.refill_count'), (string) $refillCount],
        ];

        $summaryTable = '';
        foreach ($summaryRows as $r) {
            $summaryTable .= '<tr><td>' . $this->e($r[0]) . '</td><td style="text-align:right; font-weight:bold">' . $this->e($r[1]) . '</td></tr>';
        }

        $headerLabels = [
            __('fuel.date'),
            __('fuel.vehicle'),
            __('fuel.quantity'),
            __('company.cost'),
            __('fuel.odometer'),
            __('fuel.source'),
            __('fuel.invoice'),
        ];

        $dataRows = '';
        foreach ($rows as $row) {
            if ($row->type === 'refill') {
                $fr = $row->refill;
                $vehicleObj = $fr->vehicle;
                $vehicleStr = $vehicleObj
                    ? ($vehicleObj->plate_number . ' — ' . trim(($vehicleObj->make ?? '') . ' ' . ($vehicleObj->model ?? '')))
                    : '—';
                $liters = $fr->liters !== null ? number_format((float) $fr->liters, 1) : '—';
                $cost = number_format((float) ($fr->cost ?? 0), 2);
                $odometer = $fr->odometer_km ? number_format($fr->odometer_km) . ' ' . __('common.km') : '—';
                $source = $fr->isFromExternalProvider() ? $this->e($fr->provider ?? '—') : $this->e(__('fuel.manual'));
                $invoiceDisplay = $fr->invoice ? $this->e(__('common.yes')) : ($fr->receipt_path ? $this->e(__('fuel.receipt')) : '—');
            } else {
                $inv = $row->invoice;
                $vehicleObj = $inv->vehicle;
                $vehicleStr = $vehicleObj
                    ? ($vehicleObj->plate_number . ' — ' . trim(($vehicleObj->make ?? '') . ' ' . ($vehicleObj->model ?? '')))
                    : '—';
                $liters = '—';
                $cost = number_format((float) ($inv->amount ?? 0), 2);
                $odometer = '—';
                $source = $this->e(__('fuel.company_upload'));
                $invoiceDisplay = !empty($inv->invoice_file) ? $this->e(__('common.yes')) : '—';
            }

            $dataRows .= '<tr>'
                . '<td>' . $this->e($row->date?->format('Y-m-d H:i') ?? '—') . '</td>'
                . '<td>' . $this->e($vehicleStr) . '</td>'
                . '<td>' . $this->e($liters) . '</td>'
                . '<td style="text-align:right">' . $this->e($cost) . '</td>'
                . '<td>' . $this->e($odometer) . '</td>'
                . '<td>' . $source . '</td>'
                . '<td>' . $invoiceDisplay . '</td>'
                . '</tr>';
        }

        $generatedAt = Carbon::now()->format('Y-m-d H:i');
        $dir = $isRtl ? 'rtl' : 'ltr';
        $lang = $isRtl ? 'ar' : 'en';
        $textAlign = $isRtl ? 'right' : 'left';

        $titleEsc = $this->e($title);
        $companyNameEsc = $this->e($companyName);
        $periodLabelEsc = $this->e($periodLabel);
        $generatedAtEsc = $this->e(__('reports.generated_on', ['date' => $generatedAt]));
        $reportMetricEsc = $this->e(__('reports.metric'));
        $reportValueEsc = $this->e(__('reports.value'));
        $refillsLogEsc = $this->e(__('fuel.refills_log'));

        $headerCells = '';
        foreach ($headerLabels as $i => $label) {
            $align = $i === 3 ? 'right' : $textAlign;
            $headerCells .= '<th style="text-align:' . $align . '; padding:6px 8px">' . $this->e($label) . '</th>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html dir="{$dir}" lang="{$lang}">
<head>
    <meta charset="utf-8">
    <title>{$this->e($title)}</title>
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
    <h1>{$titleEsc}</h1>
    <div class="meta">
        <p><strong>{$companyNameEsc}</strong></p>
        <p><strong>{$periodLabelEsc}</strong></p>
    </div>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="text-align:{$textAlign}">{$reportMetricEsc}</th>
                <th style="text-align:right">{$reportValueEsc}</th>
            </tr>
        </thead>
        <tbody>{$summaryTable}</tbody>
    </table>
    <h2 style="font-size: 12px; margin-top: 16px;">{$refillsLogEsc}</h2>
    <table class="data-table">
        <thead>
            <tr>{$headerCells}</tr>
        </thead>
        <tbody>{$dataRows}</tbody>
    </table>
    <p class="footer">{$generatedAtEsc}</p>
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
