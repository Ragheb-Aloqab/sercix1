<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MileageReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        public array $monthlyReport,
    ) {}

    public function collection(): Collection
    {
        return collect($this->monthlyReport);
    }

    public function headings(): array
    {
        return [
            __('vehicles.monthly_mileage'),
            __('common.km'),
            __('vehicles.estimated_market_cost') . ' (SAR)',
            __('vehicles.avg_market_operating_cost') . ' (SAR/km)',
        ];
    }

    public function map($row): array
    {
        return [
            $row['month_label'] ?? '',
            number_format($row['total_monthly_mileage_km'] ?? 0, 1),
            number_format($row['estimated_market_cost_sar'] ?? 0, 2),
            number_format($row['average_market_operating_cost_sar'] ?? 0.37, 2),
        ];
    }
}
