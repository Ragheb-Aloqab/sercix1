# Enterprise Refactoring — Sercix1 Fleet Management System

**Date:** March 4, 2026

---

## 1. Files Created

### Domain Events
| File | Purpose |
|------|---------|
| `app/Events/VehicleCreated.php` | Fired when a vehicle is created |
| `app/Events/PaymentPaid.php` | Fired when a payment is marked paid |
| `app/Events/MaintenanceRequestApproved.php` | Fired when maintenance invoice is approved |
| `app/Events/OrderStatusChanged.php` | Fired when order status changes (cache invalidation) |
| `app/Events/MaintenanceRequestCreated.php` | Fired when maintenance request is created |
| `app/Events/InvoiceCreated.php` | Fired when invoice is created |

### Event Listeners
| File | Purpose |
|------|---------|
| `app/Listeners/LogVehicleCreated.php` | Activity logging for vehicle creation |
| `app/Listeners/UpdateInvoiceOnPaymentPaid.php` | Updates invoice paid_amount when payment received |
| `app/Listeners/NotifyPaymentPaid.php` | Sends PaymentPaidNotification to admin, company, driver |
| `app/Listeners/NotifyMaintenanceRequestApproved.php` | Notifies center and driver when invoice approved |
| `app/Listeners/InvalidateCacheOnOrderStatusChanged.php` | Invalidates company analytics cache |
| `app/Listeners/InvalidateCacheOnMaintenanceRequestCreated.php` | Invalidates company analytics cache |
| `app/Listeners/InvalidateCacheOnInvoiceCreated.php` | Invalidates company analytics cache |

### Queue Jobs
| File | Purpose |
|------|---------|
| `app/Jobs/GenerateMileageReportJob.php` | Queued mileage report PDF/Excel generation with notification |
| `app/Jobs/GenerateInvoicePdfJob.php` | Queued invoice PDF generation with notification |
| `app/Jobs/GenerateVehicleReportJob.php` | Queued vehicle report PDF/Excel generation with notification |

### API Controllers
| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/V1/AuthController.php` | Company API login/logout (Sanctum tokens) |
| `app/Http/Controllers/Api/V1/VehicleController.php` | GET /api/v1/vehicles |
| `app/Http/Controllers/Api/V1/InvoiceController.php` | GET /api/v1/invoices |

### Models & Migrations
| File | Purpose |
|------|---------|
| `app/Models/ReportExport.php` | Tracks generated report exports for download |
| `database/migrations/2026_03_04_200000_create_report_exports_table.php` | Report exports table |
| `database/migrations/2026_03_04_210000_add_performance_indexes.php` | Indexes on company_id, vehicle_id, created_at |
| `database/migrations/2026_03_04_220000_add_notifications_read_at_index.php` | Index on notifications(read_at) |

### Services
| File | Purpose |
|------|---------|
| `app/Services/NotificationService.php` | Centralized notification service (unread count, mark read, etc.) |

### Notifications
| File | Purpose |
|------|---------|
| `app/Notifications/ReportReadyNotification.php` | Notifies when queued report is ready for download |

### API & Broadcasting
| File | Purpose |
|------|---------|
| `routes/api/v1.php` | API v1 routes (health, auth, vehicles, invoices) |
| `routes/channels.php` | Broadcast channels for Company and User notifications |
| `resources/views/components/echo-setup.blade.php` | Pusher/Echo setup for real-time notification bell |

---

## 2. Files Modified

| File | Changes |
|------|---------|
| `app/Providers/AppServiceProvider.php` | Registered domain event listeners |
| `app/Http/Controllers/Company/VehiclesController.php` | Dispatch VehicleCreated |
| `app/Http/Controllers/TapWebhookController.php` | Dispatch PaymentPaid instead of inline notify |
| `app/Livewire/Admin/BankTransferReview.php` | Dispatch PaymentPaid |
| `app/Services/MaintenanceRfqService.php` | Dispatch MaintenanceRequestApproved instead of inline notify |
| `app/Http/Controllers/Company/NotificationsController.php` | Use NotificationService |
| `bootstrap/app.php` | Added API routes (api/v1.php) |
| `lang/en/reports.php` | Added report_ready, report_ready_message, mileage_report |

---

## 3. Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           HTTP / API Request                                      │
└─────────────────────────────────────────────────────────────────────────────────┘
                                        │
                    ┌───────────────────┴───────────────────┐
                    ▼                                       ▼
┌───────────────────────────────┐           ┌───────────────────────────────────────┐
│  Web Routes (company, admin)   │           │  API Routes /api/v1/                   │
│  - Sync PDF/Excel (immediate)  │           │  - Health check                       │
│  - Queue option for heavy       │           │  - Future: vehicles, invoices, etc.   │
└───────────────────────────────┘           └───────────────────────────────────────┘
                    │                                       │
                    ▼                                       ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  Controllers / Livewire                                                            │
│  - Dispatch Domain Events (VehicleCreated, PaymentPaid, MaintenanceRequestApproved)│
│  - Dispatch Queue Jobs (GenerateMileageReportJob)                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
                    │
        ┌───────────┼───────────┐
        ▼           ▼           ▼
┌──────────────┐ ┌──────────────┐ ┌────────────────────────────────────────────────┐
│  Domain      │ │  Queue Jobs   │ │  NotificationService                            │
│  Events      │ │  - Retryable  │ │  - getUnreadCount()                              │
│  - Listeners │ │  - Log fail   │ │  - markAsRead()                                  │
│  - Side effects│ │  - Notify    │ │  - getNotifications()                            │
│  - No core   │ │    on complete│ │  - Prepared for Pusher/WebSockets                 │
└──────────────┘ └──────────────┘ └────────────────────────────────────────────────┘
```

