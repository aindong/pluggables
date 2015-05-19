<?php
namespace Aindong\Pluggables\Console;

use Aindong\Pluggables\Pluggables;
use Illuminate\Console\Command;

class PluggableListCommand extends Command
{
    /**
     * @var string $name The console command name.
     */
    protected $name = 'pluggables:list';

    /**
     * @var string $description The console command description.
     */
    protected $description = 'List all application pluggables';

    /**
     * @var Pluggables
     */
    protected $pluggable;

    /**
     * @var array $header The table headers for the command.
     */
    protected $headers = ['Name', 'Slug', 'Description', 'Status'];

    /**
     * Create a new command instance.
     *
     * @param Pluggables $pluggable
     */
    public function __construct(Pluggables $pluggable)
    {
        parent::__construct();

        $this->pluggable = $pluggable;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $pluggables = $this->pluggable->all();

        if (count($pluggables) == 0)
        {
            return $this->error("Your application doesn't have any pluggables.");
        }

        $this->displayPluggables($this->getPluggables());
    }

    /**
     * Get all pluggables.
     *
     * @return array
     */
    protected function getPluggables()
    {
        $pluggables = $this->pluggable->all();
        $results = array();

        foreach ($pluggables as $pluggable)
        {
            $results[] = $this->getPluggableInformation($pluggable);
        }

        return array_filter($results);
    }

    /**
     * Returns pluggable manifest information.
     *
     * @param  string $pluggable
     * @return array
     */
    protected function getPluggableInformation($pluggable)
    {
        return [
            'name'        => $pluggable['name'],
            'slug'        => $pluggable['slug'],
            'description' => $pluggable['description'],
            'status'      => ($pluggable['enabled']) ? 'Enabled' : 'Disabled'
        ];
    }

    /**
     * Display the pluggable information on the console.
     *
     * @param  array $pluggables
     * @return void
     */
    protected function displayPluggables(array $pluggables)
    {
        $this->table($this->headers, $pluggables);
    }
}