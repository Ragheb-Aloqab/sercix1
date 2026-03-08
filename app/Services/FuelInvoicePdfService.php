<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Illuminate\Support\Facades\Storage;

/**
 * Generate fuel invoice PDF in the same style as maintenance invoice (CompanyMaintenanceInvoicePdfService).
 * Shows: title, invoice#/date/vehicle, fuel row (liters, cost), total, optional receipt image.
 */
class FuelInvoicePdfService
{
    public function getPdfContent(Invoice $invoice): string
    {
        return $this->buildPdf($invoice)->output();
    }

    /**
     * Generate and save PDF to storage, return path (for observer / generate flow).
     */
    public function generate(Invoice $invoice): string
    {
        $pdf = $this->buildPdf($invoice);
        $path = 'invoices/' . $invoice->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        $invoice->update(['pdf_path' => $path]);
        return $path;
    }

    private function buildPdf(Invoice $invoice)
    {
        $invoice->loadMissing(['company', 'fuelRefill.vehicle', 'fuelRefill.company']);
        $fr = $invoice->fuelRefill;
        if (!$fr) {
            throw new \RuntimeException('Fuel invoice must have a fuel refill.');
        }

        $company = $invoice->company ?? $fr->company;
        $companyName = $company ? ($company->company_name ?? __('common.company')) : __('common.company');
        $locale = app()->getLocale();
        $isRtl = $locale === 'ar';

        $title = $this->e($companyName) . ' — ' . ($isRtl ? __('invoice.fuel_invoice') : 'Fuel Invoice');
        $invoiceRef = $invoice->invoice_number ?? 'INV-F-' . $invoice->id;
        $dateStr = $invoice->created_at ? Carbon::parse($invoice->created_at)->format('Y-m-d H:i') : '—';
        $refillAtStr = $fr->refilled_at ? Carbon::parse($fr->refilled_at)->format('Y-m-d H:i') : '—';
        $vehicleStr = $fr->vehicle
            ? $this->e($fr->vehicle->plate_number . ' — ' . trim(($fr->vehicle->make ?? '') . ' ' . ($fr->vehicle->model ?? '')))
            : '—';

        $fuelTypeLabel = __('fuel.' . ($fr->fuel_type ?? 'petrol')) ?: ucfirst($fr->fuel_type ?? 'petrol');
        $serviceDesc = $fuelTypeLabel;
        if ($fr->liters !== null) {
            $serviceDesc .= ' — ' . number_format($fr->liters, 1) . ' ' . ($isRtl ? __('fuel.quantity') : 'L');
        }
        $costStr = $fr->cost !== null ? number_format($fr->cost, 2) . ' ' . $this->e(__('company.sar')) : '—';
        $total = (float) ($fr->cost ?? $invoice->subtotal ?? 0);

        $serviceRow = '<tr><td>' . $this->e($serviceDesc) . '</td><td style="text-align:right">' . $costStr . '</td></tr>';
        $totalRow = '<tr><td><strong>' . $this->e(__('maintenance.total') ?: 'Total') . '</strong></td><td style="text-align:right"><strong>' . number_format($total, 2) . ' ' . $this->e(__('company.sar')) . '</strong></td></tr>';

        $dir = $isRtl ? 'rtl' : 'ltr';
        $lang = $isRtl ? 'ar' : 'en';
        $textAlign = $isRtl ? 'right' : 'left';

        $receiptImgHtml = '';
        if ($fr->receipt_path && Storage::disk('public')->exists($fr->receipt_path)) {
            $fullPath = storage_path('app/public/' . $fr->receipt_path);
            $fullPath = str_replace('\\', '/', realpath($fullPath));
            if ($fullPath) {
                $receiptImgHtml = '<div style="margin-top:20px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0;"><p><strong>' . $this->e(__('invoice.uploaded_invoice') ?: 'Uploaded invoice') . ':</strong></p><img src="' . $fullPath . '" alt="" style="max-width:100%; max-height:220px;" /></div>';
            }
        }

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
<p><strong>' . $this->e(__('fuel.vehicle') ?: 'Vehicle') . ':</strong> ' . $vehicleStr . ' &nbsp;|&nbsp; <strong>' . $this->e(__('fuel.refilled_at') ?: 'Refill time') . ':</strong> ' . $this->e($refillAtStr) . '</p>
</div>
<table>
<thead><tr>
<th style="text-align:' . $textAlign . '; padding:8px 10px">' . $this->e(__('maintenance.services') ?: 'Services') . '</th>
<th style="text-align:right; padding:8px 10px">' . $this->e(__('maintenance.price') ?: 'Price') . ' (' . $this->e(__('company.sar')) . ')</th>
</tr></thead>
<tbody>
' . $serviceRow . '
' . $totalRow . '
</tbody>
</table>
' . $receiptImgHtml . '
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
        return $pdf;
    }

    private function e(?string $s): string
    {
        return $s !== null ? htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8') : '';
    }
}
