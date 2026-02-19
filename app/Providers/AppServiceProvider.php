<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use App\Models\FuelRefill;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Observers\FuelRefillObserver;
use App\Observers\InvoiceObserver;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Invoice::observe(InvoiceObserver::class);
        Order::observe(OrderObserver::class);
        FuelRefill::observe(FuelRefillObserver::class);

        // Site branding (name + logo) — cached, scoped to views that need it
        // Note: 'index' excluded — IndexController passes fresh data directly
        $brandingViews = [
            'layouts.*', 'auth.*', 'driver.*', 'company.*', 'admin.*',
            'livewire.dashboard.*', 'components.*',
        ];
        View::composer($brandingViews, function ($view) {
            try {
                $siteName = Setting::get('site_name', 'SERV.X');
                $siteLogoUrl = cache()->remember('site_logo_url', 300, fn () => $this->siteLogoUrl());
            } catch (\Throwable $e) {
                $siteName = 'SERV.X';
                $siteLogoUrl = null;
            }
            $view->with([
                'siteName' => $siteName,
                'siteLogoUrl' => $siteLogoUrl,
            ]);
        });

        // Driver layout: driver name from first linked vehicle
        View::composer('layouts.driver', function ($view) {
            $driverName = __('driver.driver');
            $phone = Session::get('driver_phone');
            if ($phone) {
                $variants = $this->driverPhoneVariants($phone);
                $vehicle = Vehicle::whereIn('driver_phone', $variants)->where('is_active', true)->first();
                if ($vehicle && $vehicle->driver_name) {
                    $driverName = $vehicle->driver_name;
                }
            }
            $view->with('driverName', $driverName);
        });
    }

    private function driverPhoneVariants(?string $phone): array
    {
        if ($phone === null || $phone === '') {
            return [];
        }
        $variants = [trim($phone)];
        if (str_starts_with($phone, '+966')) {
            $variants[] = '0' . substr($phone, 4);
        }
        if (str_starts_with($phone, '0') && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 10) {
            $digits = preg_replace('/[^0-9]/', '', $phone);
            $variants[] = '+966' . substr($digits, 1, 9);
        }
        return array_unique(array_filter($variants));
    }

    private function siteLogoUrl(): ?string
    {
        try {
            $path = Setting::get('site_logo_path');
            if (!$path) {
                return null;
            }
            $url = asset('storage/' . $path);
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                $url .= '?v=' . filemtime($fullPath);
            }
            return $url;
        } catch (\Throwable $e) {
            // Table may not exist in tests
        }
        return null;
    }
}
