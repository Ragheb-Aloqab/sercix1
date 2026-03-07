<?php

namespace App\Contracts;

/**
 * Contract for PDF report generators.
 * Each report type can have its own PDF generator for proper formatting.
 */
interface PdfReportGeneratorInterface
{
    /**
     * Generate PDF content for the given report data.
     *
     * @param  array<string, mixed>  $data  Report data from ReportDataProvider
     * @param  array<string, mixed>  $options  type-specific options (company, locale, etc.)
     * @return string Raw PDF binary content
     */
    public function generate(array $data, array $options = []): string;

    /**
     * Report type this generator supports.
     */
    public function getType(): string;
}
