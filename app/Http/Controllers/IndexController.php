<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function __invoke()
    {
        // Always show main page — logged-in users can access it via "Main page" link in sidebar
        $contactEmail = Setting::get('contact_email', 'b2b@oilgo.com');
        $contactWhatsapp = Setting::get('contact_whatsapp', '05xxxxxxxx');
        $siteName = Setting::get('site_name', 'SERV.X');
        $siteLogoUrl = $this->siteLogoUrl();

        $currentLocale = app()->getLocale();
        $user = null;
        $dashboardRoute = '#';
        $logoutRoute = route('logout');
        if (Auth::guard('company')->check()) {
            $user = Auth::guard('company')->user();
            $dashboardRoute = route('company.dashboard');
            $logoutRoute = route('company.logout');
        } elseif (session()->has('driver_phone')) {
            $user = (object) ['name' => __('driver.driver'), 'is_driver' => true];
            $dashboardRoute = route('driver.dashboard');
            $logoutRoute = route('driver.logout');
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $dashboardRoute = ($user->role ?? null) === 'technician' ? route('tech.dashboard') : route('admin.dashboard');
        }

        $waNumber = preg_replace('/[^0-9]/', '', $contactWhatsapp ?? '');
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '966' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '966') && strlen($waNumber) <= 10) {
            $waNumber = '966' . ltrim($waNumber, '0');
        }

        return view('index', [
            'contactEmail' => $contactEmail,
            'contactWhatsapp' => $contactWhatsapp,
            'siteName' => $siteName,
            'siteLogoUrl' => $siteLogoUrl,
            'currentLocale' => $currentLocale,
            'user' => $user,
            'dashboardRoute' => $dashboardRoute,
            'logoutRoute' => $logoutRoute,
            'waNumber' => $waNumber ?: '966512345678',
        ]);
    }

    private function siteLogoUrl(): ?string
    {
        // 1. Site logo (Admin → Settings → Site Branding)
        $path = Setting::query()->where('key', 'site_logo_path')->value('value');
        if ($path && $this->logoFileExists($path)) {
            return $this->logoUrl($path);
        }

        // 2. Company logo (Company → Settings → Company Profile) when company is logged in
        $company = Auth::guard('company')->user();
        if ($company?->logo_path && $this->logoFileExists($company->logo_path)) {
            return $this->logoUrl($company->logo_path);
        }

        return null;
    }

    private function logoFileExists(string $path): bool
    {
        return file_exists(storage_path('app/public/' . $path));
    }

    private function logoUrl(string $path): string
    {
        $url = asset('storage/' . $path);
        $fullPath = storage_path('app/public/' . $path);
        if (file_exists($fullPath)) {
            $url .= '?v=' . filemtime($fullPath);
        }
        return $url;
    }
}
