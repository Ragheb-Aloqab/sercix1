# Sercix1 / Servx — Full Audit Report

**Date:** March 9, 2026  
**Scope:** Full codebase audit, optimization, security, and UX improvements

---

## 1. Code Fixes Applied

### 1.1 Company Logo Bug (Critical)
- **Issue:** `IndexController` and `CompanyProfile` Livewire component used `logo_path` while the Company model stores the logo in the `logo` column.
- **Fix:** Updated `IndexController::siteLogoUrl()` to use `$company->logo` instead of `$company->logo_path`. Updated `CompanyProfile` to save/read from `logo` column.

### 1.2 LocaleController Open Redirect Hardening
- **Issue:** The `return` query parameter could potentially be abused for open redirects.
- **Fix:** Added validation to block paths containing `:` (e.g. `/https:evil.com`) in addition to existing `//` check.

### 1.3 CompanyProfile Logo Persistence
- **Issue:** Company profile logo upload was saving to non-existent `logo_path` column; logo never persisted.
- **Fix:** Changed all `logo_path` references to `logo` in the Livewire component's save logic.

---

## 2. Performance Optimizations

### 2.1 Dashboard Maintenance Request Counts
- **Before:** 4 separate `count()` queries for maintenance request statuses.
- **After:** Single query with conditional aggregation (`SUM(CASE WHEN ...)`).

### 2.2 Subscription Plans Caching
- **Added:** 1-hour cache for `SubscriptionService::activePlansForDisplay()` used on landing page.
- **Invalidation:** Cache cleared when plans are created, updated, deleted, or toggled in admin.

### 2.3 API Login Throttling
- **Added:** `throttle:5,1` on `POST /api/v1/auth/login` to prevent brute-force attacks.

### 2.4 Theme Preference Throttling
- **Added:** `throttle:30,1` on theme-preference endpoint to prevent abuse.

---

## 3. Security Improvements

- **Locale redirect:** Stricter validation on `return` parameter.
- **API auth:** Throttling on login endpoint.
- **Theme endpoint:** Throttling to limit request volume.
- **RBAC:** Dashboard and mobile grid now respect `@companyCan` for plan-gated features.

---

## 4. UI/UX Improvements

### 4.1 Company Dashboard
- **Mobile grid:** Items filtered by subscription plan features (`limited_vehicles`, `request_maintenance_offers`, `fuel_manual`, `vehicle_tracking`, `basic_reports`).
- **Desktop header buttons:** Wrapped in `@companyCan` so companies only see actions they can perform.
- **Alert links:** Expiring documents and pending invoice links only shown when company has access to the target feature.

### 4.2 Landing Page (Index)
- **Workflow section:** Added responsive grid (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-5`) for workflow steps for better layout on tablet/desktop.

---

## 5. System Stability

- **Subscription plans:** Plan cache invalidated on all plan CRUD operations.
- **Company logo:** Logo now correctly persists and displays across the app.
- **Dashboard:** No 403s from plan-restricted links; UI adapts to company plan.

---

## 6. Files Modified

| File | Changes |
|------|---------|
| `app/Http/Controllers/IndexController.php` | Fixed `logo_path` → `logo` |
| `app/Http/Controllers/LocaleController.php` | Open redirect hardening |
| `app/Http/Controllers/Company/DashboardController.php` | Optimized maintenance counts, null-safe `$counts` |
| `app/Http/Controllers/Admin/PlanController.php` | Plan cache invalidation on CRUD/toggle |
| `app/Livewire/Dashboard/Settings/CompanyProfile.php` | Fixed logo column (`logo` not `logo_path`) |
| `app/Services/SubscriptionService.php` | Plans caching + `invalidatePlansCache()` |
| `routes/web.php` | Theme-preference throttle |
| `routes/api/v1.php` | API login throttle |
| `resources/views/index.blade.php` | Workflow grid layout |
| `resources/views/company/dashboard/index.blade.php` | Plan-aware mobile grid + header buttons |

---

## 7. Recommendations for Future Work

1. **Dynamic pricing:** Consider using `subscriptionPlans` from DB on the landing page instead of hardcoded `plan1`/`plan2`/`plan3` from lang files.
2. **Eager loading:** Review `CompanyAnalyticsService` and report controllers for N+1; add `with()` where needed.
3. **Asset optimization:** Run `npm run build` and ensure Vite manifest is up to date for production.
4. **Tests:** Add feature tests for plan-gated routes and company logo flow.
5. **Laravel Pint:** Run `./vendor/bin/pint` for consistent code style.

---

## 8. Verification

Run the following to verify the application:

```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan optimize
npm run build
```

All routes load correctly. No syntax errors in modified PHP files.
