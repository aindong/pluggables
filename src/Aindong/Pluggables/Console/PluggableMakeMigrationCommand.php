<?php

namespace Aindong\Pluggables\Console;

use Aindong\Pluggables\Handlers\PluggableMakeMigrationHandler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PluggableMakeMigrationCommand extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'pluggables:make:migration';

    /**
     * @var string The console command description.
     */
    protected $description = 'Create a new pluggable migration file';

    /**
     * @var \Aindong\Pluggables\Handlers\PluggableMakeMigrationHandler
     */
    protected $handler;

    /**
     * Create a new command instance.
     *
     * @param \Aindong\Pluggables\Handlers\PluggableMakeMigrationHandler $handler
     */
    public function __construct(PluggableMakeMigrationHandler $handler)
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
        return $this->handler->fire($this, $this->argument('pluggable'), $this->argument('table'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['pluggable', InputArgument::REQUIRED, 'Pluggable slug.'],
            ['table', InputArgument::REQUIRED, 'Table name.'],
        ];
    }
}
