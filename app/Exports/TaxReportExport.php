<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TaxReportExport implements FromArray, WithEvents, WithTitle
{
    private readonly array $data;
    private string $locale;

    public function __construct(array $data, ?string $locale = null)
    {
        $this->data = $data;
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
            $rows[] = []; // Spacer before table

            // Column order: Vehicle (car details on top) | Invoice Amount | VAT (15%) | Total Amount | Selected Services | Date
            $headerRow = [
                __('company.vehicle'),
                __('maintenance.invoice_amount'),
                __('maintenance.vat_amount') . ' (15%)',
                __('maintenance.total_with_tax'),
                __('maintenance.services'),
                __('reports.date'),
            ];
            $rows[] = $headerRow;

            // Each invoice: Row 1 = car details (vehicle) on TOP, Row 2 = other details (amounts, services, date) UNDER
            foreach ($this->data['invoices'] ?? [] as $inv) {
                $vehicleStr = $inv->vehicle
                    ? ($inv->vehicle->display_name ?? ($inv->vehicle->plate_number . ' — ' . trim(($inv->vehicle->make ?? '') . ' ' . ($inv->vehicle->model ?? ''))))
                    : '—';
                $servicesStr = $inv->services->isNotEmpty()
                    ? $inv->services->pluck('name')->join(', ')
                    : '—';
                // Row 1: Car details (vehicle) — TOP
                $rows[] = [$vehicleStr, '', '', '', '', ''];
                // Row 2: Other details — UNDER the car details
                $rows[] = [
                    '',
                    (float) ($inv->original_amount ?? $inv->amount ?? 0),
                    (float) ($inv->vat_amount ?? 0),
                    (float) ($inv->amount ?? 0),
                    $servicesStr,
                    $inv->created_at?->format('Y-m-d'),
                ];
            }

            // Summary (Total Invoices, etc.) — UNDER the car details
            $rows[] = [];
            $rows[] = [
                __('reports.total_invoices'),
                number_format($this->data['total_invoices'] ?? 0, 0),
                '',
                '',
                '',
                '',
            ];
            $rows[] = [
                __('reports.total_vat_amount'),
                number_format($this->data['total_vat_amount'] ?? 0, 2) . ' ' . __('company.sar'),
                '',
                '',
                '',
                '',
            ];
            $rows[] = [
                __('reports.total_including_vat'),
                number_format($this->data['total_including_vat'] ?? 0, 2) . ' ' . __('company.sar'),
                '',
                '',
                '',
                '',
            ];

            return $rows;
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function registerEvents(): array
    {
        $isRtl = $this->locale === 'ar';
        $invoiceCount = count($this->data['invoices'] ?? []);
        $headerRow = 3; // Row 3 = header (after spacer rows 1-2)
        $lastDataRow = $headerRow + ($invoiceCount * 2); // 2 rows per invoice: car on top, details under
        $summaryStartRow = $lastDataRow + 2; // Summary under car details (after spacer)

        return [
            AfterSheet::class => function (AfterSheet $event) use ($isRtl, $headerRow, $lastDataRow, $summaryStartRow) {
                $sheet = $event->sheet->getDelegate();

                if ($isRtl) {
                    $sheet->setRightToLeft(true);
                    $sheet->getParentOrThrow()->getDefaultStyle()->getFont()->setName('Arial');
                }

                // Summary section (under car details): bold labels, proper alignment, borders
                $summaryRange = 'A' . $summaryStartRow . ':B' . ($summaryStartRow + 2);
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
                $sheet->getStyle('B' . $summaryStartRow . ':B' . ($summaryStartRow + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Header row: bold, colored background, borders
                $headerRange = 'A' . $headerRow . ':F' . $headerRow;
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

                // Data rows: borders, alignment, wrap text for services column
                if ($lastDataRow > $headerRow) {
                    $dataRange = 'A' . ($headerRow + 1) . ':F' . $lastDataRow;
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
                    // Right-align numeric columns (B, C, D) - Invoice Amount, VAT, Total
                    $sheet->getStyle('B' . ($headerRow + 1) . ':D' . $lastDataRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Merge vehicle cell for each invoice: car details on TOP span both rows, details UNDER
                    for ($r = $headerRow + 1; $r < $lastDataRow; $r += 2) {
                        $sheet->mergeCells('A' . $r . ':A' . ($r + 1));
                        $sheet->getStyle('A' . $r)->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF1F5F9'],
                            ],
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'horizontal' => $isRtl ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_LEFT,
                            ],
                        ]);
                    }

                    // Conditional formatting: highlight high-value invoices (total > 5000 SAR)
                    $conditional = (new Conditional())
                        ->setConditionType(Conditional::CONDITION_EXPRESSION)
                        ->setOperatorType(Conditional::OPERATOR_NONE)
                        ->addCondition('=INDIRECT("D"&ROW())>5000');
                    $conditional->getStyle()->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFF3CD'],
                        ],
                    ]);
                    $sheet->getStyle('A' . ($headerRow + 1) . ':F' . $lastDataRow)
                        ->setConditionalStyles([$conditional]);
                }

                // Column widths (auto-size friendly)
                $sheet->getColumnDimension('A')->setWidth(28);
                $sheet->getColumnDimension('B')->setWidth(16);
                $sheet->getColumnDimension('C')->setWidth(14);
                $sheet->getColumnDimension('D')->setWidth(16);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(14);

                $sheet->getRowDimension($headerRow)->setRowHeight(24);
            },
        ];
    }

    public function title(): string
    {
        $locale = $this->locale ?? app()->getLocale();
        $original = app()->getLocale();
        app()->setLocale($locale);
        try {
            return __('reports.tax_reports');
        } finally {
            app()->setLocale($original);
        }
    }
}
