<?php

namespace App\Contracts;

/**
 * Contract for report data providers.
 * Each report type implements this to supply data for PDF/Excel export.
 */
interface ReportDataProviderInterface
{
    /**
     * Get report data for the given filters.
     *
     * @param  array<string, mixed>  $filters  type-specific filters (vehicle_id, date_from, date_to, etc.)
     * @return array<string, mixed>
     */
    public function getData(array $filters): array;

    /**
     * Report type identifier (e.g. 'vehicle', 'tax', 'comprehensive', 'mileage').
     */
    public function getType(): string;
}
