<?php

namespace Facade;

use Illuminate\Support\Facades\Facade;

class PackageFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'LaravelPackage';
    }
}
