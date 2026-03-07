<?php

namespace App\Contracts;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * Contract for Excel report generators.
 * Implementations return a Maatwebsite Excel export object.
 */
interface ExcelReportGeneratorInterface
{
    /**
     * Create an Excel export instance for the given data.
     *
     * @param  array<string, mixed>  $data  Report data from ReportDataProvider
     * @param  array<string, mixed>  $options  type-specific options (locale, etc.)
     * @return FromArray|FromCollection
     */
    public function createExport(array $data, array $options = []): FromArray|FromCollection;

    /**
     * Report type this generator supports.
     */
    public function getType(): string;
}
