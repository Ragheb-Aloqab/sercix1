<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('ui.locale', config('app.locale', 'ar'));
        if (in_array($locale, ['ar', 'en'], true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
