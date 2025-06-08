<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class PackageGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:package
                            {name : The name of the package (e.g., blog-module)}
                            {--vendor= : The vendor name (default: your-vendor)}
                            {--description= : Package description}
                            {--author= : Author name}
                            {--email= : Author email}
                            {--with-tests : Include test directories and setup}
                            {--with-facade : Create a facade for the package}
                            {--with-controller : Create a sample controller}
                            {--with-model : Create a sample model with migration}
                            {--with-command : Create a sample Artisan command}
                            {--with-middleware : Create a sample middleware}
                            {--with-event : Create a sample event and listener}
                            {--with-notification : Create a sample notification}
                            {--with-interface : Create a sample interface and implementation}
                            {--with-repository : Create repository pattern files}
                            {--with-service : Create service layer files}
                            {--with-config : Create config file}
                            {--with-views : Create sample views}
                            {--with-lang : Create language files}
                            {--with-migrations : Create migrations directory}
                            {--with-routes : Create routes file}
                            {--all : Create all available options}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a comprehensive Laravel package structure with modern features';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected Filesystem $files;

    /**
     * Package metadata.
     *
     * @var array
     */
    protected array $package = [];

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
        $this->initializePackageData();
        
        $io = new SymfonyStyle($this->input, $this->output);
        $io->title('Laravel Package Generator');
        
        $this->validateInputs();
        
        $packagePath = $this->getPackagePath();
        
        if ($this->files->exists($packagePath)) {
            if (!$io->confirm("Package directory already exists at [{$packagePath}]. Overwrite?", false)) {
                return 0;
            }
        }

        $this->createPackageStructure($packagePath);
        
        $io->success("Package {$this->package['name']} created successfully!");
        $io->text([
            "Next steps:",
            "- Review the generated files in packages/{$this->package['name']}",
            "- Update composer.json with your actual dependencies",
            "- Implement your package functionality",
            "- Consider publishing to Packagist"
        ]);

        return 0;
    }

    /**
     * Initialize package data from input.
     */
    protected function initializePackageData(): void
    {
        $this->package = [
            'name' => Str::kebab($this->argument('name')),
            'vendor' => $this->option('vendor') ?: 'your-vendor',
            'description' => $this->option('description') ?: 'A Laravel package for '.$this->argument('name'),
            'author' => $this->option('author') ?: 'Your Name',
            'email' => $this->option('email') ?: 'your.email@example.com',
            'with_tests' => $this->option('with-tests') || $this->option('all'),
            'with_facade' => $this->option('with-facade') || $this->option('all'),
            'with_controller' => $this->option('with-controller') || $this->option('all'),
            'with_model' => $this->option('with-model') || $this->option('all'),
            'with_command' => $this->option('with-command') || $this->option('all'),
            'with_middleware' => $this->option('with-middleware') || $this->option('all'),
            'with_event' => $this->option('with-event') || $this->option('all'),
            'with_notification' => $this->option('with-notification') || $this->option('all'),
            'with_interface' => $this->option('with-interface') || $this->option('all'),
            'with_repository' => $this->option('with-repository') || $this->option('all'),
            'with_service' => $this->option('with-service') || $this->option('all'),
            'with_config' => $this->option('with-config') || $this->option('all'),
            'with_views' => $this->option('with-views') || $this->option('all'),
            'with_lang' => $this->option('with-lang') || $this->option('all'),
            'with_migrations' => $this->option('with-migrations') || $this->option('all'),
            'with_routes' => $this->option('with-routes') || $this->option('all'),
        ];

        $this->package['namespace'] = $this->getNamespaceFromVendorAndPackage(
            $this->package['vendor'],
            $this->package['name']
        );

        $this->package['class_name'] = $this->getClassNameFromPackageName($this->package['name']);
    }

    /**
     * Validate input parameters.
     */
    protected function validateInputs(): void
    {
        if (!preg_match('/^[a-z0-9\-]+$/', $this->package['name'])) {
            throw new \InvalidArgumentException('Package name must be lowercase with hyphens only');
        }

        if (!preg_match('/^[a-z0-9\-]+$/', $this->package['vendor'])) {
            throw new \InvalidArgumentException('Vendor name must be lowercase with hyphens only');
        }

        if (!filter_var($this->package['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address provided');
        }
    }

    /**
     * Get the full package path.
     *
     * @return string
     */
    protected function getPackagePath(): string
    {
        return base_path('packages/' . $this->package['vendor'] . '/' . $this->package['name']);
    }

    /**
     * Create the complete package structure.
     *
     * @param string $packagePath
     */
    protected function createPackageStructure(string $packagePath): void
    {
        $this->createDirectories($packagePath);
        $this->createServiceProvider($packagePath);
        $this->createComposerJson($packagePath);
        $this->createReadme($packagePath);
        $this->createLicense($packagePath);
        $this->createGitignore($packagePath);
        
        if ($this->package['with_tests']) {
            $this->createTestFiles($packagePath);
        }
        
        if ($this->package['with_facade']) {
            $this->createFacade($packagePath);
        }
        
        if ($this->package['with_controller']) {
            $this->createController($packagePath);
        }
        
        if ($this->package['with_model']) {
            $this->createModel($packagePath);
            $this->createMigration($packagePath);
        }
        
        if ($this->package['with_command']) {
            $this->createCommand($packagePath);
        }
        
        if ($this->package['with_middleware']) {
            $this->createMiddleware($packagePath);
        }
        
        if ($this->package['with_event']) {
            $this->createEvent($packagePath);
            $this->createListener($packagePath);
        }
        
        if ($this->package['with_notification']) {
            $this->createNotification($packagePath);
        }
        
        if ($this->package['with_interface']) {
            $this->createInterface($packagePath);
            $this->createImplementation($packagePath);
        }
        
        if ($this->package['with_repository']) {
            $this->createRepository($packagePath);
        }
        
        if ($this->package['with_service']) {
            $this->createService($packagePath);
        }
        
        if ($this->package['with_config']) {
            $this->createConfigFile($packagePath);
        }
        
        if ($this->package['with_views']) {
            $this->createViews($packagePath);
        }
        
        if ($this->package['with_lang']) {
            $this->createLanguageFiles($packagePath);
        }
        
        if ($this->package['with_migrations']) {
            $this->createMigrationsDirectory($packagePath);
        }
        
        if ($this->package['with_routes']) {
            $this->createRoutesFile($packagePath);
        }
    }

    /**
     * Create the package directory structure.
     *
     * @param string $packagePath
     */
    protected function createDirectories(string $packagePath): void
    {
        $directories = [
            $packagePath . '/src',
            $packagePath . '/src/Contracts',
            $packagePath . '/src/Console',
            $packagePath . '/src/Exceptions',
            $packagePath . '/src/Http',
            $packagePath . '/src/Http/Controllers',
            $packagePath . '/src/Http/Middleware',
            $packagePath . '/src/Models',
            $packagePath . '/src/Providers',
            $packagePath . '/src/Services',
            $packagePath . '/src/Repositories',
            $packagePath . '/src/Events',
            $packagePath . '/src/Listeners',
            $packagePath . '/src/Notifications',
            $packagePath . '/config',
            $packagePath . '/resources/lang/en',
            $packagePath . '/resources/views',
            $packagePath . '/routes',
            $packagePath . '/database/migrations',
            $packagePath . '/database/seeders',
            $packagePath . '/database/factories',
        ];

        if ($this->package['with_tests']) {
            $directories = array_merge($directories, [
                $packagePath . '/tests/Feature',
                $packagePath . '/tests/Unit',
            ]);
        }

        foreach ($directories as $directory) {
            if (!$this->files->exists($directory)) {
                $this->files->makeDirectory($directory, 0755, true);
            }
        }

        $this->info('Directory structure created.');
    }

    /**
     * Create the service provider.
     *
     * @param string $packagePath
     */
    protected function createServiceProvider(string $packagePath): void
    {
        $providerName = $this->package['class_name'] . 'ServiceProvider';
        $providerPath = $packagePath . '/src/Providers/' . $providerName . '.php';

        $stub = $this->files->get($this->getStubPath('service-provider.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Providers',
            'class' => $providerName,
            'packageName' => $this->package['name'],
            'facadeClass' => $this->package['class_name'],
            'facadeNamespace' => $this->package['namespace'] . '\\Facades',
        ]);

        $this->files->put($providerPath, $stub);
        $this->info('Service provider created: ' . $providerName);
    }

    /**
     * Create composer.json file.
     *
     * @param string $packagePath
     */
    protected function createComposerJson(string $packagePath): void
    {
        $packageNameWithVendor = strtolower($this->package['vendor']) . '/' . strtolower($this->package['name']);

        $stub = $this->files->get($this->getStubPath('composer.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'name' => $packageNameWithVendor,
            'description' => $this->package['description'],
            'namespace' => $this->package['namespace'] . '\\',
            'testNamespace' => $this->package['namespace'] . '\\Tests\\',
            'className' => $this->package['class_name'] . 'ServiceProvider',
            'authorName' => $this->package['author'],
            'authorEmail' => $this->package['email'],
        ]);

        $this->files->put($packagePath . '/composer.json', $stub);
        $this->info('composer.json file created.');
    }

    /**
     * Create README.md file.
     *
     * @param string $packagePath
     */
    protected function createReadme(string $packagePath): void
    {
        $packageNameTitle = Str::title(str_replace('-', ' ', $this->package['name']));

        $stub = $this->files->get($this->getStubPath('readme.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'packageNameTitle' => $packageNameTitle,
            'packageName' => $this->package['name'],
            'vendorName' => $this->package['vendor'],
            'description' => $this->package['description'],
        ]);

        $this->files->put($packagePath . '/README.md', $stub);
        $this->info('README.md file created.');
    }

    /**
     * Create LICENSE.md file.
     *
     * @param string $packagePath
     */
    protected function createLicense(string $packagePath): void
    {
        $year = date('Y');

        $stub = $this->files->get($this->getStubPath('license.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'year' => $year,
            'authorName' => $this->package['author'],
            'authorEmail' => $this->package['email'],
        ]);

        $this->files->put($packagePath . '/LICENSE.md', $stub);
        $this->info('LICENSE.md file created.');
    }

    /**
     * Create .gitignore file.
     *
     * @param string $packagePath
     */
    protected function createGitignore(string $packagePath): void
    {
        $content = <<<EOT
        /vendor/
        /.idea/
        /.vscode/
        /.env
        /composer.lock
        /phpunit.xml
        *.log
        *.cache
        *.swp
        .DS_Store
        EOT;

        $this->files->put($packagePath . '/.gitignore', $content);
        $this->info('.gitignore file created.');
    }

    /**
     * Create test files.
     *
     * @param string $packagePath
     */
    protected function createTestFiles(string $packagePath): void
    {
        // Create TestCase.php
        $stub = $this->files->get($this->getStubPath('test-case.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Tests',
            'providerNamespace' => $this->package['namespace'] . '\\Providers',
            'providerClass' => $this->package['class_name'] . 'ServiceProvider',
        ]);

        $this->files->put($packagePath . '/tests/TestCase.php', $stub);

        // Create sample tests
        $this->createSampleTest($packagePath, 'Feature');
        $this->createSampleTest($packagePath, 'Unit');

        // Create phpunit.xml
        $stub = $this->files->get($this->getStubPath('phpunit.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\',
        ]);

        $this->files->put($packagePath . '/phpunit.xml', $stub);
        $this->info('Test files created.');
    }

    /**
     * Create a sample test file.
     *
     * @param string $packagePath
     * @param string $type
     */
    protected function createSampleTest(string $packagePath, string $type): void
    {
        $stub = $this->files->get($this->getStubPath(strtolower($type) . '-test.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Tests\\' . $type,
            'testClass' => $type === 'Unit' ? 'TestCase' : 'Tests\\TestCase',
        ]);

        $this->files->put($packagePath . '/tests/' . $type . '/ExampleTest.php', $stub);
    }

    /**
     * Create a facade for the package.
     *
     * @param string $packagePath
     */
    protected function createFacade(string $packagePath): void
    {
        $facadePath = $packagePath . '/src/Facades/' . $this->package['class_name'] . '.php';

        $stub = $this->files->get($this->getStubPath('facade.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Facades',
            'class' => $this->package['class_name'],
            'serviceClass' => $this->package['namespace'] . '\\Services\\' . $this->package['class_name'] . 'Service',
        ]);

        $this->files->put($facadePath, $stub);
        $this->info('Facade created: ' . $this->package['class_name']);
    }

    /**
     * Create a sample controller.
     *
     * @param string $packagePath
     */
    protected function createController(string $packagePath): void
    {
        $controllerName = $this->package['class_name'] . 'Controller';
        $controllerPath = $packagePath . '/src/Http/Controllers/' . $controllerName . '.php';

        $stub = $this->files->get($this->getStubPath('controller.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Http\\Controllers',
            'class' => $controllerName,
            'serviceNamespace' => $this->package['namespace'] . '\\Services',
            'serviceClass' => $this->package['class_name'] . 'Service',
        ]);

        $this->files->put($controllerPath, $stub);
        $this->info('Controller created: ' . $controllerName);
    }

    /**
     * Create a sample model.
     *
     * @param string $packagePath
     */
    protected function createModel(string $packagePath): void
    {
        $modelName = $this->package['class_name'];
        $modelPath = $packagePath . '/src/Models/' . $modelName . '.php';

        $stub = $this->files->get($this->getStubPath('model.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Models',
            'class' => $modelName,
        ]);

        $this->files->put($modelPath, $stub);
        $this->info('Model created: ' . $modelName);
    }

    /**
     * Create a sample migration.
     *
     * @param string $packagePath
     */
    protected function createMigration(string $packagePath): void
    {
        $tableName = Str::snake(Str::plural($this->package['class_name']));
        $migrationName = 'create_' . $tableName . '_table';
        $migrationPath = $packagePath . '/database/migrations/' . date('Y_m_d_His') . '_' . $migrationName . '.php';

        $stub = $this->files->get($this->getStubPath('migration.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'table' => $tableName,
        ]);

        $this->files->put($migrationPath, $stub);
        $this->info('Migration created: ' . $migrationName);
    }

    /**
     * Create a sample Artisan command.
     *
     * @param string $packagePath
     */
    protected function createCommand(string $packagePath): void
    {
        $commandName = $this->package['class_name'] . 'Command';
        $commandPath = $packagePath . '/src/Console/Commands/' . $commandName . '.php';

        $stub = $this->files->get($this->getStubPath('command.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Console\\Commands',
            'class' => $commandName,
            'signature' => $this->package['name'] . ':command',
        ]);

        $this->files->put($commandPath, $stub);
        $this->info('Command created: ' . $commandName);
    }

    /**
     * Create a sample middleware.
     *
     * @param string $packagePath
     */
    protected function createMiddleware(string $packagePath): void
    {
        $middlewareName = $this->package['class_name'] . 'Middleware';
        $middlewarePath = $packagePath . '/src/Http/Middleware/' . $middlewareName . '.php';

        $stub = $this->files->get($this->getStubPath('middleware.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Http\\Middleware',
            'class' => $middlewareName,
        ]);

        $this->files->put($middlewarePath, $stub);
        $this->info('Middleware created: ' . $middlewareName);
    }

    /**
     * Create a sample event.
     *
     * @param string $packagePath
     */
    protected function createEvent(string $packagePath): void
    {
        $eventName = $this->package['class_name'] . 'Event';
        $eventPath = $packagePath . '/src/Events/' . $eventName . '.php';

        $stub = $this->files->get($this->getStubPath('event.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Events',
            'class' => $eventName,
        ]);

        $this->files->put($eventPath, $stub);
        $this->info('Event created: ' . $eventName);
    }

    /**
     * Create a sample listener.
     *
     * @param string $packagePath
     */
    protected function createListener(string $packagePath): void
    {
        $listenerName = $this->package['class_name'] . 'Listener';
        $listenerPath = $packagePath . '/src/Listeners/' . $listenerName . '.php';

        $stub = $this->files->get($this->getStubPath('listener.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Listeners',
            'class' => $listenerName,
            'eventNamespace' => $this->package['namespace'] . '\\Events',
            'eventClass' => $this->package['class_name'] . 'Event',
        ]);

        $this->files->put($listenerPath, $stub);
        $this->info('Listener created: ' . $listenerName);
    }

    /**
     * Create a sample notification.
     *
     * @param string $packagePath
     */
    protected function createNotification(string $packagePath): void
    {
        $notificationName = $this->package['class_name'] . 'Notification';
        $notificationPath = $packagePath . '/src/Notifications/' . $notificationName . '.php';

        $stub = $this->files->get($this->getStubPath('notification.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Notifications',
            'class' => $notificationName,
        ]);

        $this->files->put($notificationPath, $stub);
        $this->info('Notification created: ' . $notificationName);
    }

    /**
     * Create a sample interface.
     *
     * @param string $packagePath
     */
    protected function createInterface(string $packagePath): void
    {
        $interfaceName = $this->package['class_name'] . 'Interface';
        $interfacePath = $packagePath . '/src/Contracts/' . $interfaceName . '.php';

        $stub = $this->files->get($this->getStubPath('interface.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Contracts',
            'interface' => $interfaceName,
        ]);

        $this->files->put($interfacePath, $stub);
        $this->info('Interface created: ' . $interfaceName);
    }

    /**
     * Create a sample implementation.
     *
     * @param string $packagePath
     */
    protected function createImplementation(string $packagePath): void
    {
        $implementationName = $this->package['class_name'] . 'Implementation';
        $implementationPath = $packagePath . '/src/Services/' . $implementationName . '.php';

        $stub = $this->files->get($this->getStubPath('implementation.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Services',
            'class' => $implementationName,
            'interfaceNamespace' => $this->package['namespace'] . '\\Contracts',
            'interface' => $this->package['class_name'] . 'Interface',
        ]);

        $this->files->put($implementationPath, $stub);
        $this->info('Implementation created: ' . $implementationName);
    }

    /**
     * Create repository files.
     *
     * @param string $packagePath
     */
    protected function createRepository(string $packagePath): void
    {
        $repositoryName = $this->package['class_name'] . 'Repository';
        $repositoryPath = $packagePath . '/src/Repositories/' . $repositoryName . '.php';

        $stub = $this->files->get($this->getStubPath('repository.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Repositories',
            'class' => $repositoryName,
            'modelNamespace' => $this->package['namespace'] . '\\Models',
            'model' => $this->package['class_name'],
        ]);

        $this->files->put($repositoryPath, $stub);
        $this->info('Repository created: ' . $repositoryName);

        // Create repository interface
        $interfaceName = $this->package['class_name'] . 'RepositoryInterface';
        $interfacePath = $packagePath . '/src/Contracts/' . $interfaceName . '.php';

        $stub = $this->files->get($this->getStubPath('repository-interface.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Contracts',
            'interface' => $interfaceName,
            'modelNamespace' => $this->package['namespace'] . '\\Models',
            'model' => $this->package['class_name'],
        ]);

        $this->files->put($interfacePath, $stub);
        $this->info('Repository interface created: ' . $interfaceName);
    }

    /**
     * Create service layer files.
     *
     * @param string $packagePath
     */
    protected function createService(string $packagePath): void
    {
        $serviceName = $this->package['class_name'] . 'Service';
        $servicePath = $packagePath . '/src/Services/' . $serviceName . '.php';

        $stub = $this->files->get($this->getStubPath('service.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'namespace' => $this->package['namespace'] . '\\Services',
            'class' => $serviceName,
            'repositoryNamespace' => $this->package['namespace'] . '\\Repositories',
            'repository' => $this->package['class_name'] . 'Repository',
            'repositoryInterfaceNamespace' => $this->package['namespace'] . '\\Contracts',
            'repositoryInterface' => $this->package['class_name'] . 'RepositoryInterface',
        ]);

        $this->files->put($servicePath, $stub);
        $this->info('Service created: ' . $serviceName);
    }

    /**
     * Create config file.
     *
     * @param string $packagePath
     */
    protected function createConfigFile(string $packagePath): void
    {
        $configPath = $packagePath . '/config/' . $this->package['name'] . '.php';

        $stub = $this->files->get($this->getStubPath('config.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'packageName' => $this->package['name'],
        ]);

        $this->files->put($configPath, $stub);
        $this->info('Config file created: ' . $this->package['name'] . '.php');
    }

    /**
     * Create sample views.
     *
     * @param string $packagePath
     */
    protected function createViews(string $packagePath): void
    {
        $viewPath = $packagePath . '/resources/views/index.blade.php';

        $stub = $this->files->get($this->getStubPath('view.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'packageName' => $this->package['name'],
        ]);

        $this->files->put($viewPath, $stub);
        $this->info('Sample view created: index.blade.php');
    }

    /**
     * Create language files.
     *
     * @param string $packagePath
     */
    protected function createLanguageFiles(string $packagePath): void
    {
        $langPath = $packagePath . '/resources/lang/en/messages.php';

        $stub = $this->files->get($this->getStubPath('lang.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'packageName' => $this->package['name'],
        ]);

        $this->files->put($langPath, $stub);
        $this->info('Language file created: en/messages.php');
    }

    /**
     * Create migrations directory.
     *
     * @param string $packagePath
     */
    protected function createMigrationsDirectory(string $packagePath): void
    {
        $this->files->ensureDirectoryExists($packagePath . '/database/migrations', 0755, true);
        $this->info('Migrations directory created.');
    }

    /**
     * Create routes file.
     *
     * @param string $packagePath
     */
    protected function createRoutesFile(string $packagePath): void
    {
        $routesPath = $packagePath . '/routes/web.php';

        $stub = $this->files->get($this->getStubPath('routes.stub'));
        $stub = $this->replacePlaceholders($stub, [
            'packageName' => $this->package['name'],
            'controllerNamespace' => $this->package['namespace'] . '\\Http\\Controllers',
            'controller' => $this->package['class_name'] . 'Controller',
        ]);

        $this->files->put($routesPath, $stub);
        $this->info('Routes file created: web.php');
    }

    /**
     * Get the path to the stub file.
     *
     * @param string $stub
     * @return string
     */
    protected function getStubPath(string $stub): string
    {
        $localPath = __DIR__ . '/stubs/package/' . $stub;
        
        if ($this->files->exists($localPath)) {
            return $localPath;
        }
        
        return __DIR__ . '/../../../stubs/package/' . $stub;
    }

    /**
     * Replace placeholders in stub file.
     *
     * @param string $stub
     * @param array $replacements
     * @return string
     */
    protected function replacePlaceholders(string $stub, array $replacements): string
    {
        foreach ($replacements as $placeholder => $replacement) {
            $stub = str_replace('{{' . $placeholder . '}}', $replacement, $stub);
        }

        return $stub;
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
