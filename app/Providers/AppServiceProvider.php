<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Setting;
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

        // Site branding (name + logo) for all views — admin can change in Settings
        View::composer('*', function ($view) {
            try {
                $siteName = Setting::get('site_name', 'SERV.X');
                $siteLogoUrl = $this->siteLogoUrl();
            } catch (\Throwable $e) {
                $siteName = 'SERV.X';
                $siteLogoUrl = null;
            }
            $view->with([
                'siteName' => $siteName,
                'siteLogoUrl' => $siteLogoUrl,
            ]);
        });
    }

    private function siteLogoUrl(): ?string
    {
        try {
            $path = Setting::get('site_logo_path');
            if ($path) {
                return asset('storage/' . $path);
            }
        } catch (\Throwable $e) {
            // Table may not exist in tests
        }
        return null;
    }
}
