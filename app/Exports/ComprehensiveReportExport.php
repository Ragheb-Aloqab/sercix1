<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ComprehensiveReportExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private readonly array $data
    ) {}

    public function array(): array
    {
        return [
            [__('reports.total_maintenance_cost'), number_format($this->data['total_maintenance_cost'] ?? 0, 2) . ' ' . __('company.sar')],
            [__('reports.total_fuel_cost'), number_format($this->data['total_fuel_cost'] ?? 0, 2) . ' ' . __('company.sar')],
            [__('reports.monthly_mileage'), number_format($this->data['monthly_mileage'] ?? 0, 2) . ' ' . __('common.km')],
            [__('reports.total_accumulated_mileage'), number_format($this->data['total_accumulated_mileage'] ?? 0, 2) . ' ' . __('common.km')],
        ];
    }

    public function headings(): array
    {
        return [__('reports.metric'), __('reports.value')];
    }

    public function title(): string
    {
        return __('reports.comprehensive_report');
    }
}
