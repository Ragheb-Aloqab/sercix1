<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| Versioned API routes for mobile app, partner integrations, and external
| consumers. Authenticate with Bearer token from POST /api/v1/auth/login.
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'version' => '1.0']))->name('api.v1.health');

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('api.v1.auth.login');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.v1.auth.logout');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index'])->name('api.v1.vehicles.index');
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('api.v1.invoices.index');
    });
});
