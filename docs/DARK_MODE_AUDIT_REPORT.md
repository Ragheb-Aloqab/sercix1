# Sercix1 Fleet Management System — Dark/Light Mode Audit Report

**Date:** March 2026  
**Scope:** Full application audit for dark/light mode consistency, hover states, typography, and theme toggle behavior.

---

## 1. Summary

Dark and light mode are now applied consistently across the Sercix1 Fleet Management System. Theme preference is persisted for all user types (guests, drivers, company users, maintenance centers, web users). Hover states use dark/black accents in light mode and red accents in dark mode where appropriate. Typography and contrast have been improved for readability in both modes.

---

## 2. Components Updated

### 2.1 Shared Components

| Component | Path | Changes |
|----------|------|---------|
| **Modal** | `resources/views/components/modal.blade.php` | Dark backdrop (`dark:bg-black/70`), dark content (`dark:bg-slate-800`), `transition-colors duration-300` |
| **Dropdown** | `resources/views/components/dropdown.blade.php` | Default `contentClasses` with `dark:bg-slate-800`, `dark:ring-white/10` |
| **DropdownLink** | `resources/views/components/dropdown-link.blade.php` | `dark:text-slate-300`, `dark:hover:bg-slate-700/50`, `dark:hover:text-white`, `transition-colors duration-300` |
| **TextInput** | `resources/views/components/text-input.blade.php` | Full dark mode: border, bg, text, placeholder, focus ring |
| **NavLink** | `resources/views/components/nav-link.blade.php` | Dark variants for active/inactive, `transition-colors duration-300` |
| **ResponsiveNavLink** | `resources/views/components/responsive-nav-link.blade.php` | Dark variants for active/inactive states |
| **CompanyAlert** | `resources/views/components/company-alert.blade.php` | Light mode text contrast: `text-red-700`, `text-emerald-700`, etc. for readability |
| **PrimaryButton** | `resources/views/components/primary-button.blade.php` | Dark mode bg/hover, `transition-colors duration-300` |
| **DangerButton** | `resources/views/components/danger-button.blade.php` | Light mode `hover:bg-red-700`, dark mode `hover:bg-red-500`, `transition-colors duration-300` |
| **CompanyTable** | `resources/views/components/company/table.blade.php` | `bg-white dark:bg-slate-800/40`, border variants |
| **CompanyGlass** | `resources/views/components/company/glass.blade.php` | Title block: `bg-white dark:bg-servx-black-card`, light/dark text |
| **company-glass (CSS)** | `resources/css/app.css` | Light gradient default; dark gradient in `.dark` |
| **ThemeToggleVanilla** | `resources/views/components/theme-toggle-vanilla.blade.php` | Light/dark styling for use on both light and dark backgrounds |

### 2.2 Layouts

| Layout | Path | Changes |
|--------|------|---------|
| **App (Profile)** | `resources/views/layouts/app.blade.php` | `x-theme-init`, body `dark:bg-slate-900`, header dark variants, `@livewireStyles`/`@livewireScripts`, theme listener |
| **Navigation** | `resources/views/livewire/layout/navigation.blade.php` | `dark:bg-slate-800`, borders, logo, dropdown trigger, hamburger, user info; added `ThemeToggleStandalone` |
| **Unified Login** | `resources/views/auth/unified-login.blade.php` | `x-theme-init`, full light/dark variants for body, card, inputs, alerts, links; `ThemeToggleStandalone`; `@livewireStyles`/`@livewireScripts` |
| **Maintenance Center Login** | `resources/views/maintenance-center/auth/login.blade.php` | `x-theme-init`, light/dark variants, `ThemeToggleVanilla` |

### 2.3 Livewire Components

