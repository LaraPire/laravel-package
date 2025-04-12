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
}
