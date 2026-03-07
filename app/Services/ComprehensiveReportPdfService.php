<?php

namespace App\Services;

use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Mpdf;

class ComprehensiveReportPdfService
{
    /**
     * Generate PDF for the comprehensive report.
     * Uses Mpdf for proper Arabic/RTL support when locale is Arabic.
     *
     * @param  array  $data  Report data from ComprehensiveReportService
     */
    public function generate(Company $company, array $data): string
    {
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        $title = __('reports.comprehensive_report');
        $companyName = $company->company_name ?? __('common.company');
        $periodLabel = $data['period_label'] ?? Carbon::now()->translatedFormat('F Y');

        $metricLabel = __('reports.metric');
        $valueLabel = __('reports.value');
        $generatedOnLabel = __('reports.generated_on', ['date' => Carbon::now()->format('Y-m-d H:i')]);

        $rows = [
            [__('reports.total_maintenance_cost'), number_format($data['total_maintenance_cost'] ?? 0, 2) . ' ' . __('company.sar')],
            [__('reports.total_fuel_cost'), number_format($data['total_fuel_cost'] ?? 0, 2) . ' ' . __('company.sar')],
            [__('reports.monthly_mileage'), number_format($data['monthly_mileage'] ?? 0, 2) . ' ' . __('common.km')],
            [__('reports.total_accumulated_mileage'), number_format($data['total_accumulated_mileage'] ?? 0, 2) . ' ' . __('common.km')],
        ];

        $tableRows = '';
        foreach ($rows as $r) {
            $tableRows .= '<tr><td>' . e($r[0]) . '</td><td style="text-align:right; font-weight:bold">' . e($r[1]) . '</td></tr>';
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
        body { font-size: 11px; padding: 24px; margin: 0; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        .meta { margin-bottom: 20px; color: #555; }
        table { width: 100%; max-width: 400px; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 10px 14px; text-align: {$textAlign}; }
        th { background: #1E3A5F; color: #fff; font-weight: bold; }
        td:last-child { text-align: right; }
        .footer { margin-top: 24px; font-size: 9px; color: #888; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <div class="meta">
        <p><strong>{$companyName}</strong></p>
        <p><strong>{$periodLabel}</strong></p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="text-align:{$textAlign}">{$metricLabel}</th>
                <th style="text-align:right">{$valueLabel}</th>
            </tr>
        </thead>
        <tbody>{$tableRows}</tbody>
    </table>
    <p class="footer">{$generatedOnLabel}</p>
</body>
</html>
HTML;

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
