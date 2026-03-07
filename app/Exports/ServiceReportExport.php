<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class ServiceReportExport implements FromArray, WithEvents, WithTitle
{
    private Collection $allItems;

    private array $totals;

    private array $analytics;

    private array $byServiceType;

    private string $locale;

    public function __construct(
        Collection $allItems,
        array $totals,
        array $analytics,
        array $byServiceType,
        ?string $locale = null
    ) {
        $this->allItems = $allItems;
        $this->totals = $totals;
        $this->analytics = $analytics;
        $this->byServiceType = $byServiceType;
        $this->locale = $locale ?? app()->getLocale();
    }

    public function array(): array
    {
        $locale = $this->locale;
        $originalLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            $rows = [];
            $rows[] = [];
            $rows[] = [];

            $headerRow = [
                __('fuel.date'),
                '#',
                __('fuel.vehicle'),
                __('reports.services'),
                __('company.cost') . ' (' . __('company.sar') . ')',
                __('orders.status_label'),
                __('maintenance.invoice') ?: 'Invoice',
            ];
            $rows[] = $headerRow;

            foreach ($this->allItems as $row) {
                $vehicle = $row->order?->vehicle ?? $row->maintenanceRequest?->vehicle ?? $row->companyMaintenanceInvoice?->vehicle ?? null;
                $vehicleStr = $vehicle
                    ? ($vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')))
                    : '—';
                $ref = $row->type === 'order'
                    ? (string) $row->order->id
                    : ($row->type === 'company_maintenance_invoice' ? 'CMI-' . $row->companyMaintenanceInvoice->id : 'MR-' . $row->maintenanceRequest->id);
                $serviceStr = $row->serviceName . (($row->orderServicesCount ?? 0) > 1 ? ' +' . ($row->orderServicesCount - 1) : '');
                $rows[] = [
                    $row->date?->format('Y-m-d H:i') ?? '—',
                    $ref,
                    $vehicleStr,
                    $serviceStr,
                    (float) $row->amount,
                    $row->statusLabel,
                    $row->invoiceDisplay ?? '—',
                ];
            }

            $rows[] = [];
            $rows[] = [__('reports.total_service_cost'), number_format($this->totals['total_cost'] ?? 0, 2) . ' ' . __('company.sar'), '', '', '', '', ''];
            $rows[] = [__('reports.order_count'), (string) ($this->totals['order_count'] ?? 0), '', '', '', '', ''];

            return $rows;
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function registerEvents(): array
    {
        $isRtl = $this->locale === 'ar';
        $headerRow = 3;
        $dataRowCount = $this->allItems->count();
        $lastDataRow = $headerRow + $dataRowCount;
        $summaryStartRow = $lastDataRow + 2;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($isRtl, $headerRow, $lastDataRow, $summaryStartRow) {
                $sheet = $event->sheet->getDelegate();

                if ($isRtl) {
                    $sheet->setRightToLeft(true);
                    $sheet->getParentOrThrow()->getDefaultStyle()->getFont()->setName('Arial');
                } else {
                    $sheet->getParentOrThrow()->getDefaultStyle()->getFont()->setName('Arial');
                }

                $headerRange = 'A' . $headerRow . ':G' . $headerRow;
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1E3A5F'],
                    ],
                    'alignment' => [
                        'horizontal' => $isRtl ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF94A3B8'],
                        ],
                    ],
                ]);

                if ($lastDataRow > $headerRow) {
                    $dataRange = 'A' . ($headerRow + 1) . ':G' . $lastDataRow;
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFCBD5E1'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                            'horizontal' => $isRtl ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_LEFT,
                        ],
                    ]);
                    $sheet->getStyle('E' . ($headerRow + 1) . ':E' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $summaryRange = 'A' . $summaryStartRow . ':B' . ($summaryStartRow + 1);
                $sheet->getStyle($summaryRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => [
                        'horizontal' => $isRtl ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCBD5E1'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(28);
                $sheet->getColumnDimension('D')->setWidth(32);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(14);
                $sheet->getRowDimension($headerRow)->setRowHeight(24);
            },
        ];
    }

    public function title(): string
    {
        $original = app()->getLocale();
        app()->setLocale($this->locale);
        try {
            return (string) (__('reports.service_report') ?: 'Service Report');
        } finally {
            app()->setLocale($original);
        }
    }
}
