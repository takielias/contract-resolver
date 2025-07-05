<?php

namespace TakiElias\ContractResolver\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ContractResolverRepository extends GeneratorCommand
{
    protected $name = 'cr:make-repo';
    protected $type = 'Repository';
    protected static $defaultName = 'cr:make-repo';
    protected $description = 'Create a new Repository';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/repository.stub');
    }

    protected function buildClass($name): string
    {
        // Extract model name and namespace from input name
        $parts = explode('\\', trim($this->argument('name')));
        $model = Str::singular(end($parts));
        $namespace = implode('\\', array_slice($parts, 0, -1));

        // Get a contract path from config
        $contractBasePath = config('contract-resolver.paths.contracts.repositories', 'App\Contracts\Repositories');
        $contractBasePath = str_replace([app_path(), '/'], ['App', '\\'], $contractBasePath);

        // Construct repository contract namespace
        $repoContractsNamespace = $contractBasePath;
        if (!empty($namespace)) {
            $repoContractsNamespace .= "\\" . $namespace;
        }

        return str_replace(
            ["{{ repoContractsNamespace }}"],
            [$repoContractsNamespace],
            parent::buildClass($name)
        );
    }

    protected function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        // Get an implementation path from config
        $implPath = config('contract-resolver.paths.implementations.repositories', app_path('Repositories'));
        $namespace = str_replace([app_path(), '/'], ['App', '\\'], $implPath);

        return str_replace('App', $rootNamespace, $namespace);
    }

    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        if (!str_ends_with($name, 'Repository')) {
            $name .= 'Repository';
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
