# Deployment Guide — Hostinger & Subdomains

This document describes how to deploy the application to **Hostinger** and configure **white-label subdomains** (e.g. `companyname.yourdomain.com`).

---

## 1. Hostinger Requirements

- **PHP**: 8.2+ (recommended 8.2 or 8.3)
- **Extensions**: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`
- **Document root**: Must point to the **`public`** folder (e.g. `public_html` → contents of `public`, or domain docroot = `.../public`)

### 1.1 Document root

- In Hostinger **Domains** → your domain → **Advanced** (or **File Manager**), set the document root to the `public` directory of the project.
- Example: if the project is at `/home/username/sercix1`, set document root to `/home/username/sercix1/public`.
- Do **not** expose the project root (where `.env` and `app/` live) as the web root.

### 1.2 Symlink / storage (optional)

- The app uses a custom `storage:link` command that works when `symlink()` is disabled (common on Hostinger).
- Run once after deploy: `php artisan storage:link`
- If you see a warning that symlink is disabled, create the link manually via **SSH**:
  ```bash
  cd /path/to/your/project
  ln -s ../storage/app/public public/storage
  ```
- Or in Hostinger File Manager: create a symbolic link from `public/storage` → `../storage/app/public`.

---

## 2. Production environment (.env)

1. Copy `.env.example` to `.env` on the server.
2. Generate key: `php artisan key:generate`
3. Set at least:

```env
APP_NAME="Your App Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
```

- **Important**: `APP_ENV=production` and `APP_DEBUG=false` are required so assets and styling load correctly.

---

## 3. Subdomain setup (white-label)

Company dashboards can be served on subdomains (e.g. `toyota.yourdomain.com`) with custom branding.

### 3.1 DNS (Hostinger)

- Add a **wildcard** A record so all subdomains point to the same server:
  - Type: **A**
  - Name: **`*`** (or `*.yourdomain.com` depending on the panel)
  - Value: your server IP (same as the main domain)
- Optional: add explicit A records for `www` and main domain if needed.

### 3.2 .env for subdomains

Set the base domain used for company subdomains (no `www`, no protocol):

```env
# Base domain for company subdomains: {subdomain}.WHITE_LABEL_DOMAIN
# Example: toyota.yourdomain.com → WHITE_LABEL_DOMAIN=yourdomain.com
WHITE_LABEL_DOMAIN=yourdomain.com
```

- Use the same value as your main site domain (e.g. `servxmotors.com` or `yourdomain.com`).
- Reserved subdomains (`www`, `app`, `admin`, `api`, `mail`, etc.) are **not** treated as company subdomains and will load the main app.

### 3.3 Session cookie (optional, for cross-subdomain login)

If company users log in on the main domain and are redirected to `companyname.yourdomain.com`, you may want the session cookie to be sent on all subdomains. Set:

```env
SESSION_DOMAIN=.yourdomain.com
```

(Leading dot makes the cookie valid for `yourdomain.com` and all subdomains.)  
If you only use the main domain for admin and companies always use their own subdomain, `SESSION_DOMAIN=null` is fine.

### 3.4 Enabling a company subdomain

1. In **Admin** → **Customers** → edit the company.
2. Set **Subdomain** (e.g. `toyota`) — 3–30 chars, lowercase, numbers and hyphens only.
3. Enable **White-label** (or the equivalent “use subdomain” option).
4. Save. The company dashboard will be available at `https://toyota.yourdomain.com` (with `WHITE_LABEL_DOMAIN=yourdomain.com`).

---

## 4. First-time deploy steps

Run on the server (SSH or Hostinger terminal), from the project root:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

- Build frontend assets on your machine or CI and upload the built files, or run `npm ci && npm run build` on the server if Node is available.
- If you use queues (e.g. for report generation), run a worker:  
  `php artisan queue:work --tries=3` (or configure a process manager like Supervisor).

---

## 5. Cron (scheduled tasks)

Laravel’s scheduler runs tracking, inspections, mileage, and admin alerts. Add one cron job on Hostinger:

**Cron command** (run every minute):

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path/to/your/project` with the full path to the project root (e.g. `/home/username/sercix1`).

In Hostinger: **Advanced** → **Cron Jobs** → create new cron, set schedule to “Every minute” and use the command above.

---

## 6. Security checklist

- [ ] `APP_DEBUG=false` and `APP_ENV=production`
- [ ] Strong `APP_KEY` (generated with `php artisan key:generate`)
- [ ] Database credentials stored only in `.env` (never in git)
- [ ] Document root is `public` only
- [ ] `.env` is outside the web root and not publicly readable
- [ ] HTTPS enabled for the domain and subdomains (Hostinger SSL)

---

## 7. Troubleshooting

| Issue | Check |
|-------|--------|
| 500 error | `storage/logs/laravel.log`, file permissions (`storage/`, `bootstrap/cache/` writable) |
| CSS/JS not loading | `APP_ENV=production`, `APP_DEBUG=false`, correct `APP_URL` |
| Subdomain shows main site | DNS wildcard, `WHITE_LABEL_DOMAIN` in `.env`, company has `subdomain` + `white_label_enabled` |
| Session lost on subdomain | Consider `SESSION_DOMAIN=.yourdomain.com` |
| Storage links (images) 404 | Run `php artisan storage:link` or create symlink manually (see 1.2) |
| ModSecurity / script blocked | App uses meta tags for theme (no inline script) to reduce blocks; if needed, adjust ModSecurity rules in Hostinger |

---

## 8. Quick reference

| Item | Value |
|------|--------|
| Main app URL | `APP_URL` (e.g. `https://yourdomain.com`) |
| Company subdomain URL | `https://{subdomain}.{WHITE_LABEL_DOMAIN}` |
| Config key for subdomain domain | `config('servx.white_label_domain')` → from `WHITE_LABEL_DOMAIN` |
| Reserved subdomains (not tenant) | `www`, `app`, `admin`, `api`, `mail`, `ftp`, `cdn`, `static` |
