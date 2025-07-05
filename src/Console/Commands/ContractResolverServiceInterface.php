<?php

namespace TakiElias\ContractResolver\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ContractResolverServiceInterface extends GeneratorCommand
{
    protected $name = 'cr:make-service-interface';
    protected $type = 'Interface';
    protected static $defaultName = 'cr:make-service-interface';
    protected $description = 'Create a new Service Interface';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/service.interface.stub');
    }

    protected function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $contractPath = config('contract-resolver.paths.contracts.services', app_path('Contracts/Services'));
        $namespace = str_replace([app_path(), '/'], ['App', '\\'], $contractPath);

        return str_replace('App', $rootNamespace, $namespace);
    }

    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        if (!str_ends_with($name, 'ServiceInterface')) {
            $name .= 'ServiceInterface';
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
