<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Driver Routes
|--------------------------------------------------------------------------
| Guard: session (driver_phone)
| Middleware: driver
| Prefix: /driver
| Name: driver.*
|--------------------------------------------------------------------------
*/

Route::prefix('driver')->name('driver.')->group(function () {
    Route::get('/login', fn () => redirect()->route('login'))->name('login');
    Route::post('/send-otp', [\App\Http\Controllers\DriverAuthController::class, 'sendOtp'])
        ->middleware('throttle:5,1')
        ->name('send_otp');
    Route::get('/verify', [\App\Http\Controllers\DriverAuthController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [\App\Http\Controllers\DriverAuthController::class, 'verifyOtp'])->name('verify_otp');
    Route::post('/logout', [\App\Http\Controllers\DriverAuthController::class, 'logout'])->name('logout');

    Route::middleware('driver')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DriverController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [\App\Http\Controllers\DriverController::class, 'history'])->name('history');
        Route::get('/maintenance-request', [\App\Http\Controllers\Driver\MaintenanceRequestController::class, 'create'])->name('maintenance-request.create');
        Route::post('/maintenance-request', [\App\Http\Controllers\Driver\MaintenanceRequestController::class, 'store'])->name('maintenance-request.store');
        Route::get('/maintenance-request/{maintenanceRequest}', [\App\Http\Controllers\Driver\MaintenanceRequestController::class, 'show'])->name('maintenance-request.show')->whereNumber('maintenanceRequest');
        Route::get('/request', [\App\Http\Controllers\DriverController::class, 'createRequest'])->name('request.create');
        Route::post('/request', [\App\Http\Controllers\DriverController::class, 'storeRequest'])->name('request.store');
        Route::get('/request/{order}', [\App\Http\Controllers\DriverController::class, 'showRequest'])->name('request.show')->whereNumber('order');
        Route::post('/request/{order}/start', [\App\Http\Controllers\DriverController::class, 'startRequest'])->name('request.start')->whereNumber('order');
        Route::post('/request/{order}/invoice', [\App\Http\Controllers\DriverController::class, 'uploadInvoice'])->name('request.invoice')->whereNumber('order');
        Route::get('/fuel-refill', [\App\Http\Controllers\DriverController::class, 'createFuelRefill'])->name('fuel-refill.create');
        Route::post('/fuel-refill', [\App\Http\Controllers\DriverController::class, 'storeFuelRefill'])->name('fuel-refill.store');
        Route::get('/tracking', [\App\Http\Controllers\DriverController::class, 'tracking'])->name('tracking');
        Route::post('/tracking/start', [\App\Http\Controllers\DriverController::class, 'startTracking'])->name('tracking.start');
        Route::post('/tracking/stop', [\App\Http\Controllers\DriverController::class, 'stopTracking'])->name('tracking.stop');
        Route::get('/tracking/status', [\App\Http\Controllers\DriverController::class, 'trackingStatus'])->name('tracking.status');
        Route::post('/tracking/report', [\App\Http\Controllers\DriverController::class, 'reportTracking'])->name('tracking.report')->middleware('throttle:60,1');
        Route::post('/odometer/daily', [\App\Http\Controllers\DriverController::class, 'storeDailyOdometer'])->name('odometer.daily');
        Route::get('/inspections', [\App\Http\Controllers\DriverInspectionController::class, 'index'])->name('inspections.index');
        Route::post('/inspections/request/{vehicle}', [\App\Http\Controllers\DriverInspectionController::class, 'requestInspection'])->name('inspections.request')->whereNumber('vehicle');
        Route::get('/inspections/{inspection}/upload', [\App\Http\Controllers\DriverInspectionController::class, 'showUploadForm'])->name('inspections.upload')->whereNumber('inspection');
        Route::post('/inspections/{inspection}/upload', [\App\Http\Controllers\DriverInspectionController::class, 'upload'])->name('inspections.upload.store')->whereNumber('inspection');
        Route::get('/notifications', [\App\Http\Controllers\Driver\NotificationsController::class, 'index'])->name('notifications.index');
        Route::match(['get', 'patch', 'post'], '/notifications/{notification}/read', [\App\Http\Controllers\Driver\NotificationsController::class, 'markRead'])->name('notifications.read');
    });
});
