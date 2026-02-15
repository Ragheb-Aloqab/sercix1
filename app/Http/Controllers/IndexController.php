<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function __invoke()
    {
        // Redirect logged-in users to their dashboard
        if (Auth::guard('company')->check()) {
            return redirect()->route('company.dashboard');
        }
        if (session()->has('driver_phone')) {
            return redirect()->route('driver.dashboard');
        }
        if (Auth::guard('web')->check()) {
            $user = Auth::user();
            if (($user->role ?? null) === 'technician') {
                return redirect()->route('tech.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }

        $contactEmail = Setting::get('contact_email', 'b2b@oilgo.com');
        $contactWhatsapp = Setting::get('contact_whatsapp', '05xxxxxxxx');

        return view('index', [
            'contactEmail' => $contactEmail,
            'contactWhatsapp' => $contactWhatsapp,
        ]);
    }
}
