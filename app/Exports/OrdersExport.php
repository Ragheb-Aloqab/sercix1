<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?int $companyId = null,
        public ?int $vehicleId = null,
    ) {}

    public function query()
    {
        $query = Order::query()
            ->with(['company:id,company_name,phone,email', 'vehicle:id,plate_number,make,model', 'orderServices']);

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }
        if ($this->vehicleId) {
            $query->where('vehicle_id', $this->vehicleId);
        }

        return $query->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID', 'Company', 'Phone', 'Email', 'Vehicle', 'Plate', 'Status', 'Total Amount', 'City', 'Address',
            'Requested By', 'Driver Phone', 'Scheduled At', 'Created At', 'Updated At',
        ];
    }

    public function map($order): array
    {
        $totalAmount = (float) ($order->total_amount ?? 0);

        return [
            $order->id,
            $order->company?->company_name ?? '',
            $order->company?->phone ?? '',
            $order->company?->email ?? '',
            trim(($order->vehicle?->make ?? '') . ' ' . ($order->vehicle?->model ?? '')),
            $order->vehicle?->plate_number ?? '',
            $order->status ?? '',
            number_format($totalAmount, 2, '.', ''),
            $order->city ?? '',
            $order->address ?? '',
            $order->requested_by_name ?? '',
            $order->driver_phone ?? '',
            $order->scheduled_at?->format('Y-m-d H:i') ?? '',
            $order->created_at?->format('Y-m-d H:i:s') ?? '',
            $order->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
