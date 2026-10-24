<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Global marketing data (branding, navigation, socials, auth state) is
     * shared with every view by {@see SaasServiceProvider},
     * which sources it from the SaaS backend's get-saas-settings endpoint.
     */
    public function boot(): void
    {
        //
    }
}
