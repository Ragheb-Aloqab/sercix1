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
            'livewire.dashboard.*', 'components.*', 'errors.*',
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

        // Admin order partials: _services and _attachments — compute display data from $order
        View::composer('admin.orders.partials._services', function ($view) {
            $order = $view->getData()['order'] ?? null;
            if (!$order) {
                return;
            }
            $items = $order->services ?? collect();
            $subtotal = $items->sum(function ($service) {
                $qty = (float) ($service->pivot->qty ?? 0);
                $unit = (float) ($service->pivot->unit_price ?? 0) ?: (float) ($service->base_price ?? 0);
                return (float) ($service->pivot->total_price ?: ($qty * $unit));
            });
            $discount = (float) ($order->discount_amount ?? 0);
            $tax = (float) ($order->tax_amount ?? 0);
            $grandTotal = max(0, $subtotal - $discount + $tax);
            $itemsWithTotals = $items->map(function ($service) {
                $qty = (float) ($service->pivot->qty ?? 0);
                $unit = (float) ($service->pivot->unit_price ?? 0) ?: (float) ($service->base_price ?? 0);
                $total = (float) ($service->pivot->total_price ?: ($qty * $unit));
                return (object) ['service' => $service, 'qty' => $qty, 'unit' => $unit, 'total' => $total];
            });
            $view->with(compact('items', 'subtotal', 'discount', 'tax', 'grandTotal', 'itemsWithTotals'));
        });

        View::composer('admin.orders.partials._attachments', function ($view) {
            $order = $view->getData()['order'] ?? null;
            if (!$order) {
                return;
            }
            $attachments = $order->attachments ?? collect();
            $before = $attachments->where('type', 'before_photo');
            $after = $attachments->where('type', 'after_photo');
            $others = $attachments->whereIn('type', ['signature', 'other']);
            $view->with(compact('before', 'after', 'others'));
        });

        // Driver layout: driver name and initial from first linked vehicle
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
            $driverInitial = mb_substr($driverName, 0, 1);
            $view->with(compact('driverName', 'driverInitial'));
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
