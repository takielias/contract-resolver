<?php

namespace TakiElias\ContractResolver\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class ContractResolverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cr:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Contract Resolver Command';

    // Constants for the types
    public const string TYPE_REPOSITORY = 'Repository';
    public const string TYPE_SERVICE = 'Service';
    public const string TYPE_ALL = 'All';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $types = $this->choice(
            'What do you want to create ?',
            [self::TYPE_SERVICE, self::TYPE_REPOSITORY, self::TYPE_ALL],
            2,
            $maxAttempts = null,
            $allowMultipleSelections = true
        );

        $name = Str::studly($this->ask("What is the name ?", 'Product'));

        foreach ($types as $type) {
            switch ($type) {

                case self::TYPE_REPOSITORY:
                    $this->createRepository($name);
                    break;

                case self::TYPE_SERVICE:
                    $this->createService($name);
                    break;

                case self::TYPE_ALL:
                    $this->createAll($name);
                    break;

            }
        }

    }

    protected function createRepository($name): void
    {
        $this->call('cr:make-repo-interface', ['name' => $name]);
        $this->call('cr:make-repo', ['name' => $name]);
    }

    protected function createService($name): void
    {
        $this->call('cr:make-service-interface', ['name' => $name]);
        $this->call('cr:make-service', ['name' => $name]);
    }


    protected function createAll($name): void
    {
        $this->createRepository($name);
        $this->createService($name);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class to which the  will be generated'],
        ];
    }
}
