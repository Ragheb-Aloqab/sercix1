<?php

namespace App\Services;

use App\Models\CompanyMaintenanceInvoice;
use Carbon\Carbon;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class CompanyMaintenanceInvoicePdfService
{
    /**
     * Generate a PDF for a company maintenance invoice (no uploaded file).
     * Uses company name as title, lists services and amounts (subtotal, VAT, total).
     * Supports RTL/Arabic via mpdf.
     */
    public function generate(CompanyMaintenanceInvoice $invoice): string
    {
        $invoice->loadMissing(['company', 'vehicle', 'services']);
        $company = $invoice->company;
        $companyName = $company ? ($company->company_name ?? __('common.company')) : __('common.company');
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        $title = $this->e($companyName) . ' — ' . ($isRtl ? __('maintenance.invoice') : 'Invoice');
        $invoiceRef = 'CMI-' . $invoice->id;
        $dateStr = $invoice->created_at ? Carbon::parse($invoice->created_at)->format('Y-m-d H:i') : '—';
        $vehicleStr = $invoice->vehicle
            ? $this->e($invoice->vehicle->plate_number . ' — ' . trim(($invoice->vehicle->make ?? '') . ' ' . ($invoice->vehicle->model ?? '')))
            : '—';

        $serviceRows = '';
        $sumFromPivot = 0.0;
        if ($invoice->services->isNotEmpty()) {
            foreach ($invoice->services as $s) {
                $name = $s->getTranslatedName();
                $price = $s->pivot && $s->pivot->price !== null ? (float) $s->pivot->price : null;
                if ($price !== null) {
                    $sumFromPivot += $price;
                }
                $priceStr = $price !== null ? number_format($price, 2) . ' ' . $this->e(__('company.sar')) : '—';
                $serviceRows .= '<tr><td>' . $this->e($name) . '</td><td style="text-align:right">' . $priceStr . '</td></tr>';
            }
        } else {
            $serviceRows = '<tr><td colspan="2" style="text-align:' . ($isRtl ? 'right' : 'left') . '">' . $this->e(__('invoice.no_services') ?: 'No services') . '</td></tr>';
        }

        $subtotal = $sumFromPivot > 0 ? $sumFromPivot : (float) ($invoice->original_amount ?? $invoice->amount ?? 0);
        $vatAmount = (float) ($invoice->vat_amount ?? 0);
        $total = (float) ($invoice->amount ?? 0);

        $subtotalRow = '<tr><td><strong>' . $this->e(__('maintenance.total_before_tax') ?: 'Subtotal') . '</strong></td><td style="text-align:right"><strong>' . number_format($subtotal, 2) . ' ' . $this->e(__('company.sar')) . '</strong></td></tr>';
        $vatRow = '';
        if ($invoice->hasTax() && $vatAmount > 0) {
            $vatRow = '<tr><td>' . $this->e(__('maintenance.tax_15') ?: 'VAT 15%') . '</td><td style="text-align:right">' . number_format($vatAmount, 2) . ' ' . $this->e(__('company.sar')) . '</td></tr>';
        }
        $totalRow = '<tr><td><strong>' . $this->e(__('maintenance.total') ?: 'Total') . '</strong></td><td style="text-align:right"><strong>' . number_format($total, 2) . ' ' . $this->e(__('company.sar')) . '</strong></td></tr>';

        $dir = $isRtl ? 'rtl' : 'ltr';
        $lang = $isRtl ? 'ar' : 'en';
        $textAlign = $isRtl ? 'right' : 'left';

        $html = '<!DOCTYPE html>
<html dir="' . $dir . '" lang="' . $lang . '">
<head><meta charset="utf-8"><title>' . $title . '</title>
<style>
body { font-size: 11px; padding: 20px; margin: 0; }
h1 { font-size: 18px; margin-bottom: 4px; }
.meta { margin-bottom: 16px; color: #555; }
table { width: 100%; border-collapse: collapse; margin-top: 12px; }
th, td { border: 1px solid #333; padding: 8px 10px; }
th { background: #1E3A5F; color: #fff; font-weight: bold; }
</style>
</head>
<body>
<h1>' . $title . '</h1>
<div class="meta">
<p><strong>' . $this->e(__('maintenance.invoice') ?: 'Invoice') . ':</strong> ' . $this->e($invoiceRef) . ' &nbsp;|&nbsp; <strong>' . $this->e(__('fuel.date') ?: 'Date') . ':</strong> ' . $this->e($dateStr) . '</p>
<p><strong>' . $this->e(__('fuel.vehicle') ?: 'Vehicle') . ':</strong> ' . $vehicleStr . '</p>
</div>
<table>
<thead><tr>
<th style="text-align:' . $textAlign . '; padding:8px 10px">' . $this->e(__('maintenance.services') ?: 'Services') . '</th>
<th style="text-align:right; padding:8px 10px">' . $this->e(__('maintenance.price') ?: 'Price') . ' (' . $this->e(__('company.sar')) . ')</th>
</tr></thead>
<tbody>
' . $serviceRows . '
' . $subtotalRow . '
' . $vatRow . '
' . $totalRow . '
</tbody>
</table>
<p style="margin-top:20px; font-size:9px; color:#888">' . $this->e(__('reports.generated_on', ['date' => Carbon::now()->format('Y-m-d H:i')])) . '</p>
</body>
</html>';

        $config = ['format' => 'A4', 'default_font_size' => 11];
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
