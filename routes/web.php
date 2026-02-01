<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CompanyAuth\OtpAuthController;
use Illuminate\Http\Request;


Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::get('/', fn () => view('index'));

/*
|--------------------------------------------------------------------------
| Company Auth (OTP)
|--------------------------------------------------------------------------
*/
Route::prefix('company')->name('company.')->group(function () {
    Route::get('/login', [OtpAuthController::class, 'showPhoneForm'])->name('login');
    Route::post('/login/send-otp', [OtpAuthController::class, 'sendOtp'])->name('send_otp');
    Route::get('/login/verify', [OtpAuthController::class, 'showVerifyForm'])->name('verify');
    Route::post('/login/verify', [OtpAuthController::class, 'verifyOtp'])->name('verify_otp');

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
Route::middleware(['auth:web'])->group(function () {
    Route::view('/profile', 'profile')->name('profile');
});

/*
|--------------------------------------------------------------------------
|  Dashboard Entry (ONE)
| اسمها: dashboard
| وظيفتها: تحول للمكان الصحيح حسب الـ guard
|--------------------------------------------------------------------------
*/


Route::get('/dashboard', function () {

    if (Auth::guard('company')->check()) {
        return redirect()->route('company.dashboard');
    }

    if (Auth::check()) {
        $user = Auth::user();

        if ($user->role === 'technician') {
            return redirect()->route('tech.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');

})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Load other route files
|--------------------------------------------------------------------------
*/
require __DIR__ . '/admin.php';
require __DIR__ . '/company.php';
require __DIR__ . '/technic.php';
require __DIR__ . '/auth.php';
