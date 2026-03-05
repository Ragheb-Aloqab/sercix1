# Hostinger 500 Error Troubleshooting

If you see **500 Server Error** on Hostinger, follow these steps:

## 1. Check Laravel logs on Hostinger

Via **File Manager** or **SSH**:
- Go to `storage/logs/laravel.log`
- Open the latest entries at the bottom
- Look for the actual error message (e.g. "Table doesn't exist", "Class not found", etc.)

## 2. Clear caches

Run these in **Hostinger Terminal** (or SSH):

```bash
php artisan config:clear
php artisan cache:clear
php artisan event:clear
php artisan view:clear
```

## 3. Run migrations

Ensure all tables exist (including `vehicle_mileage_history`):

```bash
php artisan migrate --force
```

## 4. Enable debug temporarily (to see the real error)

In `.env` on Hostinger, set:
```
APP_DEBUG=true
```

Reload the page to see the full error. **Set it back to `false`** after debugging.

## 5. Common causes

| Cause | Fix |
|-------|-----|
| **Missing `vehicle_monthly_mileage` table** (500 on company dashboard) | Run `php artisan migrate --force` |
| Missing `vehicle_mileage_history` table | Run `php artisan migrate --force` |
| Event listener error (VehicleCreated) | Run `php artisan event:clear` |
| Wrong PHP version | Use PHP 8.2+ in Hostinger control panel |
| `.env` not uploaded | Ensure `.env` exists and has correct DB credentials |
| Storage not linked | Run `php artisan storage:link` |
| File permissions | `storage` and `bootstrap/cache` should be writable (755 or 775) |

## 6. Fix for "vehicle_monthly_mileage doesn't exist"

The code now handles missing tables gracefully (falls back to fuel_refills/vehicle_locations).  
**To enable full mileage features**, run migrations on Hostinger:

```bash
cd ~/domains/servxmotors.com/public_html
php artisan migrate --force
```

This creates `vehicle_monthly_mileage`, `vehicle_mileage_history`, and any other missing tables.
