<?php

namespace Rayiumir\LaravelPackage\Providers;

use App\Console\Commands\MakePackage;
use Illuminate\Support\ServiceProvider;

class MakePackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([
            MakePackage::class,
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
