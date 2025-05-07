<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Package extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:package
                            {name : The name of the package}
                            {--vendor=your-vendor : The vendor name of the package}
                            {--with-tests : Include test directories and setup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Laravel package structure';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected Filesystem $files;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $packageName = $this->argument('name');
        $vendorName = $this->option('vendor');
        $withTests = $this->option('with-tests');

        $packagePath = base_path('packages/' . $packageName);

        // Create package directories
        $this->createDirectories($packagePath, $withTests);

        // Create package files
        $this->createServiceProvider($packagePath, $packageName, $vendorName);
        $this->createComposerJson($packagePath, $packageName, $vendorName);
        $this->createReadmeMd($packagePath, $packageName);
        $this->createLicenseMd($packagePath);

        if ($withTests) {
            $this->createTestFiles($packagePath, $packageName, $vendorName);
        }

        $this->info('Package created successfully!');
        return 0;
    }

    /**
     * Create the package directory structure.
     *
     * @param string $packagePath
     * @param bool $withTests
     * @return void
     */
    protected function createDirectories(string $packagePath, bool $withTests): void
    {
        $directories = [
            $packagePath . '/config',
            $packagePath . '/database/migrations',
            $packagePath . '/resources/lang',
            $packagePath . '/resources/views',
            $packagePath . '/routes',
            $packagePath . '/ServiceProvider',
        ];

        if ($withTests) {
            $directories = array_merge($directories, [
                $packagePath . '/tests',
                $packagePath . '/tests/Feature',
                $packagePath . '/tests/Unit',
            ]);
        }

        foreach ($directories as $directory) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $this->info('Directory structure created.');
    }

    /**
     * Create the service provider.
     *
     * @param string $packagePath
     * @param string $packageName
     * @param string $vendorName
     * @return void
     */
    protected function createServiceProvider(string $packagePath, string $packageName, string $vendorName): void
    {
        $className = $this->getClassNameFromPackageName($packageName) . 'ServiceProvider';
        $namespace = $this->getNamespaceFromVendorAndPackage($vendorName, $packageName);

        $content = <<<EOT
        <?php

        namespace {$namespace};

        use Illuminate\Support\ServiceProvider;

        class {$className} extends ServiceProvider
        {
            /**
             * Bootstrap the application services.
             *
             * @return void
             */
            public function boot()
            {
                // Publish configuration
                \$this->publishes([
                    __DIR__.'/../config/{$packageName}.php' => config_path('{$packageName}.php'),
                ], '{$packageName}-config');

                // Load routes
                \$this->loadRoutesFrom(__DIR__.'/../routes/web.php');

                // Load views
                \$this->loadViewsFrom(__DIR__.'/../resources/views', '{$packageName}');

                // Load translations
                \$this->loadTranslationsFrom(__DIR__.'/../resources/lang', '{$packageName}');

                // Load migrations
                \$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            }

            /**
             * Register the application services.
             *
             * @return void
             */
            public function register()
            {
                // Merge configuration
                \$this->mergeConfigFrom(
                    __DIR__.'/../config/{$packageName}.php', '{$packageName}'
                );
            }
        }
        EOT;

        $this->files->put(
            $packagePath . '/ServiceProvider/' . $className . '.php',
            $content
        );

        // Create a sample config file
        $configContent = <<<EOT
        <?php

        return [
            /*
            |--------------------------------------------------------------------------
            | {$packageName} Configuration
            |--------------------------------------------------------------------------
            |
            | Here you can modify the configuration for your package
            |
            */

            'enabled' => true,
        ];
        EOT;

        $this->files->put(
            $packagePath . '/config/' . $packageName . '.php',
            $configContent
        );

        // Create a sample route file
        $routeContent = <<<EOT
        <?php

        use Illuminate\Support\Facades\Route;

        /*
        |--------------------------------------------------------------------------
        | {$packageName} Routes
        |--------------------------------------------------------------------------
        |
        | Here you can register routes for your package
        |
        */

        // Route::get('/{$packageName}', function () {
        //     return view('{$packageName}::index');
        // });
        EOT;

        $this->files->put(
            $packagePath . '/routes/web.php',
            $routeContent
        );

        $this->info('Service provider and related files created.');
    }

    /**
     * Create composer.json file.
     *
     * @param string $packagePath
     * @param string $packageName
     * @param string $vendorName
     * @return void
     */
    protected function createComposerJson(string $packagePath, string $packageName, string $vendorName): void
    {
        $namespace = $this->getNamespaceFromVendorAndPackage($vendorName, $packageName);
        $packageNameWithVendor = strtolower($vendorName) . '/' . strtolower($packageName);

        $content = <<<EOT
        {
            "name": "{$packageNameWithVendor}",
            "description": "A Laravel package for {$packageName}",
            "type": "library",
            "license": "MIT",
            "authors": [
                {
                    "name": "Your Name",
                    "email": "your.email@example.com"
                }
            ],
            "minimum-stability": "dev",
            "prefer-stable": true,
            "require": {
                "php": "^8.0",
                "illuminate/support": "^9.0|^10.0|^11.0|^12.0"
            },
            "require-dev": {
                "phpunit/phpunit": "^9.0|^10.0"
            },
            "autoload": {
                "psr-4": {
                    "{$namespace}\\\\": "src/"
                }
            },
            "autoload-dev": {
                "psr-4": {
                    "{$namespace}\\\\Tests\\\\": "tests/"
                }
            },
            "extra": {
                "laravel": {
                    "providers": [
                        "{$namespace}\\\\{$this->getClassNameFromPackageName($packageName)}ServiceProvider"
                    ]
                }
            },
            "config": {
                "sort-packages": true
            }
        }
        EOT;

        $this->files->put(
            $packagePath . '/composer.json',
            $content
        );

        $this->info('composer.json file created.');
    }

    /**
     * Create README.md file.
     *
     * @param string $packagePath
     * @param string $packageName
     * @return void
     */
    protected function createReadmeMd(string $packagePath, string $packageName): void
    {
        $packageNameTitle = Str::title(str_replace('-', ' ', $packageName));

        $content = <<<EOT
        # {$packageNameTitle}

        [![Latest Version on Packagist](https://img.shields.io/packagist/v/your-vendor/{$packageName}.svg?style=flat-square)](https://packagist.org/packages/your-vendor/{$packageName})
        [![Total Downloads](https://img.shields.io/packagist/dt/your-vendor/{$packageName}.svg?style=flat-square)](https://packagist.org/packages/your-vendor/{$packageName})
        [![License](https://img.shields.io/packagist/l/your-vendor/{$packageName}.svg?style=flat-square)](https://packagist.org/packages/your-vendor/{$packageName})

        A description of what your package does.

        ## Installation

        You can install the package via composer:

        ```bash
        composer require your-vendor/{$packageName}
        ```

        ## Usage

        ```php
        // Usage example
        ```

        ### Testing

        ```bash
        composer test
        ```

        ## License

        The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
        EOT;

        $this->files->put(
            $packagePath . '/README.md',
            $content
        );

        $this->info('README.md file created.');
    }

    /**
     * Create LICENSE.md file.
     *
     * @param string $packagePath
     * @return void
     */
    protected function createLicenseMd(string $packagePath): void
    {
        $year = date('Y');

        $content = <<<EOT
        # The MIT License (MIT)

        Copyright (c) {$year} Your Name <your.email@example.com>

        > Permission is hereby granted, free of charge, to any person obtaining a copy
        > of this software and associated documentation files (the "Software"), to deal
        > in the Software without restriction, including without limitation the rights
        > to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
        > copies of the Software, and to permit persons to whom the Software is
        > furnished to do so, subject to the following conditions:
        >
        > The above copyright notice and this permission notice shall be included in
        > all copies or substantial portions of the Software.
        >
        > THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
        > IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
        > FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
        > AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
        > LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
        > OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
        > THE SOFTWARE.
        EOT;

        $this->files->put(
            $packagePath . '/LICENSE.md',
            $content
        );

        $this->info('LICENSE.md file created.');
    }

    /**
     * Create test files.
     *
     * @param string $packagePath
     * @param string $packageName
     * @param string $vendorName
     * @return void
     */
    protected function createTestFiles(string $packagePath, string $packageName, string $vendorName): void
    {
        $namespace = $this->getNamespaceFromVendorAndPackage($vendorName, $packageName);

        // Create TestCase.php
        $testCaseContent = <<<EOT
        <?php

        namespace {$namespace}\\Tests;

        use {$namespace}\\{$this->getClassNameFromPackageName($packageName)}ServiceProvider;
        use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

        class TestCase extends BaseTestCase
        {
            /**
             * Get package providers.
             *
             * @param  \\Illuminate\\Foundation\\Application  \$app
             * @return array
             */
            protected function getPackageProviders(\$app)
            {
                return [
                    {$this->getClassNameFromPackageName($packageName)}ServiceProvider::class,
                ];
            }

            /**
             * Define environment setup.
             *
             * @param  \\Illuminate\\Foundation\\Application  \$app
             * @return void
             */
            protected function defineEnvironment(\$app)
            {
                // Setup default database to use sqlite :memory:
                \$app['config']->set('database.default', 'testing');
                \$app['config']->set('database.connections.testing', [
                    'driver'   => 'sqlite',
                    'database' => ':memory:',
                    'prefix'   => '',
                ]);
            }
        }
        EOT;

        $this->files->put(
            $packagePath . '/tests/TestCase.php',
            $testCaseContent
        );

        // Create a sample feature test
        $featureTestContent = <<<EOT
        <?php

        namespace {$namespace}\\Tests\\Feature;

        use Tests\TestCase;

        class ExampleTest extends TestCase
        {
            /**
             * A basic feature test example.
             *
             * @return void
             */
            public function test_example()
            {
                \$this->assertTrue(true);
            }
        }
        EOT;

        $this->files->put(
            $packagePath . '/tests/Feature/ExampleTest.php',
            $featureTestContent
        );

        // Create a sample unit test
        $unitTestContent = <<<EOT
        <?php

        namespace {$namespace}\\Tests\\Unit;

        use PHPUnit\Framework\TestCase;

        class ExampleTest extends TestCase
        {
            /**
             * A basic unit test example.
             *
             * @return void
             */
            public function test_example()
            {
                \$this->assertTrue(true);
            }
        }
        EOT;

        $this->files->put(
            $packagePath . '/tests/Unit/ExampleTest.php',
            $unitTestContent
        );

        // Create phpunit.xml
        $phpunitContent = <<<EOT
        <?xml version="1.0" encoding="utf-8"?>
        <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                 xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
                 bootstrap="vendor/autoload.php"
                 colors="true">
            <testsuites>
                <testsuite name="Unit">
                    <directory suffix="Test.php">./tests/Unit</directory>
                </testsuite>
                <testsuite name="Feature">
                    <directory suffix="Test.php">./tests/Feature</directory>
                </testsuite>
            </testsuites>
            <coverage processUncoveredFiles="true">
                <include>
                    <directory suffix=".php">./src</directory>
                </include>
            </coverage>
            <php>
                <env name="APP_ENV" value="testing"/>
                <env name="BCRYPT_ROUNDS" value="4"/>
                <env name="CACHE_DRIVER" value="array"/>
                <env name="DB_CONNECTION" value="testing"/>
                <env name="MAIL_MAILER" value="array"/>
                <env name="QUEUE_CONNECTION" value="sync"/>
                <env name="SESSION_DRIVER" value="array"/>
                <env name="TELESCOPE_ENABLED" value="false"/>
            </php>
        </phpunit>
        EOT;

        $this->files->put(
            $packagePath . '/phpunit.xml',
            $phpunitContent
        );

        $this->info('Test files created.');
    }

    /**
     * Get the class name from the package name.
     *
     * @param string $packageName
     * @return string
     */
    protected function getClassNameFromPackageName(string $packageName): string
    {
        return Str::studly(str_replace('-', '_', $packageName));
    }

    /**
     * Get the namespace from vendor and package name.
     *
     * @param string $vendorName
     * @param string $packageName
     * @return string
     */
    protected function getNamespaceFromVendorAndPackage(string $vendorName, string $packageName): string
    {
        $vendorNamespace = Str::studly($vendorName);
        $packageNamespace = Str::studly(str_replace('-', '_', $packageName));

        return $vendorNamespace . '\\' . $packageNamespace;
    }
}
