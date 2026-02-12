<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class IndexController extends Controller
{
    public function __invoke()
    {
        $contactEmail = Setting::get('contact_email', 'b2b@oilgo.com');
        $contactWhatsapp = Setting::get('contact_whatsapp', '05xxxxxxxx');

        return view('index', [
            'contactEmail' => $contactEmail,
            'contactWhatsapp' => $contactWhatsapp,
        ]);
    }
}
