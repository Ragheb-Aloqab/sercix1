<?php

namespace App\Exports;

use App\Models\Vehicle;
use App\Models\FuelRefill;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        public Vehicle $vehicle,
        public string $type, // fuel, maintenance, all
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}

    public function collection(): Collection
    {
        $rows = collect();

        if (in_array($this->type, ['fuel', 'all'])) {
            $q = FuelRefill::where('vehicle_id', $this->vehicle->id);
            if ($this->dateFrom) {
                $q->where('refilled_at', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $q->where('refilled_at', '<=', $this->dateTo . ' 23:59:59');
            }
            foreach ($q->orderBy('refilled_at')->get() as $fr) {
                $rows->push((object) [
                    'date' => $fr->refilled_at?->format('Y-m-d H:i'),
                    'type' => 'fuel',
                    'description' => $fr->liters . ' L @ ' . ($fr->price_per_liter ?? 0) . ' SAR',
                    'cost' => (float) $fr->cost,
                ]);
            }
        }

        if (in_array($this->type, ['maintenance', 'all'])) {
            $q = MaintenanceRequest::where('vehicle_id', $this->vehicle->id)
                ->where(function ($mq) {
                    $mq->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
                });
            if ($this->dateFrom) {
                $q->where('created_at', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $q->where('created_at', '<=', $this->dateTo . ' 23:59:59');
            }
            foreach ($q->orderBy('created_at')->get() as $mr) {
                $cost = (float) ($mr->final_invoice_amount ?? $mr->approved_quote_amount ?? 0);
                $rows->push((object) [
                    'date' => $mr->created_at?->format('Y-m-d H:i'),
                    'type' => 'maintenance',
                    'description' => 'Request #' . $mr->id . ' - ' . ($mr->maintenance_type ?? ''),
                    'cost' => $cost,
                ]);
            }
        }

        return $rows->sortBy('date')->values();
    }

    public function headings(): array
    {
        return [__('fuel.date'), __('vehicles.report_type'), __('vehicles.description'), __('company.cost') . ' (SAR)'];
    }

    public function map($row): array
    {
        return [
            $row->date,
            $row->type === 'fuel' ? __('company.fuel') : __('company.maintenance'),
            $row->description,
            number_format($row->cost, 2),
        ];
    }
}
