# Fleet Management System — Architecture Upgrade

**Date:** March 4, 2026  
**Project:** Sercix1 (Servx Motors)

---

## 1. Files Created

### Services & Queries
| File | Purpose |
|------|---------|
| `app/Services/CompanyAnalyticsService.php` | Extracted analytics from Company model; caching; DTO-style `getAnalytics()` |
| `app/Modules/Vehicles/Services/VehicleQueryService.php` | Vehicle query logic; used by VehiclesList Livewire |
| `app/Helpers/FlashHelper.php` | Flash message helpers (success, error, warning, info) |
| `app/Providers/FlashServiceProvider.php` | Redirect macros: `withFlash()`, `withSuccess()`, `withError()` |

### Livewire Components
| File | Purpose |
|------|---------|
| `app/Livewire/Company/VehiclesList.php` | Full Livewire vehicles index: reactive search, status/branch filters, pagination, queryString |

### Blade Components
| File | Purpose |
|------|---------|
| `resources/views/components/company/glass.blade.php` | Replaces `glass-start`/`glass-end` |
| `resources/views/components/company/table.blade.php` | Reusable table wrapper with header slot |
| `resources/views/components/company/flash.blade.php` | Unified flash display (supports `flash` + legacy `success`/`error`/`info`) |
| `app/View/Components/Company/Glass.php` | View component for x-company.glass |

### View Partials (existing, now used by Livewire)
| File | Purpose |
|------|---------|
| `resources/views/livewire/company/vehicles-list.blade.php` | Vehicles list Livewire view |

---

## 2. Files Removed

| File | Reason |
|------|--------|
| `app/Services/Queries/VehicleQueryService.php` | Moved to `app/Modules/Vehicles/Services/VehicleQueryService.php` |

---

## 3. Files Modified

### Controllers (thin)
- `InvoicesController::index()` — 3 lines, delegates to Livewire
- `VehiclesController::index()` — 3 lines, delegates to Livewire
- `VehiclesController::store()` / `update()` — use `->withFlash('success', ...)` 

### Models
- `Company` — Analytics methods delegate to `CompanyAnalyticsService`; backward compatible

### Views
- `company/vehicles/index.blade.php` — Uses `<x-company.glass>` + `<livewire:company.vehicles-list />`
- `company/invoices/index.blade.php` — Uses `<x-company.glass>`
- `company/reports/index.blade.php` — Uses `<x-company.glass>`

### Config
- `bootstrap/providers.php` — Added `FlashServiceProvider`

### Lang
- `lang/en/vehicles.php`, `lang/ar/vehicles.php` — Added `all_statuses`

---

## 4. Architectural Diagram (Text)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           HTTP Request                                   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Controller (thin, <20 lines)                                            │
│  - InvoicesController::index() → view with Livewire                      │
│  - VehiclesController::index() → view with Livewire                      │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┴───────────────┐
                    ▼                               ▼
┌───────────────────────────────┐   ┌─────────────────────────────────────┐
│  Livewire Components          │   │  Blade Components                     │
│  - InvoicesList               │   │  - x-company.glass                     │
│  - VehiclesList               │   │  - x-company.table                    │
│  - FuelInvoiceUploadSection   │   │  - x-company.flash                     │
│  - VehicleMileageReports       │   │  - x-company-alert                    │
└───────────────────────────────┘   │  - x-summary-card                     │
                    │               └─────────────────────────────────────┘
                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Services (business logic, testable)                                     │
│  - CompanyAnalyticsService (caching, DTO)                                │
│  - InvoiceSummaryService                                                 │
│  - VehicleQueryService (Modules/Vehicles)                                │
│  - VehicleMileageService, ExpiryMonitoringService, etc.                   │
└─────────────────────────────────────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Models (Eloquent, relationships only)                                   │
│  - Company (delegates analytics to service)                              │
│  - Vehicle, Invoice, Order, etc.                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 5. Performance Improvements

| Area | Before | After |
|------|--------|-------|
| Company analytics | Raw SQL in model, no cache | `CompanyAnalyticsService` with `Cache::remember(600)` for `lastSevenMonthsComparison`, `fuelCostByMonth`, `getTopVehiclesByServiceConsumptionAndCost` |
| Vehicles index | Full page reload on filter | Livewire partial updates, debounced search (300ms) |
| Invoices index | Full page reload | Livewire (from previous refactor) |
| N+1 | Possible in various queries | Eager loading: `order.invoice`, `branch:id,name` |

---

## 6. Before/After Comparison

### Vehicles Index Controller
**Before:** ~35 lines, query logic, service calls, view data preparation  
**After:** 3 lines — `return view('company.vehicles.index');`

### Company Model
**Before:** ~200 lines of analytics SQL and aggregation  
**After:** ~50 lines — thin delegation to `CompanyAnalyticsService`

### Flash Messages
**Before:** `->with('success', $msg)` / `->with('error', $msg)` — inconsistent keys  
**After:** `->withFlash('success', $msg)` — standardized; `x-company.flash` supports both new and legacy

---

## 7. Modular Structure (Introduced)

```
app/Modules/
└── Vehicles/
    └── Services/
        └── VehicleQueryService.php
```

**Future expansion:**
```
app/Modules/
├── Vehicles/
│   ├── Controllers/
│   ├── Livewire/
│   ├── Services/
│   ├── Requests/
│   └── Policies/
├── Invoices/
├── Maintenance/
├── Fuel/
└── Company/
```

---

## 8. Enterprise Refactoring (Phase 2) — Completed

See **ENTERPRISE_REFACTORING.md** for full details. Summary:

- **Queues** — `GenerateMileageReportJob` for PDF/Excel; `queue=1` param; `ReportReadyNotification` on completion.
- **Domain Events** — `VehicleCreated`, `PaymentPaid`, `MaintenanceRequestApproved` with listeners for logging, invoice updates, notifications.
- **API Versioning** — `routes/api/v1.php` loaded at `/api/v1/`; health endpoint; structure for future APIs.
- **Database Indexes** — Migration `add_performance_indexes` on orders, invoices, vehicles, fuel_refills, maintenance_requests, payments.
- **NotificationService** — Centralized `getUnreadCount`, `markAsRead`, `getNotifications`; company sidebar badge; `NotificationsController` refactored.
- **Report Download** — `company.reports.download` route; `ReportsController::downloadExport()` for queued reports.

---

## 9. Suggested Next Enterprise Improvements

1. **Invoice PDF Job** — `GenerateInvoicePdfJob` for async invoice PDF downloads.
2. **Vehicle Report Job** — Queue vehicle report PDF/Excel exports.
3. **Caching** — Extend `CompanyAnalyticsService` cache keys with date ranges; add cache invalidation on write.
4. **Pusher/WebSockets** — Integrate real-time notification bell via Laravel Echo.
5. **API Auth** — Implement Company/Driver API authentication (Sanctum tokens).
