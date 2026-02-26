<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CompanyAuth\OtpAuthController;
use Illuminate\Http\Request;


Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
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

/*
|--------------------------------------------------------------------------
| Tap Payment (webhook + redirect - public, no auth)
|--------------------------------------------------------------------------
*/
Route::post('/payments/tap/webhook', \App\Http\Controllers\TapWebhookController::class)->name('payments.tap.webhook');
Route::get('/payments/tap/redirect', \App\Http\Controllers\TapRedirectController::class)->name('payments.tap.redirect');

/*
|--------------------------------------------------------------------------
| Unified Sign-In (Company + Driver) — one form, redirect by role
|--------------------------------------------------------------------------
*/
Route::prefix('sign-in')->name('sign-in.')->group(function () {
    Route::get('/', [\App\Http\Controllers\UnifiedAuthController::class, 'showLogin'])->name('index');
    Route::post('/identify', [\App\Http\Controllers\UnifiedAuthController::class, 'identify'])
        ->middleware('throttle:5,1')
        ->name('identify');
    Route::get('/password', [\App\Http\Controllers\UnifiedAuthController::class, 'showPasswordForm'])->name('password');
    Route::post('/password', [\App\Http\Controllers\UnifiedAuthController::class, 'authenticatePassword'])
        ->middleware('throttle:5,1')
        ->name('authenticate_password');
    Route::post('/send-otp', [\App\Http\Controllers\UnifiedAuthController::class, 'sendOtp'])
        ->middleware('throttle:5,1')
        ->name('send_otp');
    Route::get('/verify', [\App\Http\Controllers\UnifiedAuthController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [\App\Http\Controllers\UnifiedAuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')
        ->name('verify_otp');
});

/*
|--------------------------------------------------------------------------
| Driver Auth (OTP) + Dashboard — /driver/login redirects to unified sign-in
|--------------------------------------------------------------------------
*/
Route::prefix('driver')->name('driver.')->group(function () {
    Route::get('/login', fn () => redirect()->route('sign-in.index'))->name('login');
    Route::post('/send-otp', [\App\Http\Controllers\DriverAuthController::class, 'sendOtp'])
        ->middleware('throttle:5,1')
        ->name('send_otp');
    Route::get('/verify', [\App\Http\Controllers\DriverAuthController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [\App\Http\Controllers\DriverAuthController::class, 'verifyOtp'])->name('verify_otp');
    Route::post('/logout', [\App\Http\Controllers\DriverAuthController::class, 'logout'])->name('logout');

    Route::middleware('driver')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DriverController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [\App\Http\Controllers\DriverController::class, 'history'])->name('history');
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
        Route::get('/inspections', [\App\Http\Controllers\DriverInspectionController::class, 'index'])->name('inspections.index');
        Route::post('/inspections/request/{vehicle}', [\App\Http\Controllers\DriverInspectionController::class, 'requestInspection'])->name('inspections.request')->whereNumber('vehicle');
        Route::get('/inspections/{inspection}/upload', [\App\Http\Controllers\DriverInspectionController::class, 'showUploadForm'])->name('inspections.upload')->whereNumber('inspection');
        Route::post('/inspections/{inspection}/upload', [\App\Http\Controllers\DriverInspectionController::class, 'upload'])->name('inspections.upload.store')->whereNumber('inspection');
        Route::get('/notifications', [\App\Http\Controllers\Driver\NotificationsController::class, 'index'])->name('notifications.index');
        Route::match(['get', 'patch', 'post'], '/notifications/{notification}/read', [\App\Http\Controllers\Driver\NotificationsController::class, 'markRead'])->name('notifications.read');
    });
});
Route::domain('{company}.servexmotors.com')->group(function(){

});
/*
|--------------------------------------------------------------------------
| Company Auth (OTP) — /company/login redirects to unified sign-in
|--------------------------------------------------------------------------
*/
Route::prefix('company')->name('company.')->group(function () {
    Route::get('/login', fn () => redirect()->route('sign-in.index'))->name('login');
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

    // Register
    Route::get('/register', [OtpAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [OtpAuthController::class, 'register'])->name('register.store');

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
    if (Auth::guard('web')->check()) {
        $user = Auth::guard('web')->user();
        if (in_array($user->role ?? '', ['admin', 'super_admin'])) {
            return redirect()->route('admin.dashboard');
        }
    }
    return redirect()->route('sign-in.index');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Companies Dashboard - Placeholder pages (Tracking, Fuel Balance)
|--------------------------------------------------------------------------
| Routes under /dashboard/companies for company-authenticated users.
*/
Route::middleware(['company'])
    ->prefix('dashboard/companies')
    ->name('company.')
    ->group(function () {
        Route::get('/tracking', [\App\Http\Controllers\Company\TrackingController::class, 'index'])
            ->name('tracking');
        Route::get('/fuel-balance', fn () => view('company.dashboard.fuel_balance'))->name('fuel_balance');
    });

/*
|--------------------------------------------------------------------------
| Load other route files
|--------------------------------------------------------------------------
*/
require __DIR__ . '/admin.php';
require __DIR__ . '/company.php';
require __DIR__ . '/auth.php';
