<?php
namespace Aindong\Pluggables\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PluggableMigrateRefreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string $name The console command name.
     */
    protected $name = 'pluggables:migrate:refresh';

    /**
     * @var string $description The console command description.
     */
    protected $description = 'Reset and re-run all migrations for a specific or all pluggables';

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
        if (! $this->confirmToProceed()) return null;

        $pluggable     = $this->argument('pluggable');
        $moduleName = Str::studly($pluggable);

        $this->call('pluggables:migrate:reset', [
            'module'     => $pluggable,
            '--database' => $this->option('database'),
            '--force'    => $this->option('force'),
            '--pretend'  => $this->option('pretend'),
        ]);

        $this->call('pluggables:migrate', [
            'module'     => $pluggable,
            '--database' => $this->option('database')
        ]);

        if ($this->needsSeeding()) {
            $this->runSeeder($pluggable, $this->option('database'));
        }

        if (isset($pluggable)) {
            $this->info("Pluggable [$moduleName] has been refreshed.");
        } else {
            $this->info("All modules have been refreshed.");
        }
    }

    /**
     * Determine if the developer has requested database seeding.
     *
     * @return bool
     */
    protected function needsSeeding()
    {
        return $this->option('seed');
    }

    /**
     * Run the module seeder command.
     *
     * @param  string $database
     * @return void
     */
    protected function runSeeder($module = null, $database = null)
    {
        $this->call('pluggables:seed', [
            'pluggable'     => $this->argument('pluggable'),
            '--database' => $database
        ]);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [['pluggable', InputArgument::OPTIONAL, 'Pluggable slug.']];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run while in production.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.']
        ];
    }
}