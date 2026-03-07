# Servx — Project Documentation

Full documentation of the **Servx** fleet and vehicle management platform: structure, features, and technical overview.

---

## 1. Project Overview

**Servx** is a Laravel-based **fleet and vehicle management** system with:

- **Multi-tenant white-label subdomains** — Companies can have their own subdomain (e.g. `alfnar.servx.test`) with custom branding (logo, colors, name).
- **Four user types** — Admin/Super Admin (web), Company, Driver (session/OTP), Maintenance Center (OTP).
- **Unified login** — Single `/login` entry point that identifies the user type (email/phone) and routes to the correct auth flow (password, OTP, 2FA).
- **Company dashboard** — Fleet overview, vehicles, orders, fuel, reports, maintenance requests (RFQ), inspections, tracking, invoices, settings.
- **Driver portal** — Odometer, fuel refills, maintenance requests, tracking, inspections, notifications.
- **Maintenance Center portal** — RFQ (quotations, invoices), history.
- **Admin panel** — Customers (companies), orders, vehicles, services, announcements, settings, reports.

The same codebase serves both the main app domain and company subdomains; tenant resolution and branding are handled by middleware and view composers.

---

## 2. Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Blade, Livewire 3, Volt, Vite |
| Auth | Laravel session guards (web, company, maintenance_center), OTP, optional 2FA (admin) |
| PDF | DomPDF, Laravel mPDF |
| Excel | Maatwebsite Excel |
| Permissions | Spatie Laravel Permission |
| Barcode | picqer/php-barcode-generator |
| Payments | Tap (optional, config-driven) |

---

## 3. Directory Structure

```
sercix1/
├── app/
│   ├── Console/                 # Artisan commands
│   ├── Events/                  # Domain events (InvoiceCreated, OrderStatusChanged, etc.)
│   ├── Exceptions/              # Exception handler
│   ├── Exports/                 # Excel exports (reports, mileage, tax, comprehensive)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Admin: customers, orders, services, settings, etc.
│   │   │   ├── Api/V1/          # API (auth, vehicles, invoices)
│   │   │   ├── Auth/            # Unified login, OTP verify
│   │   │   ├── Company/        # Company dashboard (dashboard, vehicles, fuel, reports, maintenance, tracking, settings)
│   │   │   ├── Driver/         # Driver-specific (notifications, maintenance request)
│   │   │   ├── MaintenanceCenter/     # MC dashboard, RFQ, history
│   │   │   ├── MaintenanceCenterAuth/
│   │   │   ├── CompanyAuth/
│   │   │   └── ...             # Index, Locale, Robots, Sitemap, Tap, etc.
│   │   ├── Middleware/         # Auth guards, subdomain, CSRF, locale, theme, etc.
│   │   └── Requests/           # Form requests (e.g. Admin Customers)
│   ├── Jobs/                   # Queued jobs (PDF generation, reports)
│   ├── Listeners/              # Event listeners (cache invalidation, notifications)
│   ├── Livewire/               # Livewire components (dashboard, company, admin, settings)
│   ├── Models/                 # Eloquent models + Scopes (TenantScope), Concerns (BelongsToCompany)
│   ├── Notifications/          # Report ready, etc.
│   ├── Observers/              # Model observers (Company, Invoice, Order, Vehicle, etc.)
│   ├── Providers/              # AppServiceProvider (view composers, branding)
│   ├── Rules/                  # Custom validation rules
│   ├── Services/               # Business logic (reports, analytics, OTP, theme, tenant, etc.)
│   └── View/Components/        # Blade components (ThemeInit, ReportStatCard)
├── bootstrap/
│   └── app.php                 # Routing (web, api), middleware registration
├── config/
│   ├── app.php
│   ├── auth.php                # Guards: web, company, maintenance_center
│   ├── servx.php               # App-specific: payments, white_label_domain, market rates, etc.
│   ├── seo.php
│   └── ...
├── database/
│   ├── migrations/             # Tables: companies, vehicles, orders, maintenance_requests, etc.
│   ├── seeders/
│   └── factories/
├── public/
├── resources/
│   ├── css/
│   │   └── style.css           # Global + tenant branding (data-wl-branding)
│   ├── js/
│   │   └── app.js
│   ├── lang/                   # en, ar (admin_dashboard, common, errors, maintenance, etc.)
│   └── views/
│       ├── admin/              # Admin layout, customers, orders, overview, settings
│       ├── auth/                # Unified login, OTP verify, password
│       ├── company/             # Company dashboard, vehicles, reports, maintenance, auth
│       ├── driver/              # Driver dashboard, tracking, inspections, notifications
│       ├── layouts/             # app, driver, auth, admin (app.blade)
│       ├── livewire/            # Dashboard (sidebar, topbar), company, admin Livewire views
│       ├── maintenance-center/
│       ├── components/         # Reusable Blade components
│       ├── errors/
│       └── index.blade.php     # Landing/marketing page
├── routes/
│   ├── web.php                 # Main web routes (index, login, driver, maintenance-center, company auth)
│   ├── admin.php               # Admin routes (prefix admin, middleware auth:web + admin)
│   ├── company.php             # Company routes (prefix company, middleware company)
│   ├── auth.php                # Breeze/auth-related if any
│   ├── api/v1.php              # API routes
│   └── channels.php            # Broadcasting
├── storage/
├── tests/
├── .env / .env.example
├── composer.json
├── package.json
└── vite.config.js
```

