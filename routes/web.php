<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CompanyAuth\OtpAuthController;
use Illuminate\Http\Request;


Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->forget('two_factor_verified_at');
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('/', \App\Http\Controllers\IndexController::class)->name('index');

/*
|--------------------------------------------------------------------------
| SEO: robots.txt & Sitemap
|--------------------------------------------------------------------------
*/
Route::get('/robots.txt', \App\Http\Controllers\RobotsController::class)->name('robots');
Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');

Route::get('/set-locale', \App\Http\Controllers\LocaleController::class)->name('set-locale');

Route::post('/theme-preference', function (Request $request) {
    $theme = in_array($request->input('theme'), ['light', 'dark'], true)
        ? $request->input('theme')
        : 'light';
    app(\App\Services\ThemeService::class)->setPreference($theme);
    return response()->json(['theme' => $theme]);
})->middleware('web')->name('theme-preference');

/*
|--------------------------------------------------------------------------
| Tap Payment (webhook + redirect - public, no auth)
|--------------------------------------------------------------------------
*/
Route::post('/payments/tap/webhook', \App\Http\Controllers\TapWebhookController::class)->name('payments.tap.webhook');
Route::get('/payments/tap/redirect', \App\Http\Controllers\TapRedirectController::class)->name('payments.tap.redirect');

/*
|--------------------------------------------------------------------------
| Unified Login — /login (Admin 2FA, Company, Driver, Maintenance Center)
|--------------------------------------------------------------------------
*/
Route::get('/login', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'showLogin'])->name('login');
Route::prefix('login')->name('login.')->group(function () {
    Route::post('/identify', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'identify'])
        ->middleware('throttle:10,1')
        ->name('identify');
    Route::get('/password', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'showPasswordForm'])->name('password');
    Route::post('/password', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'authenticatePassword'])
        ->middleware('throttle:5,1')
        ->name('authenticate-password');
    Route::get('/verify-otp', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'showOtpVerify'])->name('verify-otp');
    Route::post('/verify-otp', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')
        ->name('verify-otp.store');
    Route::post('/resend-otp', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'resendOtp'])
        ->middleware('throttle:5,1')
        ->name('resend-otp');
    Route::get('/verify', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [\App\Http\Controllers\Auth\UnifiedLoginController::class, 'verifyOtpGeneric'])
        ->middleware('throttle:15,1')
        ->name('verify.store');
});

Route::get('/sign-in', fn () => redirect()->route('login'))->name('sign-in.index');

/*
|--------------------------------------------------------------------------
| Driver Auth (OTP) + Dashboard — /driver/login redirects to unified sign-in
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

/*
|--------------------------------------------------------------------------
| Maintenance Center Auth (OTP only)
|--------------------------------------------------------------------------
*/
Route::prefix('maintenance-center')->name('maintenance-center.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\MaintenanceCenterAuth\OtpAuthController::class, 'showLogin'])->name('login');
    Route::post('/send-otp', [\App\Http\Controllers\MaintenanceCenterAuth\OtpAuthController::class, 'sendOtp'])
        ->middleware('throttle:5,1')
        ->name('send-otp');
    Route::get('/verify', [\App\Http\Controllers\MaintenanceCenterAuth\OtpAuthController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [\App\Http\Controllers\MaintenanceCenterAuth\OtpAuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')
        ->name('verify.store');
    Route::post('/logout', [\App\Http\Controllers\MaintenanceCenterAuth\OtpAuthController::class, 'logout'])->name('logout');

    Route::middleware('maintenance_center')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\MaintenanceCenter\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/history', [\App\Http\Controllers\MaintenanceCenter\HistoryController::class, 'index'])->name('history.index');
        Route::get('/rfq/{maintenanceRequest}', [\App\Http\Controllers\MaintenanceCenter\RfqController::class, 'show'])->name('rfq.show')->whereNumber('maintenanceRequest');
        Route::post('/rfq/{maintenanceRequest}/quotation', [\App\Http\Controllers\MaintenanceCenter\RfqController::class, 'submitQuotation'])->name('rfq.submit-quotation')->whereNumber('maintenanceRequest');
        Route::post('/rfq/{maintenanceRequest}/start', [\App\Http\Controllers\MaintenanceCenter\RfqController::class, 'markStarted'])->name('rfq.start')->whereNumber('maintenanceRequest');
        Route::post('/rfq/{maintenanceRequest}/invoice', [\App\Http\Controllers\MaintenanceCenter\RfqController::class, 'uploadInvoice'])->name('rfq.upload-invoice')->whereNumber('maintenanceRequest');
    });
});
/*
|--------------------------------------------------------------------------
| Company Auth (OTP) — /company/login redirects to unified sign-in
|--------------------------------------------------------------------------
*/
Route::prefix('company')->name('company.')->group(function () {
    Route::get('/login', fn () => redirect()->route('login'))->name('login');
    Route::post('/login/send-otp', [OtpAuthController::class, 'sendOtp'])
        ->middleware('throttle:5,1')
        ->name('send_otp');
    Route::post('/register/resend-otp', [OtpAuthController::class, 'resendRegisterOtp'])
        ->middleware('throttle:5,1')
        ->name('resend_register_otp');
    Route::get('/login/verify', [OtpAuthController::class, 'showVerifyForm'])->name('verify');
    Route::post('/login/verify', [OtpAuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')
        ->name('verify_otp');

    // Company self-registration disabled — companies are created by Super Admin only
    Route::get('/register', fn () => abort(404))->name('register');
    Route::post('/register', fn () => abort(404))->name('register.store');

    Route::post('/logout', [OtpAuthController::class, 'logout'])->name('logout');
});


/*
|--------------------------------------------------------------------------
| Redirects
|--------------------------------------------------------------------------
*/
Route::redirect('/admin', 'dashboard');

/*
|--------------------------------------------------------------------------
| Profile (web guard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'active'])->group(function () {
    Route::view('/profile', 'profile')->name('profile');
});
/*
|--------------------------------------------------------------------------
| Dashboard redirect hub (used by auth middleware redirectGuardsTo)
|--------------------------------------------------------------------------
| No auth required - redirects to appropriate dashboard or sign-in.
*/
// Technician routes removed - redirect to admin
Route::get('/tech/{any?}', fn () => redirect()->route('admin.dashboard'))->where('any', '.*')->name('tech.redirect');

Route::get('/dashboard', function () {
    if (Auth::guard('company')->check()) {
        return redirect()->route('company.dashboard');
    }
    if (session()->has('driver_phone')) {
        return redirect()->route('driver.dashboard');
    }
    if (Auth::guard('maintenance_center')->check()) {
        return redirect()->route('maintenance-center.dashboard');
    }
    if (Auth::guard('web')->check()) {
        $user = Auth::guard('web')->user();
        if (in_array($user->role ?? '', ['admin', 'super_admin'])) {
            return redirect()->route('admin.dashboard');
        }
    }
    return redirect()->route('login');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Company routes are in routes/company.php (prefix /company)
| Tracking: company.tracking.index, Fuel Balance: company.fuel-balance
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Load other route files
|--------------------------------------------------------------------------
*/
require __DIR__ . '/admin.php';
require __DIR__ . '/company.php';
require __DIR__ . '/auth.php';
