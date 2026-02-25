<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ServicesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Service::query()->orderBy('id');
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Description', 'Base Price', 'Duration Minutes', 'Is Active', 'Created At', 'Updated At'];
    }

    public function map($service): array
    {
        return [
            $service->id,
            $service->name ?? '',
            $service->description ?? '',
            $service->base_price ?? 0,
            $service->duration_minutes ?? 0,
            $service->is_active ? 'Yes' : 'No',
            $service->created_at?->format('Y-m-d H:i:s') ?? '',
            $service->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