---

## 4. Authentication & User Types

| Type | Guard | Provider | Login flow | Entry |
|------|--------|----------|------------|--------|
| Admin / Super Admin | `web` | `users` | Email + password, optional 2FA | `/login` → redirect to `/admin/dashboard` |
| Company | `company` | `companies` | Email + OTP (unified login) | `/login` or `{subdomain}.domain` |
| Driver | (session by phone) | — | Phone + OTP | `/login` → driver flow |
| Maintenance Center | `maintenance_center` | `maintenance_centers` | Phone/email + OTP | `/login` |

- **Unified login** (`Auth\UnifiedLoginController`): single form; user enters email/phone → system identifies type → redirects to password or OTP.
- **Driver** has no Eloquent “User”; identity is stored in session (e.g. `driver_phone`, driver-linked company/vehicle context).
- **Admin 2FA**: optional two-factor; middleware `EnsureAdmin2FA` and related logic in login flow.

---

## 5. White-Label Subdomain

- **Config**: `config/servx.php` → `white_label_domain` (env: `WHITE_LABEL_DOMAIN`, default `servx.sa`).
- **Middleware**: `LoadCompanyFromSubdomain` (in `bootstrap/app.php` web stack):
  - Reads request host (e.g. `alfnar.servx.test`).
  - If host ends with `white_label_domain`, extracts subdomain; reserved names (`www`, `app`, `admin`, `api`, etc.) are ignored.
  - Looks up `Company` by `subdomain` + `white_label_enabled` + `status = active`; binds `tenant` and `company`; sets `tenant_from_subdomain = true`.
  - If logged-in company does not match tenant, logs out and returns 403.
- **Branding**: When `tenant_from_subdomain` is true, `AppServiceProvider` view composer sets `siteName`, `siteLogoUrl`, `brandTitle`, `brandTitleDriver` from the tenant and shares `wlBranding`. Views and CSS use `data-wl-branding` and tenant CSS variables for logo, colors, and footer.
- **Routes**: No separate route file for subdomains. Same routes as main app; middleware decides tenant from host.

Details: `app/Http/Middleware/LoadCompanyFromSubdomain.php`, `app/Providers/AppServiceProvider.php` (view composer), `app/Services/TenantThemeHelper.php`, `resources/css/style.css` (tenant variables).

---

## 6. Routes Overview

| File | Prefix | Middleware | Purpose |
|------|--------|------------|---------|
| `web.php` | — | web | `/` (index), `/login`, `/dashboard` redirect, driver, maintenance-center, company auth (OTP), theme, Tap redirect |
| `admin.php` | `admin` | auth:web, admin | Dashboard, companies, vehicles, orders, services, customers, settings, announcements, exports |
| `company.php` | `company` | company | Dashboard, vehicles, fuel, orders, invoices, reports, maintenance requests/offers/invoices, tracking, branches, inspections, notifications, settings |
| `auth.php` | — | — | Breeze/auth if used |
| `api/v1.php` | `api` | — | API auth, vehicles, invoices |

- **Index** (`/`): `IndexController`; on subdomain passes tenant name/logo and uses tenant branding.
- **Login**: `UnifiedLoginController` → identify → password or OTP → verify → redirect by guard.
- **Dashboard redirect** (`/dashboard`): Chooses company / driver / maintenance_center / admin dashboard by guard/session.

