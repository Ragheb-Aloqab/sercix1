<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function __invoke()
    {
        // White-label subdomain: root (/) must redirect to login (guest) or company dashboard (logged in)
        if (app()->bound('tenant_from_subdomain') && app('tenant_from_subdomain')) {
            if (!Auth::guard('company')->check()) {
                return redirect()->route('login');
            }
            return redirect()->route('company.dashboard');
        }

        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $wlBranding = app()->bound('tenant_from_subdomain') && app('tenant_from_subdomain');
        $siteName = ($tenant && $wlBranding) ? $tenant->company_name : Setting::get('site_name', 'Servx Motors');
        $siteLogoUrl = ($tenant && $wlBranding) ? ($tenant->getLogoUrl() ?? $this->siteLogoUrl()) : $this->siteLogoUrl();

        $contactEmail = Setting::get('contact_email', '');
        $contactPhone = Setting::get('contact_phone', Setting::get('contact_whatsapp', ''));
        $footerContactVisible = (bool) Setting::get('footer_contact_visible', true);

        $currentLocale = session('ui.locale', app()->getLocale());
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
            $dashboardRoute = route('admin.dashboard');
        }

        $waNumber = preg_replace('/[^0-9]/', '', $contactPhone ?? '');
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '966' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '966') && strlen($waNumber) <= 10) {
            $waNumber = '966' . ltrim($waNumber, '0');
        }

        return view('index', [
            'contactEmail' => $contactEmail,
            'contactPhone' => $contactPhone,
            'footerContactVisible' => $footerContactVisible,
            'siteName' => $siteName,
            'siteLogoUrl' => $siteLogoUrl,
            'currentLocale' => $currentLocale,
            'user' => $user,
            'dashboardRoute' => $dashboardRoute,
            'logoutRoute' => $logoutRoute,
            'waNumber' => $waNumber ?: '966512345678',
            'subscriptionPlans' => SubscriptionService::activePlansForDisplay(),
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
        if ($company?->logo && $this->logoFileExists($company->logo)) {
            return $this->logoUrl($company->logo);
        }

        return asset('images/serv.x logo.png');
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
