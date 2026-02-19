<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorSVG;

class InvoicePdfService
{
    /**
     * Get PDF content as string (for streaming download).
     */
    public function getPdfContent(Invoice $invoice): string
    {
        return $this->buildPdf($invoice)->output();
    }

    /**
     * Generate and save PDF to storage, return path.
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
        $invoice->load([
            'order.services',
            'order.orderServices.service',
            'order.vehicle',
            'order.company',
            'order.payments',
            'order.technician:id,name',
            'order.attachments',
            'fuelRefill.vehicle',
            'fuelRefill.company',
        ]);

        if (!$invoice->order && !$invoice->fuelRefill) {
            throw new \RuntimeException('لا يمكن إنشاء PDF: الفاتورة غير مرتبطة بطلب أو تعبئة وقود.');
        }

        $barcodeData = $invoice->invoice_number ?? 'INV-' . $invoice->id;
        $barcodeHtml = $this->generateBarcodeSvg($barcodeData);

        $total = (float) ($invoice->total ?? 0);
        $paid = (float) ($invoice->order?->payments
            ?->where('status', 'paid')
            ->sum(fn ($p) => (float) $p->amount) ?? 0);
        $remaining = max(0, $total - $paid);

        $company = $invoice->order?->company ?? $invoice->fuelRefill?->company ?? $invoice->company;

        $ordStatus = $invoice->order?->status ?? '';
        $ordStatusLabel = $ordStatus
            ? (\Illuminate\Support\Str::startsWith(__('common.status_' . $ordStatus), 'common.') ? $ordStatus : __('common.status_' . $ordStatus))
            : ($invoice->isFuel() ? __('common.status_completed') : '-');

        $orderItems = collect();
        if ($invoice->order?->orderServices) {
            $orderItems = $invoice->order->orderServices->map(function ($os) {
                $qty = (float) ($os->qty ?? 1);
                $unit = (float) ($os->unit_price ?? $os->total_price ?? 0);
                $rowTotal = (float) ($os->total_price ?? ($qty * $unit));
                $name = $os->display_name ?? $os->custom_service_name ?? $os->service?->name ?? '-';
                return (object) ['qty' => $qty, 'unit' => $unit, 'rowTotal' => $rowTotal, 'name' => $name];
            });
        }

        $invoiceImagePath = null;
        if ($invoice->order_id) {
            $att = $invoice->order->attachments()->where('type', 'driver_invoice')->first();
            $invoiceImagePath = $att?->file_path ? storage_path('app/public/' . $att->file_path) : null;
        } elseif ($invoice->fuel_refill_id && $invoice->fuelRefill?->receipt_path) {
            $invoiceImagePath = storage_path('app/public/' . $invoice->fuelRefill->receipt_path);
        }
        if ($invoiceImagePath && file_exists($invoiceImagePath)) {
            $invoiceImagePath = str_replace('\\', '/', realpath($invoiceImagePath));
        } else {
            $invoiceImagePath = null;
        }

        $config = [
            'format' => 'A4',
            'default_font' => 'xbriyaz',
            'default_font_size' => 12,
        ];

        $invoiceSettings = $this->getInvoiceSettings();

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'barcodeHtml' => $barcodeHtml,
            'barcodeData' => $barcodeData,
            'total' => $total,
            'paidAmount' => $paid,
            'remainingAmount' => $remaining,
            'invoiceSettings' => $invoiceSettings,
            'company' => $company,
            'ordStatusLabel' => $ordStatusLabel,
            'orderItems' => $orderItems,
            'invoiceImagePath' => $invoiceImagePath,
        ])->render();

        $html = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $html);

        $pdf = PDF::loadHTML($html, $config);

        $pdf->getMpdf()->SetDirectionality('rtl');

        return $pdf;
    }

    private function getInvoiceSettings(): array
    {
        $logoPath = Setting::get('site_logo_path');
        $logoFullPath = null;
        if ($logoPath) {
            $full = storage_path('app/public/' . $logoPath);
            if (file_exists($full)) {
                $logoFullPath = str_replace('\\', '/', realpath($full));
            }
        }

        return [
            'company_name' => Setting::get('invoice_company_name', '') ?: Setting::get('site_name', ''),
            'phone' => Setting::get('invoice_phone', ''),
            'tax_number' => Setting::get('invoice_tax_number', ''),
            'address' => Setting::get('invoice_address', ''),
            'email' => Setting::get('invoice_email', ''),
            'website' => Setting::get('invoice_website', ''),
            'notes' => Setting::get('invoice_notes', ''),
            'logo_path' => $logoFullPath,
        ];
    }

    private function generateBarcodeSvg(string $data): string
    {
        $generator = new BarcodeGeneratorSVG();
        return $generator->getBarcode($data, $generator::TYPE_CODE_128, 2, 50, 'black');
    }
}
