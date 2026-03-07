<?php

namespace App\Exports;

use App\Models\Vehicle;
use App\Services\Report\VehicleReportDataProvider;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleReportExport implements FromCollection, WithHeadings, WithMapping
{
    private Collection $rows;

    public function __construct(
        public Vehicle $vehicle,
        public string $type, // fuel, maintenance, all
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {
        $provider = app(VehicleReportDataProvider::class);
        $data = $provider->getData([
            'vehicle' => $vehicle,
            'type' => $type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
        $this->rows = collect($data['rows'])->map(fn ($r) => (object) $r);
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [__('fuel.date'), __('vehicles.report_type'), __('vehicles.description'), __('company.cost') . ' (SAR)'];
    }

    public function map($row): array
    {
        return [
            $row->date ?? '',
            $row->type === 'fuel' ? __('company.fuel') : __('company.maintenance'),
            $row->description ?? '',
            number_format((float) ($row->cost ?? 0), 2),
        ];
    }
}
