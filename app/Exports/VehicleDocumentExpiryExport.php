<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VehicleDocumentExpiryExport implements FromCollection, WithHeadings
{
    public function __construct(
        public Collection $items
    ) {}

    public function collection(): Collection
    {
        return $this->items->map(function ($i) {
            return [
                $i->vehicle->id,
                $i->vehicle->plate_number ?? '',
                trim(($i->vehicle->make ?? '') . ' ' . ($i->vehicle->model ?? '')),
                $i->vehicle->company?->company_name ?? '',
                $i->type === \App\Services\ExpiryMonitoringService::DOC_REGISTRATION ? __('vehicles.registration') : __('vehicles.insurance'),
                __('vehicles.' . $i->status),
                $i->date?->format('Y-m-d') ?? '',
                $i->days_remaining ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Vehicle ID', 'Plate', 'Make/Model', 'Company', 'Document Type', 'Status', 'Expiry Date', 'Days Remaining',
        ];
    }
}
