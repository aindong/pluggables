<?php
namespace Aindong\Pluggables\Console;

use Aindong\Pluggables\Handlers\PluggableMakeHandler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PluggableMakeCommand extends Command
{
    /**
     * @var string $name The console command name.
     */
    protected $name = 'pluggables:make';

    /**
     * @var string $description The console command description.
     */
    protected $description = 'Create a new pluggable';

    /**
     * @var \Aindong\Pluggables\Handlers\PluggableMakeHandler
     */
    protected $handler;

    /**
     * Create a new command instance.
     *
     * @param \Aindong\Pluggables\Handlers\PluggableMakeHandler $handler
     */
    public function __construct(PluggableMakeHandler $handler)
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
        return $this->handler->fire($this, $this->argument('name'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Pluggable slug.']
        ];
    }
}