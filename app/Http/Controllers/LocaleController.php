<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Set language and direction; persists to session so main index and dashboards stay in sync.
     * GET /set-locale?lang=ar|en or /set-locale?dir=rtl|ltr
     * Optional: ?return=/path to redirect back to a specific path (must start with /).
     */
    public function __invoke(Request $request)
    {
        $lang = $request->string('lang')->toString();
        $dir = $request->string('dir')->toString();

        if (in_array($lang, ['ar', 'en'], true)) {
            Session::put('ui.locale', $lang);
            Session::put('ui.dir', $lang === 'ar' ? 'rtl' : 'ltr');
            App::setLocale($lang);
        } elseif (in_array($dir, ['rtl', 'ltr'], true)) {
            Session::put('ui.dir', $dir);
            if ($dir === 'rtl') {
                Session::put('ui.locale', 'ar');
                App::setLocale('ar');
            } else {
                Session::put('ui.locale', 'en');
                App::setLocale('en');
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'locale' => Session::get('ui.locale', 'ar'),
                'dir' => Session::get('ui.dir', 'rtl'),
            ]);
        }

        $base = $request->getSchemeAndHttpHost();
        $returnPath = $request->query('return');
        // Safe redirect: only allow relative paths, block protocol-relative (//) and colons (e.g. /https:evil.com)
        if (is_string($returnPath) && str_starts_with($returnPath, '/') && !str_contains($returnPath, '//') && !str_contains($returnPath, ':')) {
            $target = $base . $returnPath;
        } else {
            $previous = url()->previous();
            if ($previous && !str_contains($previous, '/set-locale')) {
                $parsed = parse_url($previous);
                $path = $parsed['path'] ?? '/';
                $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';
                $target = $base . $path . $query;
            } else {
                $target = $base . '/';
                if (Auth::guard('company')->check()) {
                    $target = $base . '/company/dashboard';
                } elseif ($request->session()->has('driver_phone')) {
                    $target = $base . '/driver';
                } elseif (Auth::guard('maintenance_center')->check()) {
                    $target = $base . '/maintenance-center/dashboard';
                }
            }
        }

        Session::put('locale_just_changed', true);

        // Force session write so the next request sees the new locale
        Session::save();

        return redirect()->to($target)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
