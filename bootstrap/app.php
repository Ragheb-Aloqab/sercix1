<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

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
        $middleware->redirectGuestsTo(fn () => route('dashboard'));
        $middleware->web(append: [\App\Http\Middleware\SetLocaleFromSession::class]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'technician' => \App\Http\Middleware\EnsureTechnician::class,
            'company' => \App\Http\Middleware\EnsureCompany::class,
            'driver' => \App\Http\Middleware\EnsureDriverSession::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (TokenMismatchException $e) {
            return redirect('/');
        });
    })->create();
