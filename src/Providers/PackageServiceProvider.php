<?php

namespace Rayiumir\LaravelPackage\Providers;

use Illuminate\Support\ServiceProvider;
use Rayiumir\LaravelPackage\Console\Commands\Package;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([
            Package::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