---

## 4. Performance Improvements

| Area | Improvement |
|------|-------------|
| **Database** | Indexes on `company_id`, `vehicle_id`, `created_at` for orders, invoices, vehicles, fuel_refills, maintenance_requests, payments |
| **Domain Events** | Side effects (notifications, logging) moved out of core logic; decoupled |
| **Queue Jobs** | `GenerateMileageReportJob` for heavy report generation; async with notification on completion |
| **NotificationService** | Centralized unread count, mark read; reusable for future real-time |

---

## 5. Domain Event Flow

| Event | Trigger | Listeners |
|-------|---------|-----------|
| **VehicleCreated** | `Vehicle::create()` in VehiclesController | LogVehicleCreated |
| **PaymentPaid** | Tap webhook, Bank transfer approval | UpdateInvoiceOnPaymentPaid, NotifyPaymentPaid |
| **MaintenanceRequestApproved** | approveInvoice() in MaintenanceRfqService | NotifyMaintenanceRequestApproved |

---

## 6. Queue Job Usage

```php
// Dispatch mileage report for background generation
GenerateMileageReportJob::dispatch(
    $company,
    'pdf', // or 'excel'
    $from,
    $to,
    $vehicleId,
    $branchId,
    $months
);
// User receives ReportReadyNotification when done; download link in notification
```

---

## 7. API Versioning

- **Base URL:** `/api/v1/`
- **Health:** `GET /api/v1/health`
- **Future:** Company, Driver, Admin API endpoints under auth middleware

---

## 8. Phase 3 — Next-Level Improvements (Completed)

| Area | Implementation |
|------|----------------|
| **Queues** | `GenerateInvoicePdfJob`, `GenerateVehicleReportJob`; `queue=1` on invoice/vehicle report routes |
| **Events** | `OrderStatusChanged`, `MaintenanceRequestCreated`, `InvoiceCreated` with cache invalidation listeners |
| **Caching** | `InvalidateCompanyAnalyticsCache` forgets `company_*_last_seven_months`, `fuel_by_month`, `top_vehicles` |
| **APIs** | Company API: `POST /api/v1/auth/login`, `GET /api/v1/vehicles`, `GET /api/v1/invoices` with Sanctum Bearer tokens |
| **Notifications** | ReportReadyNotification broadcasts when Pusher configured; Echo setup in `x-echo-setup`; `#[On('notification-received')]` in NotificationsBell |
| **Indexes** | `notifications(read_at)` index for unread filtering |

### Pusher Setup

Set in `.env`:
```
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=...
PUSHER_APP_KEY=...
PUSHER_APP_SECRET=...
PUSHER_APP_CLUSTER=mt1
```

### API Usage

```bash
# Login
curl -X POST /api/v1/auth/login -d '{"email":"...","password":"..."}'
# Returns: {"token":"...","token_type":"Bearer","company":{...}}

# List vehicles (Bearer token)
curl -H "Authorization: Bearer <token>" /api/v1/vehicles?per_page=15&search=...

# List invoices
curl -H "Authorization: Bearer <token>" /api/v1/invoices?invoice_type=service&from=2026-01-01
```

---

## 9. Future Recommendations

1. **Repository Pattern** — Consider if query complexity grows
2. **API v2** — Add `X-API-Deprecated` header when v2 is released
3. **Dedicated Queues** — Use `reports`, `notifications` queues for better scaling
