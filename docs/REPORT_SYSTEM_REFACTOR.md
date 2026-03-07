# Fleet Management Report System Refactor

## Summary

This document describes the comprehensive refactoring of the fleet management reporting system to achieve production-ready, scalable architecture.

---

## 1. Unified Export Architecture

### ReportExportService (`app/Services/Report/ReportExportService.php`)

Single entry point for all report exports:

```php
$service = app(ReportExportService::class);
$response = $service->export($type, $filters, $format);
// type: tax | comprehensive | mileage | mileage_vehicles | vehicle
// format: pdf | excel
```

### Report Types

| Type | Description |
|------|-------------|
| `tax` | Tax reports (VAT, maintenance invoices) |
| `comprehensive` | Maintenance + fuel + mileage summary |
| `mileage` | Monthly mileage summary |
| `mileage_vehicles` | Per-vehicle mileage report |
| `vehicle` | Per-vehicle fuel + maintenance report |

### Filters from Request

```php
$filters = $service->filtersFromRequest($request, ReportExportService::TYPE_TAX);
$response = $service->export(ReportExportService::TYPE_TAX, $filters, ReportExportService::FORMAT_PDF);
```

---

## 2. Eliminated Code Duplication

### VehicleReportDataProvider (`app/Services/Report/VehicleReportDataProvider.php`)

- **Before:** Vehicle report data was fetched separately in `VehicleReportPdfService` and `VehicleReportExport` (duplicated queries for FuelRefill, CompanyFuelInvoice, MaintenanceRequest, CompanyMaintenanceInvoice).
- **After:** Single data provider used by both PDF and Excel exports. One source of truth.

### VehicleReportExport & VehicleReportPdfService

Both now delegate to `VehicleReportDataProvider` for data fetching.

---

## 3. Fuel Cost Consistency

### CompanyAnalyticsService

- `fuelsCost()` now includes **CompanyFuelInvoice** (company-uploaded fuel invoices) in addition to FuelRefill.
- `getFuelCostsSummary()` includes both sources.
- `computeFuelCostByMonth()` includes both sources.
- `getTopVehiclesByServiceConsumptionAndCost()` includes both fuel refills and fuel invoices.

### MarketComparisonService

- `getCompanyFuelTotal()` now includes **CompanyFuelInvoice** for accurate market comparison.

### VehicleAnalyticsService

- `getVehicleCostForPeriod()` now includes **CompanyFuelInvoice** for vehicle-level fuel cost.

---

## 4. Performance Optimizations

### VehicleAnalyticsService

- `getVehicleMaintenanceCost()`: Replaced `get()->sum()` with `selectRaw('COALESCE(SUM(...))')` to avoid loading all rows.
- `getVehicleCostForPeriod()`: Same optimization for MaintenanceRequest; added CompanyFuelInvoice to fuel total.

### Caching

- `InvalidateCompanyAnalyticsCache::forVehicle()` now also clears `vehicle_chart_{id}_6` and `vehicle_chart_{id}_12`.
- `InvalidateCompanyAnalyticsCache::forCompany()` now clears `market_monthly_{id}_6` and `market_monthly_{id}_12`.

---

## 5. Data Integrity & Validation

### PreventDuplicateMaintenanceInvoice (`app/Rules/PreventDuplicateMaintenanceInvoice.php`)

- Prevents duplicate maintenance invoice entries: same vehicle + same amount within 24 hours.
- Applied in `MaintenanceInvoicesSection` Livewire component before create.
- Translations: `maintenance.duplicate_invoice_warning` (en/ar).

### Odometer Validation

- Existing `OdometerTrackingService::validateOdometerReading()` prevents invalid odometer values (negative, less than previous).
- `VehicleMileageReportService` already handles negative distance anomalies (data_anomaly status).

---

## 6. Arabic / RTL Support

### VehicleReportPdfService

- Uses **Mpdf** (Laravel Mpdf) for Arabic when `app()->getLocale() === 'ar'`.
- Sets `dir="rtl"`, `lang="ar"`, `SetDirectionality('rtl')`, and `xbriyaz` font.
- Falls back to DomPDF for English.

---

## 7. Analytics Enhancements

### VehicleAnalyticsService

- Added **cost_per_km** metric: `total_cost / total_mileage` (fleet efficiency indicator).
- Exposed on vehicle show page.

### Vehicle Show Page

- Added fourth card: **Cost per km** (SAR/km).

---

## 8. Reusable UI Components

### ReportStatCard (`resources/views/components/report-stat-card.blade.php`)

- Reusable stat card for report summary sections.
- Props: `label`, `value`, `icon`, `iconColor` (sky, amber, emerald, red).
- Used in Tax Report view.

---

## 9. Contracts (Interfaces)

- `App\Contracts\ReportDataProviderInterface` – Data providers for reports.
- `App\Contracts\PdfReportGeneratorInterface` – PDF generators (for future extensibility).
- `App\Contracts\ExcelReportGeneratorInterface` – Excel generators (for future extensibility).

---

## 10. File Structure

```
app/
├── Contracts/
│   ├── ReportDataProviderInterface.php
│   ├── PdfReportGeneratorInterface.php
│   └── ExcelReportGeneratorInterface.php
├── Rules/
│   └── PreventDuplicateMaintenanceInvoice.php
├── Services/
│   └── Report/
│       ├── ReportExportService.php
│       └── VehicleReportDataProvider.php
resources/views/components/
├── report-stat-card.blade.php
app/View/Components/
└── ReportStatCard.php
```

---

## Migration Notes

- **Existing controllers** (TaxReportController, ComprehensiveReportController, etc.) continue to work unchanged.
- **ReportExportService** is optional; controllers can migrate to it for consistency.
- **VehicleReportController** and **GenerateVehicleReportJob** use the refactored VehicleReportExport and VehicleReportPdfService; no API changes.

---

## Future Extensibility

To add a new report type:

1. Create a `ReportDataProvider` implementing `ReportDataProviderInterface`.
2. Add PDF/Excel generation logic (or reuse existing services).
3. Register the type in `ReportExportService::exportPdf()` and `exportExcel()` match expressions.
4. Add `filtersFromRequest()` case for the new type.
