<?php

namespace TakiElias\ContractResolver\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ContractResolverService extends GeneratorCommand
{
    protected $name = 'cr:make-service';
    protected $type = 'Service';
    protected static $defaultName = 'cr:make-service';
    protected $description = 'Create a new Service';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/service.stub');
    }

    protected function buildClass($name): string
    {
        $parts = explode('\\', trim($this->argument('name')));
        $namespace = implode('\\', array_slice($parts, 0, -1));
        $model = Str::studly(Str::replace("Service", "", end($parts)));

        // Get contract path from config
        $contractBasePath = config('contract-resolver.paths.contracts.services', 'App\Contracts\Services');
        $contractBasePath = str_replace([app_path(), '/'], ['App', '\\'], $contractBasePath);

        $serviceContractsNamespace = $contractBasePath;
        if (!empty($namespace)) {
            $serviceContractsNamespace .= "\\" . $namespace;
        }

        $replacements = [
            "{{ camelName }}" => Str::camel($model),
            "{{ serviceContractsNamespace }}" => $serviceContractsNamespace,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), parent::buildClass($name));
    }

    protected function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $implPath = config('contract-resolver.paths.implementations.services', app_path('Services'));
        $namespace = str_replace([app_path(), '/'], ['App', '\\'], $implPath);

        return str_replace('App', $rootNamespace, $namespace);
    }

    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        if (!str_ends_with($name, 'Service')) {
            $name .= 'Service';
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
