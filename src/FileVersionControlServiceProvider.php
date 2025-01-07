<?php

namespace Jasotacademy\FileVersionControl;

use Illuminate\Support\ServiceProvider;

class FileVersionControlServiceProvider extends ServiceProvider {

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/file_version_control.php', 'file_version_control');
    }

    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/file_version_control.php' => config_path('file_version_control.php'),
        ], 'config');

        //Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}