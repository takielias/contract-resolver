<?php

namespace TakiElias\ContractResolver;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use TakiElias\ContractResolver\Console\Commands\ContractResolverCommand;
use TakiElias\ContractResolver\Console\Commands\ContractResolverRepository;
use TakiElias\ContractResolver\Console\Commands\ContractResolverRepositoryInterface;
use TakiElias\ContractResolver\Console\Commands\ContractResolverService;
use TakiElias\ContractResolver\Console\Commands\ContractResolverServiceInterface;

class ContractResolverServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
        $this->registerCommands();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/contract-resolver.php', 'contract-resolver');

        // Register the service the package provides.
        $this->app->singleton('contract-resolver', function ($app) {
            return new ContractResolver;
        });

        // Auto-bind interfaces if enabled
        if (config('contract-resolver.auto_binding.enabled', true)) {
            $this->performAutoBinding();
        }
    }

    /**
     * Perform automatic binding of interfaces to implementations.
     */
    private function performAutoBinding(): void
    {
        $filesystem = app(Filesystem::class);
        $config = config('contract-resolver');

        // Auto-bind repositories
        $this->bindContracts(
            $filesystem,
            $config['paths']['contracts']['repositories'] ?? app_path('Contracts/Repositories'),
            $config['paths']['implementations']['repositories'] ?? app_path('Repositories')
        );

        // Auto-bind services
        $this->bindContracts(
            $filesystem,
            $config['paths']['contracts']['services'] ?? app_path('Contracts/Services'),
            $config['paths']['implementations']['services'] ?? app_path('Services')
        );
    }

    /**
     * Bind contracts to their implementations.
     */
    private function bindContracts(Filesystem $filesystem, string $contractsPath, string $implementationPath): void
    {
        if (!$filesystem->isDirectory($contractsPath)) {
            return;
        }

        $files = $filesystem->allFiles($contractsPath);

        foreach ($files as $file) {
            $contractNamespace = $this->extractNamespace($file->getPathname());
            $contractClass = $contractNamespace . "\\" . $file->getFilenameWithoutExtension();

            // Ensure class existence and interface nature
            if (!interface_exists($contractClass)) {
                continue;
            }

            $implementationClass = $this->getImplementationClass($contractClass);

            if (class_exists($implementationClass)) {
                $this->app->bind($contractClass, $implementationClass);
            }
        }
    }

    /**
     * Get the implementation class name from the contract class.
     */
    private function getImplementationClass(string $contractClass): string
    {
        $config = config('contract-resolver.naming');
        $removeSegments = $config['remove_segments'] ?? ['Contracts'];
        $interfaceSuffix = $config['interface_suffix'] ?? 'Interface';

        // Remove specified namespace segments
        $implementationClass = $contractClass;
        foreach ($removeSegments as $segment) {
            $implementationClass = str_replace("\\{$segment}\\", '\\', $implementationClass);
        }

        // Remove interface suffix
        return preg_replace('/' . preg_quote($interfaceSuffix, '/') . '$/', '', $implementationClass);
    }

    /**
     * Extract namespace from file content.
     */
    private function extractNamespace(string $filePath): string
    {
        $contents = file_get_contents($filePath);
        preg_match('/namespace\s+(.*);/', $contents, $matches);
        return trim($matches[1] ?? '');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['contract-resolver'];
    }

    /**
     * Register the package's artisan commands.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        $this->commands([
            ContractResolverCommand::class,
            ContractResolverService::class,
            ContractResolverServiceInterface::class,
            ContractResolverRepository::class,
            ContractResolverRepositoryInterface::class,
        ]);
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/contract-resolver.php' => config_path('contract-resolver.php'),
        ], 'contract-resolver.config');
    }
}
