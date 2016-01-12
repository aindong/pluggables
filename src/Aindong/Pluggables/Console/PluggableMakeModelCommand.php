<?php

namespace Aindong\Pluggables\Console;

use Aindong\Pluggables\Handlers\PluggableMakeModelHandler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PluggableMakeModelCommand extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'pluggables:make:model';

    /**
     * @var string The console command description.
     */
    protected $description = 'Create a new pluggable model class';

    protected $handler;

    public function __construct(PluggableMakeModelHandler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        return $this->handler->fire($this, $this->argument('pluggable'), $this->argument('name'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['pluggable', InputArgument::REQUIRED, 'The slug of the pluggable'],
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }
}
