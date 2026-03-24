<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\TimezoneHelper;

class TimezoneServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('timezone', function () {
            return new TimezoneHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set the default timezone for PHP
        date_default_timezone_set(config('app.timezone', 'Asia/Manila'));
    }
}
