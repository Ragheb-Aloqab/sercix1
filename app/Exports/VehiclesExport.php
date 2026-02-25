<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehiclesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public ?int $companyId = null,
        public ?int $branchId = null,
    ) {}

    public function query()
    {
        $query = Vehicle::query()->with('company:id,company_name');

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }
        if ($this->branchId) {
            $query->where('company_branch_id', $this->branchId);
        }

        return $query->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID', 'Company', 'Plate Number', 'Make', 'Model', 'Year', 'Type', 'Color',
            'Driver Name', 'Driver Phone', 'Is Active', 'Created At', 'Updated At',
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->id,
            $vehicle->company?->company_name ?? '',
            $vehicle->plate_number ?? '',
            $vehicle->make ?? '',
            $vehicle->model ?? '',
            $vehicle->year ?? '',
            $vehicle->type ?? '',
            $vehicle->color ?? '',
            $vehicle->driver_name ?? '',
            $vehicle->driver_phone ?? '',
            $vehicle->is_active ? 'Yes' : 'No',
            $vehicle->created_at?->format('Y-m-d H:i:s') ?? '',
            $vehicle->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
