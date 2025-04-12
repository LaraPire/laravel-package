<?php


use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:package
                            {name : The name of the package (vendor/package-name)}
                            {--namespace= : The namespace of the package}
                            {--type=default : The type of package (default, admin-panel, api-service, theme)}
                            {--with-tests : Include tests directory}
                            {--with-config : Include config file}
                            {--interactive : Run in interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Laravel package structure';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The base path of the package.
     *
     * @var string
     */
    protected string $packagePath;

    /**
     * The vendor name of the package.
     *
     * @var string
     */
    protected string $vendorName;

    /**
     * The package name (without vendor).
     *
     * @var string
     */
    protected string $packageName;

    /**
     * The package namespace.
     *
     * @var string
     */
    protected string $namespace;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->parsePackageName();

        if ($this->option('interactive')) {
            $this->runInteractiveMode();
        }

        $this->createPackageDirectory();
        $this->createPackageStructure();
    }

    /**
     * Parse the package name.
     *
     * @return void
     */
    protected function parsePackageName(): void
    {
        $name = $this->argument('name');
        $parts = explode('/', $name);

        if (count($parts) !== 2) {
            $this->error('Invalid package name format. Use vendor/package-name');
            exit(Command::FAILURE);
        }

        $this->vendorName = $parts[0];
        $this->packageName = $parts[1];
        $this->packagePath = base_path('packages/' . $this->vendorName . '/' . $this->packageName);

        $namespace = $this->option('namespace');
        if (empty($namespace)) {
            $namespace = Str::studly($this->vendorName) . '\\' . Str::studly($this->packageName);
        }
        $this->namespace = $namespace;
    }

    /**
     * Run interactive mode.
     *
     * @return void
     */
    protected function runInteractiveMode(): void
    {
        $this->info('Interactive mode enabled.');

        // Ask for namespace
        $namespace = $this->ask('Package namespace', $this->namespace);
        $this->namespace = $namespace;

        // Ask for package type
        $type = $this->choice('Package type', ['default', 'admin-panel', 'api-service', 'theme'], 0);
        $this->input->setOption('type', $type);

        // Ask for tests
        if ($this->confirm('Include tests directory?', true)) {
            $this->input->setOption('with-tests', true);
        }

        // Ask for config
        if ($this->confirm('Include config file?', true)) {
            $this->input->setOption('with-config', true);
        }
    }

    /**
     * Create package base directory.
     *
     * @return void
     */
    protected function createPackageDirectory(): void
    {
        if (File::exists($this->packagePath)) {
            if (!$this->confirm("The package directory already exists. Do you want to overwrite it?")) {
                $this->error('Package creation aborted.');
                exit(Command::FAILURE);
            }

            File::deleteDirectory($this->packagePath);
        }

        File::makeDirectory($this->packagePath, 0755, true);
    }

    /**
     * Create package directory structure.
     *
     * @return void
     */
    protected function createPackageStructure(): void
    {
        $folders = [
            'src',
            'resources/views',
            'resources/lang',
            'database/migrations',
            'routes',
        ];

        // Create specific structure based on package type
        $type = $this->option('type');
        if ($type === 'admin-panel') {
            $folders[] = 'resources/assets/js';
            $folders[] = 'resources/assets/css';
        } elseif ($type === 'api-service') {
            $folders[] = 'src/Http/Controllers/Api';
            $folders[] = 'src/Http/Resources';
        } elseif ($type === 'theme') {
            $folders[] = 'resources/assets/js';
            $folders[] = 'resources/assets/css';
            $folders[] = 'resources/assets/images';
        }

        foreach ($folders as $folder) {
            File::makeDirectory($this->packagePath . '/' . $folder, 0755, true);
        }

        // Create empty .gitkeep files to ensure directories are tracked in git
        foreach ($folders as $folder) {
            File::put($this->packagePath . '/' . $folder . '/.gitkeep', '');
        }
    }

    /**
     * Create composer.json file.
     *
     * @return void
     */
    protected function createComposerJson(): void
    {
        $namespaceForJson = str_replace('\\', '\\\\', $this->namespace);
        $composerJson = <<<JSON
{
    "name": "{$this->vendorName}/{$this->packageName}",
    "description": "A Laravel package",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Your Name",
            "email": "email@example.com"
        }
    ],
    "require": {
        "php": "^8.0",
        "illuminate/support": "^9.0|^10.0|^11.0|^12.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0|^10.0"
    },
    "autoload": {
        "psr-4": {
            "{$namespaceForJson}\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "{$namespaceForJson}\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "{$this->namespace}\\{$this->getServiceProviderName()}"
            ]
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
JSON;

        File::put($this->packagePath . '/composer.json', $composerJson);
    }
}
