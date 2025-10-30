<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pail\Pail;

class PailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Override Pail's output formatting
        if (class_exists(Pail::class)) {
            // Disable Pail's HTTP request formatting by setting custom renderer
            Pail::renderHttpRequestsUsing(function ($request) {
                // Return null to disable Pail's formatting
                // Our middleware will handle output instead
                return null;
            });
        }
    }

    public function register(): void
    {
        //
    }
}
