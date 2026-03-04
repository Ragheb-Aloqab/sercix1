# Dark/Light Mode — Production Troubleshooting (Hostinger)

If dark/light mode works locally but **not correctly on Hostinger**, check these causes and fixes.

---

## How It Works

1. **Theme init script** (`<x-theme-init />`) runs in `<head>` before any content
2. It sets `document.documentElement.classList.toggle('dark', isDark)` on `<html>`
3. Tailwind's `dark:` classes and CSS `.dark` selectors apply when `html` has the `dark` class
4. Theme preference: **logged-in** → DB (`theme_preference`); **guests** → `localStorage` key `sercix_theme`

---

## Common Causes & Fixes

### 1. Inline Script Blocked (CSP / ModSecurity)

**Symptom:** Page loads in one mode only; toggle does nothing.

**Cause:** Hostinger or your server may block inline `<script>` (Content Security Policy, ModSecurity).

**Fix:**
- Check Hostinger → Security → ModSecurity: temporarily disable to test
- Or move the theme script to an external file and load it first:

```blade
{{-- In theme-init.blade.php, replace inline script with: --}}
<script src="{{ asset('js/theme-init.js') }}" defer></script>
```

Then create `public/js/theme-init.js` with the script content. External scripts are usually allowed.

---

### 2. View Cache Serving Stale HTML

**Symptom:** Theme toggle works once, then reverts on refresh; or wrong theme on first load.

**Cause:** `php artisan view:cache` caches Blade output. If cached before theme logic was correct, old HTML is served.

**Fix:**
```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```
Then **do not** run `view:cache` until you've verified theme works. Or run it again after fixes.

---

### 3. CSS Not Loading (Wrong Asset URL)

**Symptom:** Whole site looks broken; no Tailwind styles. Dark mode "doesn't work" because nothing is styled.

**Cause:** `APP_URL` wrong, or `public/hot` deployed (forces Vite dev server).

**Fix:**
- Set `APP_URL=https://yourdomain.com` (no trailing slash, use `https` if site is HTTPS)
- Delete `public/hot` before deploy
- Run `npm run build` and upload `public/build/`
- Verify: `https://yourdomain.com/build/manifest.json` returns JSON

---

### 4. Tailwind Purging `dark:` Classes

**Symptom:** Some pages have dark mode, others don't; or specific components stay light.

**Cause:** Tailwind purges unused classes. If `content` paths don't include all Blade files, `dark:` classes may be removed.

**Fix:** Ensure `tailwind.config.js` includes all view paths:

```js
content: [
    './resources/views/**/*.blade.php',
    './app/View/Components/**/*.php',
],
```

Then rebuild: `npm run build`

---

### 5. Session / Cookie Issues (Theme Not Persisting)

**Symptom:** Theme resets on every page load for logged-in users.

**Cause:** Session cookie not sent (wrong domain, secure flag, SameSite).

**Fix:** In `.env` on Hostinger:
```
SESSION_DOMAIN=null
# or your domain, e.g. .yourdomain.com if using subdomains
SESSION_SECURE_COOKIE=true
# if your site is HTTPS
```

Note: For **company/maintenance_center/web** users, theme is stored in DB (`theme_preference`), not session. Session mainly affects guests. If DB theme isn't applied, check that `ThemeInit` receives `initialTheme` from `ThemeService`.

---

### 6. Theme Init Runs After CSS (Flash / Wrong Initial State)

**Symptom:** Brief flash of wrong theme, then corrects.

**Cause:** Script runs too late, or CSS loads before script.

**Fix:** Theme init is already first in `<head>`. If flash persists, add a blocking script or use `document.write` (not recommended). Better: ensure no `defer`/`async` on theme script — it should be synchronous and inline.

---

### 7. `theme-color` Meta Always Dark (Mobile)

**Symptom:** On mobile, browser UI (address bar) stays dark even in light mode.

**Cause:** Admin layout has `<meta name="theme-color" content="#0f172a" />` hardcoded.

**Fix:** Make it dynamic with a small inline script after theme-init, or use a Livewire/Alpine binding to update `content` based on current theme.

---

### 8. JavaScript Error Before Theme Init

**Symptom:** Theme never applies; page stuck in one mode.

**Cause:** Another script errors before theme-init, or theme-init itself errors (e.g. `localStorage` unavailable in private mode).

**Fix:**
- Open DevTools → Console. Look for errors.
- Wrap `localStorage` access in try/catch (already done in theme-init).
- Ensure no script in `<head>` runs before `<x-theme-init />`.

---

## Quick Checklist

| Check | Command / Action |
|-------|------------------|
| Clear caches | `php artisan view:clear && php artisan config:clear && php artisan cache:clear` |
| Rebuild assets | `npm run build` |
| No `public/hot` | Delete before deploy |
| Correct `APP_URL` | `APP_URL=https://yourdomain.com` in `.env` |
| Manifest reachable | Visit `/build/manifest.json` |
| Console errors | DevTools → Console on production |
| ModSecurity | Disable temporarily to test |

---

## Verify Theme Init

Add this temporarily to any layout (after `</body>`) to debug:

```html
<script>
console.log('Theme:', window.__sercix_theme);
console.log('HTML has dark:', document.documentElement.classList.contains('dark'));
</script>
```

- If `__sercix_theme` is undefined → theme-init script didn't run (blocked or error).
- If `dark` is false in dark mode → script ran but logic is wrong.
- If both look correct but page is wrong → CSS or Tailwind issue.

---

## Files Involved

| File | Role |
|------|------|
| `resources/views/components/theme-init.blade.php` | Inline script, sets `dark` class on `<html>` |
| `app/View/Components/ThemeInit.php` | Passes `initialTheme` from ThemeService |
| `app/Services/ThemeService.php` | Resolves theme from DB/session |
| `tailwind.config.js` | `darkMode: 'class'` — required |
| `resources/css/app.css` | `.dark .company-glass` etc. |
| Layouts | Must include `<x-theme-init />` in `<head>` |
