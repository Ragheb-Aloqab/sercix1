<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleMileageReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        public array $rows,
    ) {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return [
            __('fleet.plate_number'),
            __('vehicles.vehicle_name'),
            __('vehicles.current_mileage'),
            __('vehicles.previous_mileage'),
            __('vehicles.total_distance'),
            __('vehicles.last_update_date'),
            __('vehicles.status'),
        ];
    }

    public function map($row): array
    {
        return [
            $row['plate_number'] ?? '-',
            $row['vehicle_name'] ?? '-',
            number_format($row['current_mileage'] ?? 0, 1),
            number_format($row['previous_mileage'] ?? 0, 1),
            number_format($row['total_distance'] ?? 0, 1),
            $row['last_update_date'] ?? '-',
            $row['status_label'] ?? $row['status'] ?? '-',
        ];
    }
}
