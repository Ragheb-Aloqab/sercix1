<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TaxReportExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private readonly array $data
    ) {}

    public function array(): array
    {
        $rows = [
            [__('reports.total_invoices'), number_format($this->data['total_invoices'] ?? 0, 0)],
            [__('reports.total_vat_amount'), number_format($this->data['total_vat_amount'] ?? 0, 2) . ' ' . __('company.sar')],
            [__('reports.total_including_vat'), number_format($this->data['total_including_vat'] ?? 0, 2) . ' ' . __('company.sar')],
        ];

        $rows[] = [];
        $rows[] = [__('reports.date'), __('company.vehicle'), __('maintenance.invoice_amount'), __('maintenance.vat_amount'), __('maintenance.total_with_tax')];

        foreach ($this->data['invoices'] ?? [] as $inv) {
            $vehicleStr = $inv->vehicle
                ? ($inv->vehicle->plate_number . ' — ' . trim(($inv->vehicle->make ?? '') . ' ' . ($inv->vehicle->model ?? '')))
                : '—';
            $rows[] = [
                $inv->created_at?->format('Y-m-d H:i'),
                $vehicleStr,
                (float) ($inv->original_amount ?? $inv->amount ?? 0),
                (float) ($inv->vat_amount ?? 0),
                (float) ($inv->amount ?? 0),
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return __('reports.tax_reports');
    }
}
