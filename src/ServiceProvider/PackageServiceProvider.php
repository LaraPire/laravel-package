<?php

namespace Rayiumir\LaravelPackage\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Package;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('Package', function() {
            return new Package();
        });
    }

    public function boot(): void
    {
        $this->_loadPublished();
    }

    private function _loadPublished(): void
    {
        $this->publishes([
            __DIR__.'/../Console/Commands' => app_path('Console/Commands/')
        ],'LaravelPackageCommands');

        $this->publishes([
            __DIR__.'/../Providers' => app_path('Providers/')
        ],'LaravelPackageProviders');
    }
}
