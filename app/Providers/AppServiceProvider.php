<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Contracts\DelhiveryServiceInterface;
use App\Services\DelhiveryService;
use App\Models\SiteSetting;

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
        // Share site-wide settings with all views (for SEO, branding, etc.)
        View::composer('*', function ($view) {
            $settings = cache()->remember('site_settings', 60, function () {
                return SiteSetting::query()->first();
            });

            $view->with('siteSettings', $settings);
        });
    }
}
