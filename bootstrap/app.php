<?php

use App\Exceptions\ExceptionHandler as AppExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'payments/tap/webhook',
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\ForceHttps::class);
        $middleware->redirectGuestsTo(fn () => route('dashboard'));
        $middleware->web(append: [\App\Http\Middleware\SetLocaleFromSession::class]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'technician' => \App\Http\Middleware\EnsureTechnician::class,
            'company' => \App\Http\Middleware\EnsureCompany::class,
            'driver' => \App\Http\Middleware\EnsureDriverSession::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'checkrole' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Global error handling: 401, 403, 404, 419, 500
        // Handles both normal requests (views) and AJAX (JSON)
        $exceptions->renderable(function (Throwable $e, Request $request) {
            return (new AppExceptionHandler())->render($request, $e);
        });
    })->create();
