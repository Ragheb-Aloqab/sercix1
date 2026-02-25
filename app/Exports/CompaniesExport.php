<?php

namespace App\Exports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CompaniesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Company::query()
            ->withCount(['vehicles', 'orders'])
            ->orderBy('id');
    }

    public function headings(): array
    {
        return ['ID', 'Company Name', 'Phone', 'Email', 'Status', 'Vehicles Count', 'Orders Count', 'Created At', 'Updated At'];
    }

    public function map($company): array
    {
        return [
            $company->id,
            $company->company_name ?? '',
            $company->phone ?? '',
            $company->email ?? '',
            $company->status ?? '',
            $company->vehicles_count ?? 0,
            $company->orders_count ?? 0,
            $company->created_at?->format('Y-m-d H:i:s') ?? '',
            $company->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
