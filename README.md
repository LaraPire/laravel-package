# Laravel Package

A Laravel package generator that streamlines the process of creating standardized package structures. Quickly bootstrap your packages with a complete, well-organized structure and focus on building functionality rather than configuration.

## Features

- Generates a complete Laravel package structure with a single command
- Creates standard Laravel package directories (config, migrations, views, etc.)
- Sets up composer.json with proper autoloading and dependencies
- Creates a Service Provider with common Laravel integrations pre-configured
- Includes testing setup with PHPUnit
- Generates license, readme, and other essential files

## Installation

You can install the package via composer:

```bash
composer require rayiumir/laravel-package
```

The service provider will be automatically registered for Laravel 5.5+. For older versions, add the service provider manually:

```php
// config/app.php

'providers' => [
    Rayiumir\LaravelPackage\ServiceProvider\PackageServiceProvider::class,
];
```

## Usage

### Basic Usage

Generate a new package with the following command:

```bash
php artisan make:package my-package
```

This will create a new package in the `packages/my-package` directory with the default vendor name.

### Customizing the Vendor Name

You can specify a custom vendor name:

```bash
php artisan make:package my-package --vendor=acme
```

### Including Tests

To include PHPUnit test setup:

```bash
php artisan make:package my-package --with-tests
```

## Generated Structure

The generated package will have the following structure:

```
packages/package-name/
├── config/
├── database/
│   └── migrations/
├── resources/
│   ├── lang/
│   └── views/
├── routes/
├── ServiceProvider/
│   └── PackageNameServiceProvider.php
├── tests/ (if --with-tests option is used)
│   ├── Feature/
│   ├── Unit/
│   └── TestCase.php
├── composer.json
├── LICENSE.md
├── README.md
└── phpunit.xml
```

## Next Steps After Generation

After generating your package, you might want to:

1. Edit the `composer.json` file to update package details and requirements
2. Modify the Service Provider to add any specific functionality
3. Update the README.md with your package documentation
4. Add your migrations, routes, and views
5. Create your package's main classes in the `src` directory
6. If you used the `--with-tests` option, start writing tests for your package

### Local Development

When developing the package locally within a Laravel application, you can add the repository to your application's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/my-package"
    }
]
```

### Testing Your Package

If you generated your package with the `--with-tests` option, you can run tests with:

```bash
cd packages/my-awesome-package
composer install
vendor/bin/phpunit
```

## Publishing Your Package

Once your package is ready, you can publish it to Packagist:

1. Push your package to GitHub or another Git repository
2. Register your package on [Packagist](https://packagist.org/)
3. Set up webhooks for automatic updates when you push changes

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover any security-related issues, please email your-email@example.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you have any questions or need help, please:

- Open an issue on GitHub
