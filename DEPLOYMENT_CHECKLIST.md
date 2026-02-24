# Pre-Deployment Checklist (Hostinger)

Use this checklist before uploading to Hostinger.

---

## ⚠️ FIX: Pages Show Without Styling

If your site loads but has **no CSS/styling**, check these causes:

| Cause | Fix |
|-------|-----|
| **`public/hot` file exists** | Delete it. This file makes Laravel load from Vite dev server (localhost:5173) which doesn't exist on production. |
| **`APP_ENV` not production** | Set `APP_ENV=production` in `.env` or Laravel may try Vite dev server. |
| **Build folder missing** | Run `npm run build` and upload `public/build/` (manifest.json + assets/*.css + assets/*.js). |
| **Wrong document root** | Point to `public` folder (e.g. `public_html/public`). |
| **Wrong `APP_URL`** | Set to your live URL (e.g. `https://servxmotors.com`) – no trailing slash. |
| **Mixed content** | If site is HTTPS, use `https://` in APP_URL. |
| **`fallback.css` missing** | Upload `public/css/fallback.css` for CDN fallback when build fails. |

**Steps to fix:**
1. Delete `public/hot` if it exists (do not deploy it)
2. Set `APP_ENV=production` and `APP_URL=https://yourdomain.com` in `.env`
3. Run `npm run build` before uploading
4. Upload `public/build/` and `public/css/`
5. Clear config cache: `php artisan config:clear && php artisan config:cache`

**Verify:** Visit `https://yourdomain.com/build/manifest.json` – it should return JSON, not 404.

---

## 0. OTP / SMS (Company & Driver Login)

- [ ] **Set `AUTHENTICA_API_KEY`** in `.env` — OTP will not be sent without it. Get your key from [Authentica](https://authentica.sa).
- [ ] After adding, run `php artisan config:clear && php artisan config:cache`

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
- [ ] **Upload `public/css/`** – Contains `fallback.css` for CDN fallback when build fails
- [ ] **Do NOT upload `public/hot`** – Delete it if it exists on server (forces dev server, breaks styling)

---

## 3. Laravel Setup (on Hostinger)

- [ ] **Document root** – Point to `public` folder (Hostinger: Domains → Document Root → `public_html/public`)
- [ ] **If you cannot change document root:** The project includes a root `.htaccess` that redirects requests to `public/`. Ensure the root `.htaccess` is uploaded (in the same folder as `app/`, `config/`, etc.)
- [ ] **Storage link** – Hostinger often disables `exec()` and `symlink()`, so `php artisan storage:link` may fail. Create it manually via SSH:
  ```bash
  cd /home/u654853714/domains/yourdomain.com/public_html
  ln -s ../storage/app/public public/storage
  ```
  Or use Hostinger File Manager to create a symbolic link: `public/storage` → `../storage/app/public`
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
