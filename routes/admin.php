<?php

use Illuminate\Support\Facades\Route;
use App\Models\Company;

use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ServicesController;

use App\Http\Controllers\Admin\Orders\OrderController;
use App\Http\Controllers\Admin\Orders\OrderStatusController;
use App\Http\Controllers\Admin\Orders\OrderAttachmentController;
use App\Http\Controllers\Admin\Orders\OrderPaymentController;
use App\Http\Controllers\Admin\Orders\OrderInvoiceController;

use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\Settings\BankAccountController;

use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\DataExportController;
// use App\Http\Controllers\Payments\TapWebhookController;
// Route::post('/webhooks/tap', [TapWebhookController::class, 'handle'])->name('webhooks.tap');

Route::middleware(['auth:web', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // /admin -> /admin/dashboard
    Route::redirect('/', '/admin/dashboard');

    /**
     * Admin dashboard: auth:web + admin (active + role check)
     */
    Route::prefix('dashboard')->group(function () {

        // Admin Overview (Super Dashboard)
        Route::view('/', 'admin.overview.index')->name('dashboard'); // admin.dashboard

        // Companies (Super Admin)
        Route::get('/companies', fn () => view('admin.companies.index'))->name('companies.index');
        Route::get('/companies/{company}', fn (Company $company) => view('admin.companies.show', ['company' => $company]))->name('companies.show');

        // Vehicles Overview (Super Admin)
        Route::get('/vehicles', fn () => view('admin.vehicles.index'))->name('vehicles.index');

        // Vehicle document expiry report
        Route::get('/vehicles/expiring-documents', [\App\Http\Controllers\Admin\VehicleDocumentExpiryController::class, 'index'])->name('vehicles.expiring-documents');
        Route::get('/export/expiring-documents', [\App\Http\Controllers\Admin\VehicleDocumentExpiryController::class, 'exportCsv'])->name('export.expiring-documents');
        Route::get('/export/expiring-documents/excel', [\App\Http\Controllers\Admin\VehicleDocumentExpiryController::class, 'exportExcel'])->name('export.expiring-documents.excel');

        // Vehicle Quota Requests
        Route::get('/quota-requests', [\App\Http\Controllers\Admin\VehicleQuotaRequestController::class, 'index'])->name('quota-requests.index');
        Route::post('/quota-requests/{quotaRequest}/approve', [\App\Http\Controllers\Admin\VehicleQuotaRequestController::class, 'approve'])->name('quota-requests.approve');
        Route::post('/quota-requests/{quotaRequest}/reject', [\App\Http\Controllers\Admin\VehicleQuotaRequestController::class, 'reject'])->name('quota-requests.reject');

        // Activities
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

        // Data Export (CSV + Excel)
        Route::prefix('export')->name('export.')->group(function () {
            Route::get('/orders', [DataExportController::class, 'orders'])->name('orders');
            Route::get('/orders/excel', [DataExportController::class, 'ordersExcel'])->name('orders.excel');
            Route::get('/companies', [DataExportController::class, 'companies'])->name('companies');
            Route::get('/companies/excel', [DataExportController::class, 'companiesExcel'])->name('companies.excel');
            Route::get('/vehicles', [DataExportController::class, 'vehicles'])->name('vehicles');
            Route::get('/vehicles/excel', [DataExportController::class, 'vehiclesExcel'])->name('vehicles.excel');
            Route::get('/services', [DataExportController::class, 'services'])->name('services');
            Route::get('/services/excel', [DataExportController::class, 'servicesExcel'])->name('services.excel');
            Route::get('/activities', [DataExportController::class, 'activities'])->name('activities');
            Route::get('/activities/excel', [DataExportController::class, 'activitiesExcel'])->name('activities.excel');
        });

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

        // =========================
        // Orders
        // =========================
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');

            Route::post('/{order}/status', [OrderStatusController::class, 'store'])->name('orders.status');

            Route::post('/{order}/attachments', [OrderAttachmentController::class, 'store'])->name('orders.attachments.store');
            Route::delete('/attachments/{attachment}', [OrderAttachmentController::class, 'destroy'])->name('orders.attachments.destroy');

            Route::get('/{order}/invoice', [OrderInvoiceController::class, 'show'])->name('orders.invoice.show');
            Route::get('/{order}/invoice/pdf', [OrderInvoiceController::class, 'downloadPdf'])->name('orders.invoice.pdf');
            Route::get('/{order}/invoice/maintenance-pdf', [OrderInvoiceController::class, 'downloadMaintenancePdf'])->name('orders.invoice.maintenance-pdf');
            Route::post('/{order}/invoice', [OrderInvoiceController::class, 'store'])->name('orders.invoice.store');
        });

        // Payments (only when config servx.payments_enabled = true)
        Route::middleware('payments')->group(function () {
            Route::post('/orders/{order}/payments', [OrderPaymentController::class, 'store'])->name('orders.payments.store');
            Route::get('/bank-transfers', fn () => view('admin.bank-transfers.index'))->name('bank-transfers.index');
        });

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
        // Customers (Companies)
        // =========================
        Route::resource('customers', CustomersController::class)->except(['show'])->names('customers');

        // =========================
        // Admin Users
        // =========================
        Route::get('/users', [\App\Http\Controllers\Admin\AdminUsersController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\Admin\AdminUsersController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\Admin\AdminUsersController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\AdminUsersController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminUsersController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [\App\Http\Controllers\Admin\AdminUsersController::class, 'toggleStatus'])->name('users.toggle');
        
        // =========================
        // Announcements
        // =========================
        Route::get('/announcements', [\App\Http\Controllers\Admin\AnnouncementsController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [\App\Http\Controllers\Admin\AnnouncementsController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [\App\Http\Controllers\Admin\AnnouncementsController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [\App\Http\Controllers\Admin\AnnouncementsController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementsController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementsController::class, 'destroy'])->name('announcements.destroy');

        // =========================
        // Notifications
        // =========================
        Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [NotificationsController::class, 'markRead'])->name('notifications.read');

        // =========================
        // Maintenance Centers
        // =========================
        Route::prefix('maintenance-centers')->name('maintenance-centers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MaintenanceCenterController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\MaintenanceCenterController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\MaintenanceCenterController::class, 'store'])->name('store');
            Route::get('/{maintenanceCenter}', [\App\Http\Controllers\Admin\MaintenanceCenterController::class, 'show'])->name('show')->whereNumber('maintenanceCenter');
            Route::get('/{maintenanceCenter}/edit', [\App\Http\Controllers\Admin\MaintenanceCenterController::class, 'edit'])->name('edit')->whereNumber('maintenanceCenter');
            Route::put('/{maintenanceCenter}', [\App\Http\Controllers\Admin\MaintenanceCenterController::class, 'update'])->name('update')->whereNumber('maintenanceCenter');
            Route::patch('/{maintenanceCenter}/toggle-status', [\App\Http\Controllers\Admin\MaintenanceCenterController::class, 'toggleStatus'])->name('toggle-status')->whereNumber('maintenanceCenter');
        });

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
