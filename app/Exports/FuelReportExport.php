<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FuelReportExport implements FromArray, WithEvents, WithTitle
{
    private Collection $rows;

    private float $totalCost;

    private float $totalLiters;

    private int $refillCount;

    private string $locale;

    public function __construct(
        Collection $rows,
        float $totalCost,
        float $totalLiters,
        int $refillCount,
        ?string $locale = null
    ) {
        $this->rows = $rows;
        $this->totalCost = $totalCost;
        $this->totalLiters = $totalLiters;
        $this->refillCount = $refillCount;
        $this->locale = $locale ?? app()->getLocale();
    }

    public function array(): array
    {
        $locale = $this->locale;
        $originalLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            $out = [];
            $out[] = [];
            $out[] = [];

            $headerRow = [
                __('fuel.date'),
                __('fuel.vehicle'),
                __('fuel.quantity'),
                __('company.cost') . ' (' . __('company.sar') . ')',
                __('fuel.odometer'),
                __('fuel.source'),
                __('fuel.invoice'),
            ];
            $out[] = $headerRow;

            foreach ($this->rows as $row) {
                if ($row->type === 'refill') {
                    $fr = $row->refill;
                    $vehicle = $fr->vehicle;
                    $vehicleStr = $vehicle
                        ? ($vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')))
                        : '—';
                    $liters = (float) ($fr->liters ?? 0);
                    $cost = (float) ($fr->cost ?? 0);
                    $odometer = $fr->odometer_km ? number_format($fr->odometer_km) . ' ' . __('common.km') : '—';
                    $source = $fr->isFromExternalProvider() ? ($fr->provider ?? '—') : __('fuel.manual');
                    $invoiceDisplay = $fr->invoice ? __('common.yes') : ($fr->receipt_path ? __('fuel.receipt') : '—');
                } else {
                    $inv = $row->invoice;
                    $vehicle = $inv->vehicle;
                    $vehicleStr = $vehicle
                        ? ($vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')))
                        : '—';
                    $liters = '—';
                    $cost = (float) ($inv->amount ?? 0);
                    $odometer = '—';
                    $source = __('fuel.company_upload');
                    $invoiceDisplay = !empty($inv->invoice_file) ? __('common.yes') : '—';
                }

                $out[] = [
                    $row->date?->format('Y-m-d H:i') ?? '—',
                    $vehicleStr,
                    $liters,
                    $cost,
                    $odometer,
                    $source,
                    $invoiceDisplay,
                ];
            }

            $out[] = [];
            $out[] = [__('fuel.total_fuel_cost'), number_format($this->totalCost, 2) . ' ' . __('company.sar'), '', '', '', '', ''];
            $out[] = [__('fuel.total_liters'), number_format($this->totalLiters, 1), '', '', '', '', ''];
            $out[] = [__('fuel.refill_count'), (string) $this->refillCount, '', '', '', '', ''];

            return $out;
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function registerEvents(): array
    {
        $isRtl = $this->locale === 'ar';
        $headerRow = 3;
        $dataRowCount = $this->rows->count();
        $lastDataRow = $headerRow + $dataRowCount;
        $summaryStartRow = $lastDataRow + 2;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($isRtl, $headerRow, $lastDataRow, $summaryStartRow) {
                $sheet = $event->sheet->getDelegate();

                if ($isRtl) {
                    $sheet->setRightToLeft(true);
                }
                $sheet->getParentOrThrow()->getDefaultStyle()->getFont()->setName('Arial');

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
                    $sheet->getStyle('D' . ($headerRow + 1) . ':D' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $summaryEndRow = $summaryStartRow + 2;
                $summaryRange = 'A' . $summaryStartRow . ':B' . $summaryEndRow;
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
                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(14);
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
            return (string) (__('fuel.title') ?: 'Fuel Report');
        } finally {
            app()->setLocale($original);
        }
    }
}
