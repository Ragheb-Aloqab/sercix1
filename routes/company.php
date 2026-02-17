<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\OrdersController;
use App\Http\Controllers\Company\VehiclesController;
use App\Http\Controllers\Company\FuelController;
use App\Http\Controllers\Company\InvoicesController;
use App\Http\Controllers\Company\PaymentsController;
use App\Http\Controllers\Company\ServicesController;
use App\Http\Controllers\Company\NotificationsController;
use App\Http\Controllers\Company\BranchesController;
use App\Livewire\Company\Settings;

/*
|--------------------------------------------------------------------------
| Company Routes
|--------------------------------------------------------------------------
| Guard: company
| Middleware: auth:company
| Prefix: /company
| Name: company.*
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:company', 'guard.for.gate:company'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Orders
        Route::get('/orders', [OrdersController::class, 'index'])
            ->name('orders.index');

        Route::get('/orders/create', [OrdersController::class, 'create'])
            ->name('orders.create');

        Route::post('/orders', [OrdersController::class, 'store'])
            ->name('orders.store');

        Route::get('/orders/{order}', [OrdersController::class, 'show'])
            ->name('orders.show')
            ->whereNumber('order');
        Route::post('/orders/{order}/cancel', [OrdersController::class, 'cancel'])
            ->name('orders.cancel');
        // Invoices
        Route::get('/invoices', [InvoicesController::class, 'index'])
            ->name('invoices.index');

        Route::get('/invoices/{invoice}', [InvoicesController::class, 'show'])
            ->name('invoices.show')
            ->whereNumber('invoice');

        Route::get('/invoices/{invoice}/pdf', [InvoicesController::class, 'downloadPdf'])
            ->name('invoices.pdf')
            ->whereNumber('invoice');

        // Payments
        Route::get('/payments', [PaymentsController::class, 'index'])
            ->name('payments.index');

        Route::get('/payments/{payment}', [PaymentsController::class, 'show'])
            ->name('payments.show')
            ->whereNumber('payment');

        Route::post('/payments/{payment}/tap', [PaymentsController::class, 'payWithTap'])
            ->name('payments.tap')
            ->whereNumber('payment');

        Route::post('/payments/{payment}/tap-charge', [PaymentsController::class, 'chargeWithToken'])
            ->name('payments.tap.charge')
            ->whereNumber('payment');

        Route::post('/payments/{payment}/cash', [PaymentsController::class, 'payCash'])
            ->name('payments.cash')
            ->whereNumber('payment');

        Route::post('/payments/{payment}/bank-receipt', [PaymentsController::class, 'uploadBankReceipt'])
            ->name('payments.bank.receipt')
            ->whereNumber('payment');

        // Services
        Route::get('/services', [ServicesController::class, 'index'])
            ->name('services.index');

        // Vehicles
        Route::get('/vehicles', [VehiclesController::class, 'index'])
            ->name('vehicles.index');

        Route::get('/fuel', [FuelController::class, 'index'])
            ->name('fuel.index');

        Route::get('/vehicles/create', [VehiclesController::class, 'create'])
            ->name('vehicles.create');

        Route::post('/vehicles', [VehiclesController::class, 'store'])
            ->name('vehicles.store');

        Route::get('/vehicles/{vehicle}', [VehiclesController::class, 'show'])
            ->name('vehicles.show')
            ->whereNumber('vehicle');

        Route::get('/vehicles/{vehicle}/edit', [VehiclesController::class, 'edit'])
            ->name('vehicles.edit')
            ->whereNumber('vehicle');

        Route::patch('/vehicles/{vehicle}', [VehiclesController::class, 'update'])
            ->name('vehicles.update')
            ->whereNumber('vehicle');

        // Branches
        Route::get('/branches', [BranchesController::class, 'index'])
            ->name('branches.index');

        Route::get('/branches/create', [BranchesController::class, 'create'])
            ->name('branches.create');

        Route::post('/branches', [BranchesController::class, 'store'])
            ->name('branches.store');

        Route::get('/branches/{branch}/edit', [BranchesController::class, 'edit'])
            ->name('branches.edit')
            ->whereNumber('branch');

        Route::patch('/branches/{branch}', [BranchesController::class, 'update'])
            ->name('branches.update')
            ->whereNumber('branch');

        // Notifications
        Route::get('/notifications', [NotificationsController::class, 'index'])
            ->name('notifications.index');

        Route::patch('/notifications/{notification}/read', [NotificationsController::class, 'markRead'])
            ->name('notifications.read')
            ->whereNumber('notification');

        // Settings (Livewire)
        Route::get('/settings', Settings::class)->name('settings');
    });
