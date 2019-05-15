<?php

namespace Ammonkc\Init;

use Illuminate\Support\ServiceProvider;

class InitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Registering package commands.
            $this->commands([
                Commands\AppInstall::class,
                Commands\UserCreate::class
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Register the main class to use with the facade
    }
}
