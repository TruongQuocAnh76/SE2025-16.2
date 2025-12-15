<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\StorageServiceInterface;
use App\Services\Storage\MinioStorageService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the storage service interface binding
        $this->app->singleton(StorageServiceInterface::class, function ($app) {
            return new MinioStorageService('s3');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
