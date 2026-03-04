<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\OrdersController;
use App\Http\Controllers\Company\VehiclesController;
use App\Http\Controllers\Company\FuelController;
use App\Http\Controllers\Company\ReportsController;
use App\Http\Controllers\Company\ServiceReportController;
use App\Http\Controllers\Company\InvoicesController;
use App\Http\Controllers\Company\PaymentsController;
use App\Http\Controllers\Company\ServicesController;
use App\Http\Controllers\Company\NotificationsController;
use App\Http\Controllers\Company\BranchesController;
use App\Http\Controllers\Company\TrackingController;
use App\Http\Controllers\Company\VehicleInspectionController;
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

Route::middleware(['company'])
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

        Route::get('/invoices/{invoice}/maintenance-pdf', [InvoicesController::class, 'downloadMaintenancePdf'])
            ->name('invoices.maintenance-pdf')
            ->whereNumber('invoice');

        // Company-uploaded fuel invoices (view, download, thumbnail)
        Route::get('/fuel-invoices/{companyFuelInvoice}/view', [\App\Http\Controllers\Company\FuelInvoiceController::class, 'view'])
            ->name('fuel-invoices.view')
            ->whereNumber('companyFuelInvoice');
        Route::get('/fuel-invoices/{companyFuelInvoice}/thumbnail', [\App\Http\Controllers\Company\FuelInvoiceController::class, 'thumbnail'])
            ->name('fuel-invoices.thumbnail')
            ->whereNumber('companyFuelInvoice');
        Route::get('/fuel-invoices/{companyFuelInvoice}/download', [\App\Http\Controllers\Company\FuelInvoiceController::class, 'download'])
            ->name('fuel-invoices.download')
            ->whereNumber('companyFuelInvoice');

        // Payments (only when config servx.payments_enabled = true)
        Route::middleware('payments')->group(function () {
            Route::get('/payments', [PaymentsController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [PaymentsController::class, 'show'])->name('payments.show')->whereNumber('payment');
            Route::post('/payments/{payment}/tap', [PaymentsController::class, 'payWithTap'])->name('payments.tap')->whereNumber('payment');
            Route::post('/payments/{payment}/tap-charge', [PaymentsController::class, 'chargeWithToken'])->name('payments.tap.charge')->whereNumber('payment');
            Route::post('/payments/{payment}/cash', [PaymentsController::class, 'payCash'])->name('payments.cash')->whereNumber('payment');
            Route::post('/payments/{payment}/bank-receipt', [PaymentsController::class, 'uploadBankReceipt'])->name('payments.bank.receipt')->whereNumber('payment');
        });

        // Services
        Route::get('/services', [ServicesController::class, 'index'])
            ->name('services.index');

        // Vehicles
        Route::get('/vehicles', [VehiclesController::class, 'index'])
            ->name('vehicles.index');

        Route::get('/fuel', [FuelController::class, 'index'])
            ->name('fuel.index');

        // Fuel Balance (fleet fuel cards)
        Route::get('/fuel-balance', [\App\Http\Controllers\Company\FuelBalanceController::class, 'index'])
            ->name('fuel-balance');
        Route::post('/fuel-balance/add', [\App\Http\Controllers\Company\FuelBalanceController::class, 'addBalance'])
            ->name('fuel-balance.add');
        Route::post('/fuel-balance/add-all', [\App\Http\Controllers\Company\FuelBalanceController::class, 'addBalanceAll'])
            ->name('fuel-balance.add-all');

        Route::post('/fuel/{fuelRefill}/generate-invoice', [FuelController::class, 'generateInvoice'])
            ->name('fuel.generate-invoice')
            ->whereNumber('fuelRefill');

        // Reports
        Route::get('/reports', [ReportsController::class, 'index'])
            ->name('reports.index');
        Route::get('/reports/service', [ServiceReportController::class, 'index'])
            ->name('reports.service');
        Route::get('/reports/mileage', [ReportsController::class, 'mileage'])
            ->name('reports.mileage');
        Route::get('/reports/mileage/excel', [\App\Http\Controllers\Company\MileageReportController::class, 'exportExcel'])
            ->name('reports.mileage.excel');
        Route::get('/reports/mileage/pdf', [\App\Http\Controllers\Company\MileageReportController::class, 'exportPdf'])
            ->name('reports.mileage.pdf');
        Route::get('/reports/download/{export}', [\App\Http\Controllers\Company\ReportsController::class, 'downloadExport'])
            ->name('reports.download')
            ->whereUuid('export');

        // Insurances (My Insurance)
        Route::get('/insurances', [\App\Http\Controllers\Company\InsurancesController::class, 'index'])
            ->name('insurances.index');

        Route::get('/vehicles/create', [VehiclesController::class, 'create'])
            ->name('vehicles.create');

        Route::get('/vehicles/quota-request', [\App\Http\Controllers\Company\VehicleQuotaRequestController::class, 'show'])
            ->name('vehicles.quota-request');

        Route::post('/vehicles/quota-request', [\App\Http\Controllers\Company\VehicleQuotaRequestController::class, 'store'])
            ->name('vehicles.quota-request.store');

        Route::post('/vehicles', [VehiclesController::class, 'store'])
            ->name('vehicles.store');

        Route::get('/vehicles/{vehicle}', [VehiclesController::class, 'show'])
            ->name('vehicles.show')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/details', [VehiclesController::class, 'details'])
            ->name('vehicles.details')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/tracking', [VehiclesController::class, 'tracking'])
            ->name('vehicles.tracking')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/images', [VehiclesController::class, 'images'])
            ->name('vehicles.images')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/reports', [VehiclesController::class, 'reports'])
            ->name('vehicles.reports')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/mileage', [VehiclesController::class, 'mileage'])
            ->name('vehicles.mileage')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/report/excel', [\App\Http\Controllers\Company\VehicleReportController::class, 'exportExcel'])
            ->name('vehicles.report.excel')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/report/pdf', [\App\Http\Controllers\Company\VehicleReportController::class, 'exportPdf'])
            ->name('vehicles.report.pdf')
            ->whereNumber('vehicle');

        Route::get('/vehicles/{vehicle}/edit', [VehiclesController::class, 'edit'])
            ->name('vehicles.edit')
            ->whereNumber('vehicle');

        Route::patch('/vehicles/{vehicle}', [VehiclesController::class, 'update'])
            ->name('vehicles.update')
            ->whereNumber('vehicle');

        Route::post('/vehicles/{vehicle}/documents/registration', [\App\Http\Controllers\Company\VehicleDocumentController::class, 'uploadRegistration'])
            ->name('vehicles.documents.registration')
            ->whereNumber('vehicle');
        Route::post('/vehicles/{vehicle}/documents/insurance', [\App\Http\Controllers\Company\VehicleDocumentController::class, 'uploadInsurance'])
            ->name('vehicles.documents.insurance')
            ->whereNumber('vehicle');
        Route::patch('/vehicles/{vehicle}/documents/expiry', [\App\Http\Controllers\Company\VehicleDocumentController::class, 'updateExpiryDates'])
            ->name('vehicles.documents.expiry')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/documents/registration/preview', [\App\Http\Controllers\Company\VehicleDocumentController::class, 'previewRegistration'])
            ->name('vehicles.documents.registration.preview')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/documents/insurance/preview', [\App\Http\Controllers\Company\VehicleDocumentController::class, 'previewInsurance'])
            ->name('vehicles.documents.insurance.preview')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/documents/registration/download', [\App\Http\Controllers\Company\VehicleDocumentController::class, 'downloadRegistration'])
            ->name('vehicles.documents.registration.download')
            ->whereNumber('vehicle');
        Route::get('/vehicles/{vehicle}/documents/insurance/download', [\App\Http\Controllers\Company\VehicleDocumentController::class, 'downloadInsurance'])
            ->name('vehicles.documents.insurance.download')
            ->whereNumber('vehicle');

        // Tracking
        Route::get('/vehicles/{vehicle}/track', [TrackingController::class, 'show'])
            ->name('vehicles.track')
            ->whereNumber('vehicle');
        Route::post('/vehicles/{vehicle}/track/fetch', [TrackingController::class, 'fetchLocation'])
            ->name('vehicles.track.fetch')
            ->whereNumber('vehicle');
        Route::get('/tracking', [TrackingController::class, 'index'])
            ->name('tracking.index');
        Route::post('/tracking/fetch-all', [TrackingController::class, 'fetchAll'])
            ->name('tracking.fetch_all');

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
        // Maintenance Requests (RFQ workflow)
        Route::get('/maintenance-requests/create', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'create'])
            ->name('maintenance-requests.create');
        Route::post('/maintenance-requests', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'store'])
            ->name('maintenance-requests.store');
        Route::get('/maintenance-requests', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'index'])
            ->name('maintenance-requests.index');
        Route::get('/maintenance-requests/{maintenanceRequest}', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'show'])
            ->name('maintenance-requests.show')
            ->whereNumber('maintenanceRequest');
        Route::post('/maintenance-requests/{maintenanceRequest}/reject', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'reject'])
            ->name('maintenance-requests.reject')
            ->whereNumber('maintenanceRequest');
        Route::post('/maintenance-requests/{maintenanceRequest}/send-rfq', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'sendRfq'])
            ->name('maintenance-requests.send-rfq')
            ->whereNumber('maintenanceRequest');
        Route::post('/maintenance-requests/{maintenanceRequest}/approve-center/{quotation}', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'approveCenter'])
            ->name('maintenance-requests.approve-center')
            ->whereNumber(['maintenanceRequest', 'quotation']);
        Route::post('/maintenance-requests/{maintenanceRequest}/reject-all-quotes', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'rejectAllQuotes'])
            ->name('maintenance-requests.reject-all-quotes')
            ->whereNumber('maintenanceRequest');
        Route::post('/maintenance-requests/{maintenanceRequest}/approve-invoice', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'approveInvoice'])
            ->name('maintenance-requests.approve-invoice')
            ->whereNumber('maintenanceRequest');
        Route::post('/maintenance-requests/{maintenanceRequest}/reject-invoice', [\App\Http\Controllers\Company\MaintenanceRequestController::class, 'rejectInvoice'])
            ->name('maintenance-requests.reject-invoice')
            ->whereNumber('maintenanceRequest');

        // Maintenance Offers (quotations grouped by request)
        Route::get('/maintenance-offers', [\App\Http\Controllers\Company\MaintenanceOffersController::class, 'index'])
            ->name('maintenance-offers.index');

        // Maintenance Centers (read-only list of active centers available for RFQ)
        Route::get('/maintenance-centers', [\App\Http\Controllers\Company\MaintenanceCenterController::class, 'index'])
            ->name('maintenance-centers.index');

        // Maintenance Invoice Archive (view & download final invoices)
        Route::get('/maintenance-invoices', [\App\Http\Controllers\Company\MaintenanceInvoiceController::class, 'index'])
            ->name('maintenance-invoices.index');
        Route::post('/maintenance-invoices', [\App\Http\Controllers\Company\MaintenanceInvoiceController::class, 'store'])
            ->name('maintenance-invoices.store');
        Route::get('/maintenance-invoices/{maintenanceRequest}/view', [\App\Http\Controllers\Company\MaintenanceInvoiceController::class, 'stream'])
            ->name('maintenance-invoices.view')
            ->whereNumber('maintenanceRequest');
        Route::get('/maintenance-invoices/{maintenanceRequest}/download', [\App\Http\Controllers\Company\MaintenanceInvoiceController::class, 'download'])
            ->name('maintenance-invoices.download')
            ->whereNumber('maintenanceRequest');
        Route::get('/maintenance-invoices/company/{companyMaintenanceInvoice}/view', [\App\Http\Controllers\Company\MaintenanceInvoiceController::class, 'streamCompanyInvoice'])
            ->name('maintenance-invoices.company.view')
            ->whereNumber('companyMaintenanceInvoice');
        Route::get('/maintenance-invoices/company/{companyMaintenanceInvoice}/thumbnail', [\App\Http\Controllers\Company\MaintenanceInvoiceController::class, 'thumbnailCompanyInvoice'])
            ->name('maintenance-invoices.company.thumbnail')
            ->whereNumber('companyMaintenanceInvoice');
        Route::get('/maintenance-invoices/company/{companyMaintenanceInvoice}/download', [\App\Http\Controllers\Company\MaintenanceInvoiceController::class, 'downloadCompanyInvoice'])
            ->name('maintenance-invoices.company.download')
            ->whereNumber('companyMaintenanceInvoice');

        // Vehicle Inspections
        Route::get('/inspections', [VehicleInspectionController::class, 'index'])
            ->name('inspections.index');
        Route::get('/inspections/{inspection}', [VehicleInspectionController::class, 'show'])
            ->name('inspections.show')
            ->whereNumber('inspection');
        Route::get('/inspections/{inspection}/photo/{photo}', [VehicleInspectionController::class, 'servePhoto'])
            ->name('inspections.photo')
            ->whereNumber(['inspection', 'photo']);
        Route::patch('/inspections/{inspection}/approve', [VehicleInspectionController::class, 'approve'])
            ->name('inspections.approve')
            ->whereNumber('inspection');
        Route::patch('/inspections/{inspection}/reject', [VehicleInspectionController::class, 'reject'])
            ->name('inspections.reject')
            ->whereNumber('inspection');
        Route::get('/inspections/{inspection}/download', [VehicleInspectionController::class, 'downloadZip'])
            ->name('inspections.download')
            ->whereNumber('inspection');
        Route::post('/vehicles/{vehicle}/request-inspection', [VehicleInspectionController::class, 'requestInspection'])
            ->name('vehicles.request-inspection')
            ->whereNumber('vehicle');

        // Notifications
        Route::get('/notifications', [NotificationsController::class, 'index'])
            ->name('notifications.index');

        Route::patch('/notifications/{notification}/read', [NotificationsController::class, 'markRead'])
            ->name('notifications.read');

        // Settings (Livewire)
        Route::get('/settings', Settings::class)->name('settings');
    });