| Component | Path | Changes |
|----------|------|---------|
| **VehicleMileageReports** | `resources/views/livewire/company/vehicle-mileage-reports.blade.php` | Back link, filters (labels, inputs, selects), table header/rows, status badges, sort buttons, PDF/Excel buttons; light mode typography and hover |
| **MaintenanceInvoicesSection** | `resources/views/livewire/company/maintenance-invoices-section.blade.php` | Table rows, PDF icon contrast, download link hover |
| **VehiclesList** | `resources/views/livewire/company/vehicles-list.blade.php` | Search/filters, table header/rows, status badges, action buttons; full light mode support |
| **Reports index** | `resources/views/company/reports/index.blade.php` | Date filters, report cards (maintenance, fuel, mileage) |
| **Invoices table** | `company/invoices/partials/invoices-table.blade.php` | Table container, header, rows, action buttons |
| **Maintenance invoices table** | `company/invoices/partials/maintenance-invoices-table.blade.php` | Full light mode |
| **Company fuel invoices table** | `company/invoices/partials/company-fuel-invoices-table.blade.php` | Full light mode |
| **Vehicles mileage** | `company/vehicles/mileage.blade.php` | Back link, cards, tables |
| **Dashboard index** | `company/dashboard/index.blade.php` | Mobile grid, alert links, servx-inner CSS overrides |
| **Maintenance invoices index** | `company/maintenance-invoices/index.blade.php` | Table, status badges, action buttons |
| **InvoicesList** | `livewire/company/invoices-list.blade.php` | Filter inputs, section heading |
| **MaintenanceInvoicesSection** | `livewire/company/maintenance-invoices-section.blade.php` | Upload modal, form inputs |
| **FuelInvoiceUploadSection** | `livewire/company/fuel-invoice-upload-section.blade.php` | Upload modal, form labels/inputs/select/cancel button |
| **Insurances index** | `company/insurances/index.blade.php` | Coming soon card |
| **Glass start** | `company/partials/glass-start.blade.php` | Title block light mode |
| **CompanyGlass** | `components/company-glass.blade.php` | Title block light mode (bg-white, text-slate-900) |
| **CompanyFilterField** | `components/company-filter-field.blade.php` | Labels, select, input with light/dark variants |

### 2.4 Driver Views

| View | Path | Changes |
|------|------|---------|
| **Tracking** | `driver/tracking.blade.php` | Back link, modals (end odometer, daily odometer), dash-card, tracking status text |
| **Dashboard** | `driver/dashboard.blade.php` | Service cards text, requests list, status badges |
| **History** | `driver/history.blade.php` | Request list items, status badges, pagination border |
| **Maintenance request show** | `driver/maintenance-request/show.blade.php` | Labels, values, image borders, back link |
| **Maintenance request create** | `driver/maintenance-request/create.blade.php` | Form labels, inputs, selects, textareas, cancel link |
| **Inspections index** | `driver/inspections/index.blade.php` | Back link, no-pending card, vehicle list items |
| **Inspections upload** | `driver/inspections/upload.blade.php` | Back link, labels, file inputs, odometer, notes |
| **Fuel refill create** | `driver/fuel-refill-create.blade.php` | Form labels, inputs, selects, textarea, receipt hint, cancel link |
| **Notifications** | `driver/notifications/index.blade.php` | Filter select, notification items, empty state, pagination border |

### 2.5 Maintenance Center Views

| View | Path | Changes |
|------|------|---------|
| **Dashboard** | `maintenance-center/dashboard.blade.php` | Logout button, stat cards, table header/rows, status badges |
| **History** | `maintenance-center/history/index.blade.php` | Filters (labels, inputs, selects), table, reset/back links |
| **RFQ show** | `maintenance-center/rfq/show.blade.php` | Request details, quotation form, invoice form, back link |
| **Auth verify** | `maintenance-center/auth/verify.blade.php` | Theme init, theme toggle, body/card/input light/dark variants |

### 2.6 CSS (style.css)

| Section | Changes |
|---------|---------|
| **Sidebar nav items** | Light mode: `hover` uses `rgba(15,23,42,...)` (dark/black) instead of red; active state uses dark accent in light mode |
| **Sidebar nav icons** | Active icon in light mode: `rgba(15,23,42,0.2)`, `color: rgb(15,23,42)` |
| **dash-card** | Light mode default: `bg-white`, `border-slate`, hover `border-slate`; dark mode retains original design |
| **dash-card-title** | Light: `color: rgb(100,116,139)`; dark: `var(--servx-silver)` |
| **dash-card-value** | Light: `color: rgb(15,23,42)`; dark: `var(--servx-white)` |
| **dash-section-title** | Light: `color: rgb(15,23,42)`; dark: `var(--servx-silver-light)` |
| **dash-card-kpi hover** | Light: `border-color: rgba(15,23,42,0.2)`; dark: `rgba(59,130,246,0.25)` |

---

## 3. Theme Toggle Behavior

