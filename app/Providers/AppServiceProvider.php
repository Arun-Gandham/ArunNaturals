<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DelhiveryServiceInterface;
use App\Services\DelhiveryService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DelhiveryServiceInterface::class, DelhiveryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