---

## 7. Main Application Areas

### 7.1 Admin (`/admin`)

- **Access**: `auth:web` + `admin` middleware (role admin/super_admin).
- **Features**: Super dashboard (companies, vehicles), customer (company) CRUD, orders, services, vehicle document expiry, announcements, notifications, activity, settings (bank, OTP, branding), data export.
- **Controllers**: Under `Http/Controllers/Admin/` (CustomersController, Orders, Services, Settings, etc.).
- **Views**: `resources/views/admin/` (layouts, customers, overview, partials/topbar).
- **Livewire**: Admin dashboard (SuperDashboard, CompaniesList, CompanyDetails, OrdersList, OrderShow, etc.).

### 7.2 Company (`/company`)

- **Access**: `company` middleware (company guard).
- **Features**: Dashboard, vehicles (CRUD, documents, inspections, tracking, mileage, reports), fuel (refills, balance, invoices), orders, invoices, maintenance requests (RFQ), maintenance offers, maintenance invoices (upload/view), reports (mileage, tax, comprehensive, service), branches, tracking map, notifications, settings (profile, branding, invoice, notifications, OTP).
- **Controllers**: `Http/Controllers/Company/` (DashboardController, VehiclesController, FuelController, ReportsController, MaintenanceRequestController, etc.).
- **Views**: `resources/views/company/` (dashboard, vehicles, fuel, reports, maintenance-invoices, auth).
- **Livewire**: Company dashboard (Sidebar, VehiclesList, OrderShow, MaintenanceInvoicesSection, VehicleMileageReports, Settings, etc.).

### 7.3 Driver

- **Access**: Session-based (phone + OTP); no company guard; driver routes under `driver` prefix.
- **Features**: Dashboard, history, fuel refill, maintenance request, order request (create/start/invoice), tracking (start/stop/status/report), daily odometer, inspections (request/upload), notifications.
- **Controllers**: `DriverController`, `Driver/MaintenanceRequestController`, `DriverInspectionController`, `Driver/NotificationsController`.
- **Views**: `resources/views/driver/`; layout `layouts/driver.blade.php`.
- **Routes**: In `web.php` under `Route::prefix('driver')`.

### 7.4 Maintenance Center

- **Access**: `maintenance_center` guard (OTP login).
- **Features**: Dashboard, RFQ (view request, submit quotation, upload invoice), history.
- **Controllers**: `MaintenanceCenter/DashboardController`, `RfqController`, `HistoryController`, `MaintenanceCenterAuth/OtpAuthController`.
- **Views**: `resources/views/maintenance-center/`.
- **Routes**: In `web.php` under `Route::prefix('maintenance-center')`.

---

## 8. Models (Summary)

| Model | Purpose |
|-------|---------|
| `User` | Admin/super_admin (web guard); roles, 2FA. |
| `Company` | Tenant/customer; subdomain, white_label, logo, branches, vehicles, orders, maintenance requests. |
| `Vehicle` | Belongs to company; fuel refills, locations, inspections, documents, tracking. |
| `Order` | Company order; services, status, invoices, attachments. |
| `Invoice` | Order-related invoice. |
| `FuelRefill`, `CompanyFuelInvoice` | Fuel data and company-uploaded fuel invoices. |
| `MaintenanceRequest` | RFQ workflow; status, quotations, approved center, attachments. |
| `Quotation`, `RfqAssignment` | Maintenance Center quotations and assignments. |
| `MaintenanceCenter` | Maintenance center auth and RFQ. |
| `CompanyMaintenanceInvoice` | Company-uploaded maintenance invoices. |
| `VehicleInspection`, `VehicleInspectionPhoto` | Inspections and photos. |
| `VehicleDailyOdometer`, `VehicleMileageHistory`, `VehicleMonthlyMileage` | Odometer and mileage. |
| `VehicleLocation`, `MobileTrackingTrip` | Tracking. |
| `ReportExport` | Async report generation (PDF/Excel). |
| `Setting` | Global settings (site name, logo, etc.). |
| `Notification`, `DriverNotification` | Notifications. |
| `Announcement`, `AnnouncementRead` | Admin announcements. |
| Plus: `Attachment`, `OrderService`, `CompanyBranch`, `Service`, `CompanyService`, `Payment`, `BankAccount`, `OtpVerification`, `LoginAudit`, `WebhookUrl`, etc. |