| Location | Component | Persistence |
|----------|-----------|-------------|
| Admin/Company/Maintenance dashboard | `UiPreferences` (in sidebar/topbar) | DB (users/companies/maintenance_centers) + session |
| Auth layout (verify, password) | `ThemeToggleStandalone` | Session + localStorage |
| Unified login | `ThemeToggleStandalone` | Session + localStorage |
| Maintenance center login | `ThemeToggleVanilla` | Session + localStorage (via `/theme-preference` route) |
| Driver layout | `ThemeToggleVanilla` | Session + localStorage (via `/theme-preference` route) |
| Profile (layouts.app) | `ThemeToggleStandalone` in nav | DB/session + localStorage |

---

## 4. Hover State Improvements

- **Light mode:** Red hovers replaced with black/dark (`hover:text-slate-900`, `hover:bg-slate-100`, `rgba(15,23,42,...)`) for links, table rows, cards, sidebar items.
- **Dark mode:** Red/brand accents retained where appropriate (sidebar, servx-red).
- **Transitions:** `transition-colors duration-300` applied across updated components.
- **PDF/Excel buttons:** Red kept for PDF (brand); light mode hover `hover:bg-red-700` for better contrast.

---

## 5. Icon Colors (Theme-Aware)

Icons now use light/dark color variants for visibility in both modes:

- **Colored icons:** `text-sky-600 dark:text-sky-400`, `text-emerald-600 dark:text-emerald-400`, `text-amber-600 dark:text-amber-400`, `text-red-600 dark:text-red-400`
- **Driver dashboard:** Service card icons (maintenance, fuel, camera, tracking)
- **Company dashboard:** Alert icons, document expiry, inspection, announcements, fuel, bullhorn, arrow links
- **Driver layout:** Header, sidebar nav, bottom tab bar, menu dropdown, toast icons
- **Upload sections:** Cloud-arrow-up, circle-check, template icons
- **Insurances:** Shield icon

---

## 6. Typography & Readability

- **Alerts:** `text-red-700`, `text-emerald-700`, `text-amber-800`, `text-sky-700` in light mode for sufficient contrast on light backgrounds.
- **Status badges:** `text-emerald-700`, `text-amber-800`, `text-red-700` in light mode.
- **Table text:** `text-slate-900`/`text-slate-600` in light mode; `text-white`/`text-servx-silver` in dark mode.
- **Labels/placeholders:** `text-slate-600`, `placeholder-slate-400` in light mode.

---

## 6. Theme Persistence

- **Logged-in users (company, maintenance_center, web):** Stored in `theme_preference` column (users/companies/maintenance_centers tables).
- **Drivers (session-based):** Stored in session via `POST /theme-preference`.
- **Guests:** Stored in `localStorage` key `sercix_theme`.
- **Theme init:** `ThemeInit` component runs in `<head>` to prevent flash; uses server preference when actor exists, otherwise localStorage + system preference.

---

## 7. Pages/Components Not Modified

- **Print layout** (`admin/layouts/print.blade.php`): Intentionally light-only for print.
- **Index/landing** (`index.blade.php`): Dark-only by design; can be extended later if needed.
- **Admin CRUD pages** (users, inventory, technicians, etc.): Use admin layout which already has dark mode; individual page cards/tables inherit from shared components where updated.
- **Maintenance center verify:** Uses `layouts.auth` which has theme support.

---

## 8. Testing Checklist

- [ ] Toggle theme on unified login → persists on next visit
- [ ] Toggle theme on maintenance center login → persists
- [ ] Toggle theme on driver dashboard → persists across pages
- [ ] Toggle theme on company dashboard → persists, sidebar/cards/table reflect mode
- [ ] Toggle theme on admin dashboard → same as company
- [ ] Toggle theme on profile page (layouts.app) → persists
- [ ] Hover table rows in light mode → dark hover, no red
- [ ] Hover sidebar items in light mode → dark accent
- [ ] Alerts (success, error, warning) readable in both modes
- [ ] Modal/dropdown/text-input usable in both modes

---

## 9. Files Modified (Summary)

**Views:** modal, dropdown, dropdown-link, text-input, nav-link, responsive-nav-link, company-alert, primary-button, danger-button, company/table, theme-toggle-vanilla, layouts/app, livewire/layout/navigation, auth/unified-login, maintenance-center/auth/login, livewire/company/vehicle-mileage-reports, livewire/company/maintenance-invoices-section

**CSS:** style.css (sidebar, dash-card, dash-card-title, dash-card-value, dash-section-title, dash-card-kpi)

**Routes:** web.php (theme-preference route already present from prior work)
