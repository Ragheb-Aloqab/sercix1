# Pre-Deployment Checklist (Hostinger)

Use this checklist before uploading to Hostinger.

---

## ⚠️ FIX: Pages Show Without Styling

If your site loads but has **no CSS/styling**, do these in order:

1. **Set `APP_ENV=production`** in `.env` on Hostinger (critical – otherwise Vite loads from dev server which doesn't exist)
2. **Run `npm run build`** on your local machine before uploading
3. **Upload the entire `public/build/` folder** – must include:
   - `public/build/manifest.json`
   - `public/build/assets/*.css`
   - `public/build/assets/*.js`
4. **Document root** must point to the `public` folder (e.g. `public_html/public` or `domains/yourdomain.com/public_html/public`)
5. **Set `APP_URL`** to your live URL (e.g. `https://servxmotors.com`) – no trailing slash
6. **Clear config cache** on server: `php artisan config:clear && php artisan config:cache`

**Verify:** Visit `https://yourdomain.com/build/manifest.json` – it should return JSON, not 404.

---

## 1. Environment & Security

- [ ] **Never upload `.env`** – It's in `.gitignore`. Create `.env` on Hostinger manually.
- [ ] **If `.env` was ever committed:** Run `git rm --cached .env` then commit, so secrets are not in repo history. Rotate any exposed keys.
- [ ] **Production `.env` values:**
  ```
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://yourdomain.com
  ```
- [ ] **Generate new APP_KEY** on server: `php artisan key:generate`
- [ ] **Database credentials** – Use Hostinger MySQL credentials (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [ ] **Tap Payment** – Use `sk_live_xxx` and `pk_live_xxx` for production; set `TAP_MODE=live`

---

## 2. Build & Dependencies

- [ ] **Run `npm run build`** – Vite assets must be built before deploy (required for styling)
- [ ] **Run `composer install --optimize-autoloader --no-dev`** – On server or before upload
- [ ] **Upload `public/build/`** – The entire folder: `manifest.json` + `assets/*.css` + `assets/*.js`

---

## 3. Laravel Setup (on Hostinger)

- [ ] **Document root** – Point to `/public` (Hostinger: Domains → Document Root)
- [ ] **Storage link:** `php artisan storage:link`
- [ ] **Run migrations:** `php artisan migrate --force`
- [ ] **Optimize:** `php artisan config:cache && php artisan route:cache && php artisan view:cache`

---

## 4. Cron Job (Required)

The app uses scheduled tasks (e.g. `tracking:fetch-locations` every 5 minutes).

**Add in Hostinger → Cron Jobs:**
```
* * * * * cd /home/u123456789/domains/yourdomain.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```
Replace path with your actual project path.

---

## 5. Queue Worker (Optional but recommended)

If using `QUEUE_CONNECTION=database`, run a queue worker. On Hostinger shared hosting you can:

- Add a cron: `* * * * * php artisan queue:work --stop-when-empty --max-jobs=10`
- Or use Hostinger's "Background Process" if available

---

## 6. File Permissions

- [ ] `storage/` and `bootstrap/cache/` – writable (755 or 775)
- [ ] `public/storage` – symlink created by `storage:link`

---

## 7. Verify Before Go-Live

- [ ] Visit `https://yourdomain.com/up` – Health check
- [ ] Test login (admin, company, technician)
- [ ] Test vehicle tracking (if configured)
- [ ] Test Tap payment (sandbox first)

---

## 8. Hostinger-Specific Notes

- **PHP version:** Use PHP 8.2+ (Hostinger: PHP Configuration)
- **Extensions:** Ensure `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo` are enabled
- **Memory limit:** 256M or higher recommended
- **Max execution time:** 60+ seconds for long requests
