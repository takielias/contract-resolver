<?php

namespace TakiElias\ContractResolver\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ContractResolverRepositoryInterface extends GeneratorCommand
{
    protected $name = 'cr:make-repo-interface';
    protected $type = 'Interface';
    protected static $defaultName = 'cr:make-repo-interface';
    protected $description = 'Create a new Repository Interface';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/repository.interface.stub');
    }

    protected function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        // Get contract path from config
        $contractPath = config('contract-resolver.paths.contracts.repositories', app_path('Contracts/Repositories'));
        $namespace = str_replace([app_path(), '/'], ['App', '\\'], $contractPath);

        return str_replace('App', $rootNamespace, $namespace);
    }

    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        if (!str_ends_with($name, 'RepositoryInterface')) {
            $name .= 'RepositoryInterface';
        }

        return $name;
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the class already exists'],
        ];
    }
}
