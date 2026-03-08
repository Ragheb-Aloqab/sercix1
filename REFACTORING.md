# Laravel & Livewire Refactoring – Maintenance Guide

This document describes the refactoring and optimization work done on the project for **maintainability**, **consistency**, and **future-proofing**. It complements `REFACTORING_SUMMARY.md` (earlier refactor: PhoneHelper, Vehicle scopes, routes, card component).

---

## 1. Project Structure (Conventions)

| Purpose | Location |
|--------|----------|
| **Livewire components** | `app/Livewire/` (Admin, Company, Dashboard, Layout, Tech, Forms, Actions) |
| **Blade layouts** | `resources/views/layouts/` (app, auth, driver) and `resources/views/admin/layouts/` (app, print) |
| **Reusable Blade components** | `resources/views/components/` and `resources/views/components/company/` |
| **Livewire views** | `resources/views/livewire/` (mirrors app/Livewire structure) |
| **JS/CSS assets** | `resources/js/`, `resources/css/` – built via **Vite** and included with `@vite()` |

---

## 2. Layouts – Livewire & Vite

### Livewire

- **`@livewireStyles`** and **`@livewireScripts`** must be present in any layout that renders a page containing `<livewire:...>` components.
- **Layouts that include both:**
  - `resources/views/layouts/app.blade.php`
  - `resources/views/layouts/auth.blade.php`
  - `resources/views/admin/layouts/app.blade.php`
  - `resources/views/auth/unified-login.blade.php`
  - **`resources/views/layouts/driver.blade.php`** – added in this refactor so driver pages can use Livewire in the future without layout changes.

### Vite

- **Standard bundle:** `resources/css/app.css`, `resources/css/style.css`, `resources/js/app.js`.
- **Auth layout** was missing `resources/css/app.css`; it now includes all three for consistency with other layouts.
- **Driver layout** now includes `resources/js/app.js` (in addition to CSS) so Alpine and other app JS run on driver pages.

---

## 3. Reusable Blade Components

### Existing (keep using)

- **`<x-card>`** – generic card with optional padding and class (`resources/views/components/card.blade.php`).
- **`<x-company.table>`** – company table wrapper with optional header slot.
- **`<x-export-dropdown>`** – PDF/Excel export dropdown (used in fuel, service, tax, comprehensive, vehicle, mileage reports).
- **`<x-report-stat-card>`** – stat cards for reports.
- **`<x-company.flash>`**, **`<x-company-alert>`**, **`<x-company.glass>`** – flash messages, alerts, glass layout.
- **`<x-input-label>`**, **`<x-text-input>`**, **<x-input-error>** – form fields and errors.

### New in this refactor

- **`<x-company.form-field>`** – single component for company-style form fields (dark theme: slate-800 background, rounded-2xl).
  - **Location:** `resources/views/components/company/form-field.blade.php`
  - **Props:** `label`, `name`, `type` (text|textarea|select), `id`, `value`, `required`, `placeholder`.
  - **Usage:** Use in company forms (e.g. vehicle create/edit, branches) to replace repeated label+input blocks and keep styling consistent.
  - **Example:**
    ```blade
    <x-company.form-field name="plate_number" label="{{ __('vehicles.plate_number') }}" required />
    <x-company.form-field name="notes" type="textarea" label="ملاحظات" />
    <x-company.form-field name="company_branch_id" type="select" label="{{ __('vehicles.branch') }}">
        <option value="">—</option>
        @foreach($branches as $b)
            <option value="{{ $b->id }}">{{ $b->name }}</option>
        @endforeach
    </x-company.form-field>
    ```

---

## 4. Code Cleanup

- **Removed:** Commented `@extends('dashboard.layout.app')` in `resources/views/admin/users/create.blade.php` so the file only extends `admin.layouts.app`.

---

## 5. Duplication & Future Work

- **Form fields:** Company vehicle create/edit and other company forms repeat the same input classes (`rounded-2xl border border-slate-500/50 bg-slate-800/40 ...`). These can be gradually migrated to `<x-company.form-field>` for consistency and easier theme changes.
- **Cards:** Many views use `dash-card` or custom card classes; consider using `<x-card>` or `<x-company.table>` where it fits.
- **Buttons:** `dash-btn dash-btn-primary` / `dash-btn-secondary` are repeated; consider a shared button component if you standardise further.

---

## 6. What Was Not Changed (Safe to Do Later)

- **Unused files/assets:** No files or assets were deleted. An audit of unused controllers, views, and JS/CSS can be done separately with tests and grep to avoid breakage.
- **Undefined variables:** No broad sweep was done. If a view expects `$siteName`, `$brandTitleDriver`, etc., ensure they are provided by a view composer or controller.
- **Moving Livewire/Blade files:** Folder structure already matches conventions; no moves were needed.

---

## 7. Commands Run After Refactoring

```bash
php artisan optimize:clear
php artisan view:clear
php artisan cache:clear
```

Run these after any refactor that touches views or config so Blade and config cache stay in sync.

---

## 8. Testing Checklist

After refactoring, verify:

- [ ] **Admin:** Login, dashboard, companies list, company show, vehicles list, orders list, order show, settings.
- [ ] **Company:** Login (unified OTP), dashboard, vehicles list/create/edit, fuel, reports (service, tax, comprehensive, mileage), export dropdown (PDF/Excel), invoices, maintenance invoices, orders, tracking.
- [ ] **Driver:** Login, dashboard, history, notifications, service request create/show, fuel refill, tracking (if used).
- [ ] **Auth:** Unified login, OTP verify, password reset, register (if used).
- [ ] **Layouts:** No missing Livewire scripts on pages that use Livewire; no missing CSS/JS (check auth and driver in particular).

---

## Summary of Changes in This Refactor

| Change | File(s) | Why |
|--------|---------|-----|
| Add Livewire styles/scripts to driver layout | `layouts/driver.blade.php` | So driver area can use Livewire components without layout changes later. |
| Add `app.js` to driver layout | `layouts/driver.blade.php` | Driver pages now get the same Vite JS bundle as the rest of the app. |
| Add `app.css` to auth layout | `layouts/auth.blade.php` | Consistent Vite assets with other layouts. |
| Remove commented @extends | `admin/users/create.blade.php` | Cleaner, single source of layout. |
| New company form-field component | `components/company/form-field.blade.php` | Reusable company form fields; reduces duplication and keeps styling in one place. |
| Document structure and conventions | `REFACTORING.md` | Clear place for future maintainers to see layout/component usage and run clear commands. |

All changes are backward-compatible; existing views keep working as before.
