<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Set language and direction; persists to session so main index and dashboards stay in sync.
     * GET /set-locale?lang=ar|en or /set-locale?dir=rtl|ltr
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

        return redirect()->back();
    }
}
