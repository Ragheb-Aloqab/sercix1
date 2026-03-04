<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Redirect;

class FlashServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Redirect::macro('withFlash', function (string $type, string $message) {
            return $this->with('flash', ['type' => $type, 'message' => $message]);
        });

        Redirect::macro('withSuccess', function (string $message) {
            return $this->withFlash('success', $message);
        });

        Redirect::macro('withError', function (string $message) {
            return $this->withFlash('error', $message);
        });

        Redirect::macro('withWarning', function (string $message) {
            return $this->withFlash('warning', $message);
        });

        Redirect::macro('withInfo', function (string $message) {
            return $this->withFlash('info', $message);
        });
    }
}
