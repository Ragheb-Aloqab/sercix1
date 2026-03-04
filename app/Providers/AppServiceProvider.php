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
use App\Models\DriverNotification;
use App\Models\Company;
use App\Models\VehicleLocation;
use App\Models\MaintenanceRequest;
use App\Observers\CompanyObserver;
use App\Observers\VehicleLocationObserver;
use App\Observers\FuelRefillObserver;
use App\Observers\InvoiceObserver;
use App\Observers\OrderObserver;
use App\Observers\VehicleObserver;
use App\Observers\MaintenanceRequestObserver;
use App\Events\VehicleCreated;
use App\Events\PaymentPaid;
use App\Events\MaintenanceRequestApproved;
use App\Events\OrderStatusChanged;
use App\Events\MaintenanceRequestCreated;
use App\Events\InvoiceCreated;
use Illuminate\Support\Facades\Event;

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
        // Override storage:link for Hostinger (exec/symlink disabled)
        $this->app->singleton(
            \Illuminate\Foundation\Console\StorageLinkCommand::class,
            \App\Console\Commands\StorageLinkCommand::class
        );

        Schema::defaultStringLength(191);

        Company::observe(CompanyObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Order::observe(OrderObserver::class);
        FuelRefill::observe(FuelRefillObserver::class);
        Vehicle::observe(VehicleObserver::class);
        VehicleLocation::observe(VehicleLocationObserver::class);
        MaintenanceRequest::observe(MaintenanceRequestObserver::class);

        $this->registerDomainEvents();

        // Site branding (name + logo) — cached, scoped to views that need it
        // Note: 'index' excluded — IndexController passes fresh data directly
        $brandingViews = [
            'layouts.*', 'auth.*', 'driver.*', 'company.*', 'admin.*', 'maintenance-center.*',
            'livewire.dashboard.*', 'components.*', 'errors.*',
        ];
        View::composer($brandingViews, function ($view) {
            try {
                $siteName = Setting::get('site_name', 'Servx Motors');
                $siteLogoUrl = cache()->remember('site_logo_url', 300, fn () => $this->siteLogoUrl());
            } catch (\Throwable $e) {
                $siteName = 'Servx Motors';
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

        // Driver layout: driver name, initial, and unread notification count
        View::composer('layouts.driver', function ($view) {
            $phone = Session::get('driver_phone');
            $driverName = __('driver.driver');
            $driverInitial = mb_substr($driverName, 0, 1);
            $driverNotificationCount = 0;
            if ($phone) {
                $variants = $this->driverPhoneVariants($phone);
                $cacheKey = 'driver_layout_' . md5($phone);
                $cached = cache()->remember($cacheKey, 300, function () use ($variants) {
                    $vehicle = Vehicle::whereIn('driver_phone', $variants)->where('is_active', true)->first();
                    $name = ($vehicle && $vehicle->driver_name) ? $vehicle->driver_name : __('driver.driver');
                    return ['driverName' => $name, 'driverInitial' => mb_substr($name, 0, 1)];
                });
                $driverName = $cached['driverName'];
                $driverInitial = $cached['driverInitial'] ?? mb_substr($driverName, 0, 1);
                $driverNotificationCount = DriverNotification::whereIn('driver_phone', $variants)
                    ->whereNull('read_at')
                    ->count();
            }
            $view->with([
                'driverName' => $driverName,
                'driverInitial' => $driverInitial,
                'driverNotificationCount' => $driverNotificationCount,
            ]);
        });
    }

    private function registerDomainEvents(): void
    {
        Event::listen(VehicleCreated::class, [
            \App\Listeners\LogVehicleCreated::class,
        ]);
        Event::listen(PaymentPaid::class, [
            \App\Listeners\UpdateInvoiceOnPaymentPaid::class,
            \App\Listeners\NotifyPaymentPaid::class,
        ]);
        Event::listen(MaintenanceRequestApproved::class, [
            \App\Listeners\NotifyMaintenanceRequestApproved::class,
        ]);
        Event::listen(OrderStatusChanged::class, [
            \App\Listeners\InvalidateCacheOnOrderStatusChanged::class,
        ]);
        Event::listen(MaintenanceRequestCreated::class, [
            \App\Listeners\InvalidateCacheOnMaintenanceRequestCreated::class,
        ]);
        Event::listen(InvoiceCreated::class, [
            \App\Listeners\InvalidateCacheOnInvoiceCreated::class,
        ]);
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
            if ($path) {
                $fullPath = storage_path('app/public/' . $path);
                if (file_exists($fullPath)) {
                    $url = asset('storage/' . $path);
                    return $url . '?v=' . filemtime($fullPath);
                }
            }
            return asset('images/serv.x logo.png');
        } catch (\Throwable $e) {
            return asset('images/serv.x logo.png');
        }
    }
}