**Tenant scoping**: Many models use `BelongsToCompany` and global `TenantScope` so company-scoped data is filtered by the current tenant when bound.

---

## 9. Services (Summary)

| Service | Purpose |
|--------|---------|
| `TenantThemeHelper` | Resolve tenant primary/secondary colors for branding. |
| `TenantSecurityLogger` | Log invalid subdomain access, tenant mismatch. |
| `ThemeService` | User/company theme preference (light/dark). |
| `VehicleMileageService`, `VehicleMileageReportService`, `MileageReportPdfService` | Mileage calculation and reports. |
| `CompanyAnalyticsService`, `VehicleAnalyticsService`, `MarketComparisonService` | Analytics and market comparison. |
| `TaxReportService`, `ComprehensiveReportService`, `ComprehensiveReportPdfService` | Tax and comprehensive reports. |
| `ReportExportService`, `VehicleReportPdfService`, `VehicleReportDataProvider` | Report export and PDF. |
| `MaintenanceRfqService` | RFQ workflow logic. |
| `NotificationService`, `DriverNotificationService` | Notifications. |
| `OtpService`, `AdminOtpService` | OTP sending/verification. |
| `TapService` | Tap payment integration. |
| `VehicleTrackingApiService`, `OdometerTrackingService`, `DailyOdometerSnapshotService` | Tracking and odometer. |
| `ExpiryMonitoringService` | Document/expiry alerts. |
| `InvoicePdfService`, `MaintenanceInvoicePdfService` | PDF generation. |

---

## 10. Configuration (Highlights)

- **`config/servx.php`**: `payments_enabled`, `white_label_domain`, `default_vehicle_quota`, `market_avg_per_km`, `market_fuel_per_km`, `invoice_max_size_mb`, `stuck_order_days`, `inactive_company_days`, `low_fleet_utilization_threshold`, `default_map_style`.
- **`config/auth.php`**: Guards `web`, `company`, `maintenance_center`; providers `users`, `companies`, `maintenance_centers`.
- **`.env`**: `APP_URL`, `WHITE_LABEL_DOMAIN`, `PAYMENTS_ENABLED`, `DB_*`, queue, mail, etc.

---

## 11. Key Files Quick Reference

| Concern | File(s) |
|--------|--------|
| Subdomain tenant resolution | `app/Http/Middleware/LoadCompanyFromSubdomain.php` |
| Tenant branding (name, logo, theme) | `app/Providers/AppServiceProvider.php` (view composer), `app/View/Components/ThemeInit.php` |
| Tenant colors / CSS | `app/Services/TenantThemeHelper.php`, `resources/css/style.css` (data-wl-branding) |
| Unified login | `app/Http/Controllers/Auth/UnifiedLoginController.php`, `resources/views/auth/unified-login.blade.php` |
| Landing page | `app/Http/Controllers/IndexController.php`, `resources/views/index.blade.php` |
| Company dashboard layout | `resources/views/admin/layouts/app.blade.php`, `resources/views/admin/partials/topbar.blade.php`, `resources/views/livewire/dashboard/sidebar.blade.php` |
| Driver layout | `resources/views/layouts/driver.blade.php` |
| Tenant scope | `app\Models\Scopes\TenantScope.php`, `app\Models\Concerns\BelongsToCompany.php` |

---

## 12. Getting Started (Brief)

1. Clone repo, `composer install`, copy `.env.example` to `.env`, set `APP_KEY`, `DB_*`, `WHITE_LABEL_DOMAIN` (e.g. `servx.test` for local).
2. `php artisan migrate`, `php artisan storage:link`, seeders if needed.
3. `npm install && npm run dev` (or `npm run build`).
4. Run `php artisan serve` (and queue worker if using queues). For subdomain testing, use a hosts entry and domain like `alfnar.servx.test` pointing to the app.
5. Create a company with `subdomain` and `white_label_enabled` in DB or via Admin → Customers to use white-label subdomain.

---

This document describes the full project and structure as of the current codebase. For day-to-day development, refer to the routes, middleware, and the sections above for each area (Admin, Company, Driver, Maintenance Center) and white-label behavior.
