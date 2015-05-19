<?php
namespace Aindong\Pluggables\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PluggableEnableCommand extends Command
{
    /**
     * @var string $name The console command name.
     */
    protected $name = 'pluggables:enable';

    /**
     * @var string $description The console command description.
     */
    protected $description = 'Enable a pluggable';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $pluggable = $this->argument('pluggable');

        if ($this->laravel['pluggables']->isDisabled($this->argument('pluggable'))) {
            $this->laravel['pluggables']->enable($pluggable);

            $this->info("Pluggable [{$pluggable}] was enabled successfully.");
        } else {
            $this->comment("Pluggable [{$pluggable}] is already enabled.");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['pluggable', InputArgument::REQUIRED, 'Pluggable slug.']
        ];
    }
}