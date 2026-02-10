<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\Maps\TechniciansMapController;
use App\Http\Controllers\Admin\ServicesController;

use App\Http\Controllers\Admin\Orders\OrderController;
use App\Http\Controllers\Admin\Orders\OrderAssignmentController;
use App\Http\Controllers\Admin\Orders\OrderStatusController;
use App\Http\Controllers\Admin\Orders\OrderAttachmentController;
use App\Http\Controllers\Admin\Orders\OrderPaymentController;
use App\Http\Controllers\Admin\Orders\OrderInvoiceController;

use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CustomersController;

use App\Http\Controllers\Admin\DashboardUserController;
use App\Http\Controllers\Admin\Settings\BankAccountController;

use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\InventoryMovementExportController;
// Webhooks خارج auth (لو تحتاج)
// use App\Http\Controllers\Payments\TapWebhookController;
// Route::post('/webhooks/tap', [TapWebhookController::class, 'handle'])->name('webhooks.tap');

Route::prefix('admin')->name('admin.')->group(function () {

    // /admin -> /admin/dashboard
    Route::redirect('/', '/admin/dashboard');

    /**
     * ✅ كل لوحة الأدمن محمية: auth:web + role:admin
     * أي "تقني" يحاول يدخل /admin/dashboard بياخذ 403 مباشرة
     */
    Route::middleware(['auth:web', 'role:admin'])->prefix('dashboard')->group(function () {

        // Admin Overview
        Route::view('/', 'admin.overview.index')->name('dashboard'); // admin.dashboard

        // Activities
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

        // عامة داخل لوحة الأدمن
        Route::get('/map', [MapController::class, 'index'])->name('map');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

        // =========================
        // Technicians
        // =========================
        Route::prefix('technicians')->group(function () {
            Route::get('/', [DashboardUserController::class, 'index'])->name('technicians.index');
            Route::get('/create', [DashboardUserController::class, 'create'])->name('technicians.create');
            Route::post('/', [DashboardUserController::class, 'store'])->name('technicians.store');
            Route::get('/{user}/edit', [DashboardUserController::class, 'edit'])->name('technicians.edit');
            Route::put('/{user}', [DashboardUserController::class, 'update'])->name('technicians.update');
            Route::patch('/{user}/toggle', [DashboardUserController::class, 'toggle'])->name('technicians.toggle');
            Route::delete('/{user}', [DashboardUserController::class, 'destroy'])->name('technicians.destroy');
        });

        // =========================
        // Orders
        // =========================
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');

            Route::post('/{order}/assign', [OrderAssignmentController::class, 'store'])->name('orders.assign');
            Route::post('/{order}/status', [OrderStatusController::class, 'store'])->name('orders.status');

            Route::post('/{order}/attachments', [OrderAttachmentController::class, 'store'])->name('orders.attachments.store');
            Route::delete('/attachments/{attachment}', [OrderAttachmentController::class, 'destroy'])->name('orders.attachments.destroy');

            Route::post('/{order}/payments', [OrderPaymentController::class, 'store'])->name('orders.payments.store');

            Route::get('/{order}/invoice', [OrderInvoiceController::class, 'show'])->name('orders.invoice.show');
            Route::post('/{order}/invoice', [OrderInvoiceController::class, 'store'])->name('orders.invoice.store');
        });

        Route::get('/bank-transfers', fn () => view('admin.bank-transfers.index'))->name('bank-transfers.index');

        // =========================
        // Services
        // =========================
        Route::prefix('services')->group(function () {
            Route::get('/', [ServicesController::class, 'index'])->name('services.index');
            Route::get('/create', [ServicesController::class, 'create'])->name('services.create');
            Route::post('/', [ServicesController::class, 'store'])->name('services.store');
            Route::get('/{service}/edit', [ServicesController::class, 'edit'])->name('services.edit');
            Route::put('/{service}', [ServicesController::class, 'update'])->name('services.update');
            Route::delete('/{service}', [ServicesController::class, 'destroy'])->name('services.destroy');
            Route::patch('/{service}/toggle', [ServicesController::class, 'toggle'])->name('services.toggle');
        });

        // =========================
        // Maps
        // =========================
        Route::get('/maps/technicians', [TechniciansMapController::class, 'index'])->name('maps.technicians');

        // =========================
        // Customers + Inventory
        // =========================
        Route::resource('customers', CustomersController::class)->except(['show'])->names('customers');
        Route::resource('inventory', InventoryController::class)->except(['show'])->names('inventory');

        Route::get('/inventory/movements', fn () => view('admin.inventory.movements'))
            ->name('inventory.movements');
        Route::get('/inventory/movements/export', InventoryMovementExportController::class)
            ->name('inventory.movements.export');
        
        // =========================
        // Notifications
        // =========================
        Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [NotificationsController::class, 'markRead'])->name('notifications.read');

        // =========================
        // Bank Accounts
        // =========================
        Route::prefix('settings/bank-accounts')->group(function () {
            Route::get('/', [BankAccountController::class, 'index'])->name('settings.bank-accounts');
            Route::post('/', [BankAccountController::class, 'store'])->name('settings.bank-accounts.store');
            Route::put('/{bankAccount}', [BankAccountController::class, 'update'])->name('settings.bank-accounts.update');
            Route::delete('/{bankAccount}', [BankAccountController::class, 'destroy'])->name('settings.bank-accounts.destroy');
            Route::patch('/{bankAccount}/toggle', [BankAccountController::class, 'toggleActive'])->name('settings.bank-accounts.toggle');
            Route::patch('/{bankAccount}/default', [BankAccountController::class, 'makeDefault'])->name('settings.bank-accounts.default');
        });
    });
});
