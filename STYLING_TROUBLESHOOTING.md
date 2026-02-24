# Styling Troubleshooting

This document explains why pages may load without CSS and how to fix it.

---

## How Styling Works

1. **Normal flow:** Laravel's `@vite(['resources/css/style.css'])` loads built assets from `public/build/` (manifest.json + hashed CSS/JS).
2. **Fallback:** If build CSS fails to load (blocked, 404, etc.), the `vite-cdn-fallback` component detects it and loads Tailwind CDN, Font Awesome, fonts, and `public/css/fallback.css`.

---

## Common Causes & Fixes

| Cause | Symptom | Fix |
|-------|---------|-----|
| **`public/hot` exists** | All pages unstyled; browser tries `localhost:5173` | Delete `public/hot` on server. Do not deploy it. Add to `.gitignore`. |
| **`APP_ENV` not production** | Vite may use dev server URL | Set `APP_ENV=production` in `.env` |
| **`public/build/` missing** | 404 on CSS/JS; fallback may kick in | Run `npm run build` and upload `public/build/` |
| **Wrong document root** | Assets 404 (wrong path) | Point document root to `public/` folder |
| **Wrong `APP_URL`** | Asset URLs wrong (http vs https, wrong domain) | Set `APP_URL=https://yourdomain.com` (no trailing slash) |
| **`public/css/fallback.css` missing** | Fallback loads but custom styles missing | Upload `public/css/` folder |
| **Blocked external resources** | Ad/tracking blockers block CDN | Fallback uses CDN; ensure fallback.css is self-hosted |
| **Mixed content** | HTTPS page loading HTTP assets | Use `https://` in APP_URL and asset URLs |

---

## Quick Fix Checklist

1. [ ] Delete `public/hot` if it exists on the server
2. [ ] Set `APP_ENV=production` and `APP_URL=https://yourdomain.com` in `.env`
3. [ ] Run `npm run build` locally before deploy
4. [ ] Upload `public/build/` (manifest.json + assets/*.css + assets/*.js)
5. [ ] Upload `public/css/` (fallback.css)
6. [ ] Ensure document root points to `public/`
7. [ ] Run `php artisan config:clear && php artisan config:cache`

---

## Verify

- **Manifest:** Visit `https://yourdomain.com/build/manifest.json` – should return JSON, not 404
- **CSS:** Visit a built CSS URL from the manifest – should return CSS
- **Fallback:** If build fails, page should still show Tailwind + fallback.css styles

---

## Layouts Using @vite

All main layouts use `@vite` for styling. Duplicate Tailwind CDN scripts have been removed; the CDN is only loaded by the fallback when build assets fail.
