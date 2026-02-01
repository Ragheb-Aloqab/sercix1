<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\Invoice;
use App\Models\Order;
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
        /*
        سطر ضروري ليعمل mysql
        */
        Schema::defaultStringLength(191);
        /*
        ربط oberserver مع model
        */
        Invoice::observe(InvoiceObserver::class);
        Order::observe(OrderObserver::class);
    }
}
